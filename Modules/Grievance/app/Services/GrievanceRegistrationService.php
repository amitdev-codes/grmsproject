<?php

namespace Modules\Grievance\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Modules\Grievance\Datatable\GrievanceDataTable;
use Modules\Grievance\DataTransferObjects\GrievanceIntakeData;
use Modules\Grievance\Events\GrievanceRegistered;
use Modules\Grievance\Interface\GrievanceRepositoryInterface;
use Modules\Grievance\Jobs\SubmitGrievanceJob;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Models\GrievanceCategory;
use Modules\Grievance\Models\GrievanceMessage;
use Modules\Grievance\Models\InboundSms;
use Modules\Grievance\Notifications\GrievanceAllocated;
use Modules\Grievance\Services\Ai\AiGrievanceClassifier;
// channel is a plain string code (see GrievanceIntakeData) resolved against
// the grievance_channels table via findChannelByCode() — no enum import.
use Modules\Master\Models\District;
use Modules\Master\Models\Division;

class GrievanceRegistrationService
{
    public function __construct(
        protected GrievanceRepositoryInterface $grievances,
        protected GrievanceDataTable $dataTable,
        protected AiGrievanceClassifier $classifier,
    ) {}

    public function table(Request $request): array
    {
        return $this->dataTable->toArray($request);
    }

    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->grievances->paginate($filters, $perPage);
    }

    public function find(int $id): Grievance
    {
        return $this->grievances->findMany([$id])->firstOrFail();
    }

    // -------------------------------------------------------------------
    // THE single entry point. Public web app, mobile app, helpdesk officer,
    // SMS and USSD all build a GrievanceIntakeData and call this. Nothing
    // else in the codebase should create a Grievance row directly.
    // -------------------------------------------------------------------
    /**
     * Register a grievance from the public web form (legacy - uses full submit).
     *
     * @param  array  $data  Validated data from StorePublicGrievanceRequest
     * @param  UploadedFile[]  $attachments
     */
    public function registerPublic(array $data, array $attachments = []): Grievance
    {
        $intakeData = GrievanceIntakeData::fromPublicWebRequest($data, $attachments);

        // Use new quick submit + async pattern
        $grievance = $this->submitQuick($intakeData);
        SubmitGrievanceJob::dispatch($grievance->id);

        return $grievance;
    }

    /**
     * Quick submit - creates grievance with reference number immediately.
     * Heavy processing (AI classification, routing, notifications) is deferred to a job.
     */
    public function submitQuick(GrievanceIntakeData $data): Grievance
    {
        return DB::transaction(function () use ($data) {
            $channel = $this->grievances->findChannelByCode($data->channel);

            $grievance = $this->grievances->create([
                'reference_no' => $this->grievances->nextReferenceNumber(),
                'grievance_category_id' => $data->categoryId ?? null,
                'channel_id' => $channel?->id,
                'district_id' => $data->districtId,
                'description' => $data->description,
                'complainant_name' => $data->contactName,
                'complainant_phone' => $data->contactPhone,
                'complainant_email' => $data->contactEmail,
                'is_anonymous' => $data->isAnonymous,
                'raw_payload' => $data->rawPayload,
                'status' => Grievance::STATUS_SUBMITTED,
                'registered_by' => $data->registeredBy,
                'project_id' => $data->projectId,
                'is_previously_lodged' => $data->isPreviouslyLodged,
                'is_previously_finalized' => $data->isPreviouslyFinalized,
                'location_description' => $data->locationDescription,
                'latitude' => $data->latitude,
                'longitude' => $data->longitude,
                'location_accuracy_meters' => $data->locationAccuracyMeters,
                'preferred_language' => $data->preferredLanguage,
                'metadata' => $data->metadata,
                'ai_suggested_category_id' => null,
                'ai_confidence' => null,
            ]);

            if ($data->sourceGrievanceReference) {
                $sourceId = Grievance::where('reference_no', $data->sourceGrievanceReference)->value('id');

                if ($sourceId) {
                    $this->grievances->update($grievance, ['source_grievance_id' => $sourceId]);
                }
            }

            $this->attachMedia($grievance, $data->attachments);

            $this->grievances->recordStatus(
                $grievance, from: null, to: Grievance::STATUS_SUBMITTED,
                actorId: $data->registeredBy,
                actorRole: $data->resolveActorRole(),
            );

            return $grievance->refresh();
        });
    }

    /**
     * Full processing - AI classification, routing, notifications.
     * Runs in a queued job after quick submit.
     */
    public function processAsync(int $grievanceId): void
    {
        $grievance = Grievance::with(['category', 'channel', 'district'])->findOrFail($grievanceId);

        [$category, $aiResult] = $this->resolveCategoryForAsync($grievance);

        $grievance->update([
            'grievance_category_id' => $category->id,
            'ai_suggested_category_id' => $aiResult['category_id'] ?? null,
            'ai_confidence' => $aiResult['confidence'] ?? null,
        ]);

        // Build minimal intake data from grievance for routing/acknowledgement
        $intakeData = new GrievanceIntakeData(
            channel: $grievance->channel?->code ?? 'web',
            description: $grievance->description,
            categoryId: $category->id,
            districtId: $grievance->district_id,
            divisionId: $grievance->division_id,
            sectionId: $grievance->section_id,
            contactPhone: $grievance->complainant_phone,
            contactEmail: $grievance->complainant_email,
            contactName: $grievance->complainant_name,
            isAnonymous: $grievance->is_anonymous,
            locationDescription: $grievance->location_description,
            latitude: $grievance->latitude,
            longitude: $grievance->longitude,
            locationAccuracyMeters: $grievance->location_accuracy_meters,
            projectId: $grievance->project_id,
            isPreviouslyLodged: $grievance->is_previously_lodged,
            isPreviouslyFinalized: $grievance->is_previously_finalized,
            metadata: $grievance->metadata,
            preferredLanguage: $grievance->preferred_language,
        );

        $this->routeGrievance($grievance, $category, $intakeData);
        $this->recordAcknowledgement($grievance, $intakeData);

        if ($grievance->is_previously_lodged || $grievance->is_previously_finalized) {
            Notification::send(User::role('Director')->get(), new GrievanceAllocated($grievance));
        }

        event(new GrievanceRegistered($grievance));
    }

    /**
     * Resolve category for async processing.
     * Uses the category from the grievance if already set, otherwise runs AI classification.
     */
    protected function resolveCategoryForAsync(Grievance $grievance): array
    {
        if ($grievance->grievance_category_id) {
            $category = GrievanceCategory::findOrFail($grievance->grievance_category_id);

            return [$category, ['category_id' => $category->id, 'confidence' => 1.0]];
        }

        $aiResult = $this->classifier->classifyToCategory($grievance->description);

        if ($aiResult['confidence'] >= config('grievance.ai.confidence_threshold') && $aiResult['category_id']) {
            $category = GrievanceCategory::find($aiResult['category_id']);

            if ($category) {
                return [$category, $aiResult];
            }
        }

        $fallback = GrievanceCategory::where('slug', 'other')->first()
            ?? GrievanceCategory::query()->firstOrFail();

        return [$fallback, $aiResult];
    }

    public function submit(GrievanceIntakeData $data): Grievance
    {
        return DB::transaction(function () use ($data) {
            [$category, $aiResult] = $this->resolveCategory($data);

            $channel = $this->grievances->findChannelByCode($data->channel);

            $grievance = $this->grievances->create([
                'reference_no' => $this->grievances->nextReferenceNumber(),
                'grievance_category_id' => $category->id,
                'channel_id' => $channel?->id,
                'district_id' => $data->districtId,
                'description' => $data->description,
                'complainant_name' => $data->contactName,
                'complainant_phone' => $data->contactPhone,
                'complainant_email' => $data->contactEmail,
                'is_anonymous' => $data->isAnonymous,
                'raw_payload' => $data->rawPayload,
                'status' => Grievance::STATUS_SUBMITTED,
                'registered_by' => $data->registeredBy,
                'project_id' => $data->projectId,
                'is_previously_lodged' => $data->isPreviouslyLodged,
                'is_previously_finalized' => $data->isPreviouslyFinalized,
                'location_description' => $data->locationDescription,
                'latitude' => $data->latitude,
                'longitude' => $data->longitude,
                'location_accuracy_meters' => $data->locationAccuracyMeters,
                'preferred_language' => $data->preferredLanguage,
                'metadata' => $data->metadata,
                'ai_suggested_category_id' => $aiResult['category_id'] ?? null,
                'ai_confidence' => $aiResult['confidence'] ?? null,
            ]);

            if ($data->sourceGrievanceReference) {
                $sourceId = Grievance::where('reference_no', $data->sourceGrievanceReference)->value('id');

                if ($sourceId) {
                    $this->grievances->update($grievance, ['source_grievance_id' => $sourceId]);
                }
            }

            $this->attachMedia($grievance, $data->attachments);

            $this->grievances->recordStatus(
                $grievance, from: null, to: Grievance::STATUS_SUBMITTED,
                actorId: $data->registeredBy,
                actorRole: $data->resolveActorRole(),
            );

            $this->routeGrievance($grievance, $category, $data);
            $this->recordAcknowledgement($grievance, $data);

            if ($data->isPreviouslyLodged || $data->isPreviouslyFinalized) {
                Notification::send(User::role('Director')->get(), new GrievanceAllocated($grievance));
            }

            event(new GrievanceRegistered($grievance));

            return $grievance->refresh();
        });
    }

    /**
     * If the caller already knows the category (dropdown on the web form,
     * helpdesk officer, USSD menu) we trust it. Otherwise — typically free
     * text SMS — we ask the configured AI provider to pick one. Low
     * confidence falls back to "Other" so a human reviews it instead of a
     * bad auto-classification silently routing the case to the wrong desk.
     */
    protected function resolveCategory(GrievanceIntakeData $data): array
    {
        if ($data->categoryId) {
            return [GrievanceCategory::findOrFail($data->categoryId), null];
        }

        $aiResult = $this->classifier->classifyToCategory($data->description);

        if ($aiResult['confidence'] >= config('grievance.ai.confidence_threshold') && $aiResult['category_id']) {
            $category = GrievanceCategory::find($aiResult['category_id']);

            if ($category) {
                return [$category, $aiResult];
            }
        }

        $fallback = GrievanceCategory::where('slug', 'other')->first()
            ?? GrievanceCategory::query()->firstOrFail();

        return [$fallback, $aiResult];
    }

    protected function routeGrievance(Grievance $grievance, GrievanceCategory $category, GrievanceIntakeData $data): void
    {
        $policy = DB::table('grievance_sla_policies')->where('is_active', true)->orderBy('id')->first();
        $now = now();

        $divisionId = $data->divisionId ?? $this->resolveRoutingDivisionId($category, $data->districtId);
        $sectionId = $data->sectionId ?? $category->default_section_id ?? null;
        $status = $divisionId ? 'allocated_division' : Grievance::STATUS_SUBMITTED;

        $this->grievances->update($grievance, [
            'division_id' => $divisionId,
            'section_id' => $sectionId,
            'status' => $status,
            'grievance_sla_policy_id' => $policy?->id,
            'first_response_due_at' => $now->copy()->addHours($policy?->acknowledgement_hours ?? 48),
            'resolution_due_at' => $now->copy()->addHours($policy?->resolution_hours ?? 168),
            'sla_due_at' => $now->copy()->addHours($policy?->acknowledgement_hours ?? 48),
        ]);

        if ($divisionId) {
            $this->grievances->recordStatus($grievance, Grievance::STATUS_SUBMITTED, $status, null, 'system', 'Automatically routed from grievance category.');

            DB::table('grievance_assignments')->insert([
                'grievance_id' => $grievance->id,
                'action' => 'allocated',
                'to_division_id' => $divisionId,
                'to_section_id' => $sectionId,
                'reason' => 'Automatic category routing.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            Notification::send(
                User::role('Division Director')->where('division_id', $divisionId)->get(),
                new GrievanceAllocated($grievance)
            );
        }
    }

    protected function recordAcknowledgement(Grievance $grievance, GrievanceIntakeData $data): void
    {
        DB::table('grievance_communications')->insert([
            'grievance_id' => $grievance->id,
            'message_type' => 'acknowledgement',
            'channel' => $data->contactPhone ? 'sms' : 'email',
            'recipient' => $data->isAnonymous ? null : ($data->contactPhone ?? $data->contactEmail),
            'body' => "Grievance {$grievance->reference_no} received. You can track its status using this reference number.",
            'template_data' => json_encode(['reference_no' => $grievance->reference_no]),
            'delivery_status' => $data->isAnonymous ? 'not_applicable' : 'queued',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function resolveRoutingDivisionId(GrievanceCategory $category, ?int $districtId): ?int
    {
        if ($category->division_id) {
            return $category->division_id;
        }

        $districtCode = District::whereKey($districtId)->value('code');
        $divisionCode = match ($districtCode) {
            'BE', 'BB', 'LE' => 'NR',
            'MS' => 'CR',
            'MF', 'MH', 'MK', 'QN', 'QT', 'TT' => 'SR',
            default => null,
        };

        return $divisionCode ? Division::where('code', $divisionCode)->value('id') : null;
    }

    // -------------------------------------------------------------------
    // Edit / list / export — unchanged
    // -------------------------------------------------------------------

    public function update(Grievance $grievance, array $data, array $newAttachments = [], array $removeMediaIds = []): Grievance
    {
        $grievance = $this->grievances->update($grievance, $data);
        $this->attachMedia($grievance, $newAttachments);
        $this->removeMedia($grievance, $removeMediaIds);

        return $grievance;
    }

    public function bulkDelete(array $ids): int
    {
        return $this->grievances->bulkDelete($ids);
    }

    public function exportQuery(array $filters): Builder
    {
        return $this->grievances->queryForExport($filters);
    }

    /** @param UploadedFile[] $files */
    protected function attachMedia(Grievance $grievance, array $files): void
    {
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $grievance->addMedia($file)->toMediaCollection(Grievance::MEDIA_COLLECTION);
            }
        }
    }

    protected function removeMedia(Grievance $grievance, array $mediaIds): void
    {
        if (empty($mediaIds)) {
            return;
        }

        $grievance->media()->whereIn('id', $mediaIds)->get()->each->delete();
    }

    // -------------------------------------------------------------------
    // USSD — now just builds a DTO and calls submit()
    // -------------------------------------------------------------------

    public function handleUssd(string $sessionId, string $phoneNumber, string $text): string
    {
        $steps = $text === '' ? [] : explode('*', $text);

        return match (count($steps)) {
            0 => "CON Welcome to RD Grievance Line\n1. Continue",
            1 => "CON Select category:\n".$this->numberedMenu(GrievanceCategory::active()->pluck('name_en')),
            2 => "CON Select district:\n".$this->numberedMenu(District::orderBy('name')->pluck('name')),
            3 => 'CON Describe the issue (briefly):',
            4 => $this->submitFromUssd($steps, $phoneNumber, $sessionId, $text),
            default => 'END Session expired. Please dial again.',
        };
    }

    protected function submitFromUssd(array $steps, string $phone, string $sessionId, string $rawText): string
    {
        [, $catIndex, $distIndex, $description] = $steps;
        $category = GrievanceCategory::active()->get()->get(((int) $catIndex) - 1);
        $district = District::orderBy('name')->get()->get(((int) $distIndex) - 1);

        if (! $category || ! $district || trim($description) === '') {
            return 'END Invalid selection. Please dial again.';
        }

        $grievance = $this->submit(GrievanceIntakeData::fromUssd(
            categoryId: $category->id,
            districtId: $district->id,
            description: $description,
            phone: $phone,
            rawPayload: ['session_id' => $sessionId, 'text' => $rawText],
        ));

        return "END Thank you. Your grievance reference is {$grievance->reference_no}. You will receive an SMS confirmation.";
    }

    // -------------------------------------------------------------------
    // SMS — structured "GRV <catcode> <distcode> <text>" still works as
    // before; free-text SMS with no recognizable code now also registers,
    // letting the AI classifier pick the category inside submit(), instead
    // of just parking it in inbound_sms unread.
    // -------------------------------------------------------------------

    public function handleSms(string $from, string $text): array
    {
        $text = trim($text);

        if (preg_match('/^GRV\s+([A-Z0-9]+)\s+([A-Z0-9]+)\s+(.+)$/i', $text, $m)) {
            $category = $this->grievances->findCategoryByCode($m[1]);
            $district = $this->grievances->findDistrictByCode($m[2]);

            if ($category && $district) {
                $grievance = $this->submit(GrievanceIntakeData::fromSms(
                    description: $m[3],
                    phone: $from,
                    rawPayload: ['raw_text' => $text],
                    categoryId: $category->id,
                    districtId: $district->id,
                ));

                return ['registered' => true, 'grievance' => $grievance];
            }
        }

        // Too short to be meaningful — keep the old "unread inbox" fallback
        // rather than wasting an AI call and creating junk grievances.
        if (mb_strlen($text) < 10) {
            $inbound = InboundSms::create(['from_number' => $from, 'raw_message' => $text, 'status' => 'pending']);

            return ['registered' => false, 'inbound_sms' => $inbound];
        }

        $grievance = $this->submit(GrievanceIntakeData::fromSms(
            description: $text,
            phone: $from,
            rawPayload: ['raw_text' => $text],
        ));

        return ['registered' => true, 'grievance' => $grievance];
    }

    protected function numberedMenu($items): string
    {
        return $items->values()->map(fn ($v, $i) => ($i + 1).". {$v}")->implode("\n");
    }

    // -------------------------------------------------------------------
    // Tracking / messaging / rating — unchanged
    // -------------------------------------------------------------------

    public function track(string $referenceNo, ?string $contact): ?Grievance
    {
        return $this->grievances->findForTracking($referenceNo, $contact);
    }

    public function addCitizenMessage(Grievance $grievance, string $contact, string $body): ?GrievanceMessage
    {
        if ($grievance->is_anonymous || $grievance->status === 'closed' || $grievance->status === 'rejected') {
            return null;
        }

        if (! $this->grievances->findForTracking($grievance->reference_no, $contact)) {
            return null;
        }

        return $this->grievances->addMessage($grievance, 'citizen', null, $body);
    }

    public function rate(Grievance $grievance, string $contact, int $rating): ?Grievance
    {
        if (! in_array($grievance->status, ['resolved', 'closed'])) {
            return null;
        }

        if (! $grievance->is_anonymous && ! $this->grievances->findForTracking($grievance->reference_no, $contact)) {
            return null;
        }

        return $this->grievances->rate($grievance, $rating);
    }
}
