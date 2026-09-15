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
use Modules\Grievance\Events\GrievanceRegistered;
use Modules\Grievance\Interface\GrievanceRepositoryInterface;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Models\GrievanceCategory;
use Modules\Grievance\Models\GrievanceMessage;
use Modules\Grievance\Models\InboundSms;
use Modules\Grievance\Notifications\GrievanceAllocated;
use Modules\Master\Models\District;
use Modules\Master\Models\Division;

class GrievanceRegistrationService
{
    public function __construct(protected GrievanceRepositoryInterface $grievances, protected GrievanceDataTable $dataTable) {}

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

    /** @param UploadedFile[] $attachments */
    public function register(
        int $categoryId,
        ?int $districtId,
        string $description,
        ?string $phone,
        string $channelCode,
        ?array $rawPayload = null,
        bool $isAnonymous = false,
        ?string $email = null,
        ?string $name = null,
        ?int $registeredBy = null,
        ?string $actorRole = null,
        array $attachments = [],
    ): Grievance {
        $channel = $this->grievances->findChannelByCode($channelCode);

        $grievance = $this->grievances->create([
            'reference_no' => $this->grievances->nextReferenceNumber(),
            'grievance_category_id' => $categoryId,
            'channel_id' => $channel?->id,
            'district_id' => $districtId,
            'description' => $description,
            'complainant_name' => $name,
            'complainant_phone' => $phone,
            'complainant_email' => $email,
            'is_anonymous' => $isAnonymous,
            'raw_payload' => $rawPayload,
            'status' => Grievance::STATUS_SUBMITTED,
            'registered_by' => $registeredBy,
        ]);

        $this->attachMedia($grievance, $attachments);

        $this->grievances->recordStatus(
            $grievance, from: null, to: Grievance::STATUS_SUBMITTED,
            actorId: $registeredBy,
            actorRole: $registeredBy ? 'helpdesk_officer' : ($actorRole ?? 'self_service'),
        );

        event(new GrievanceRegistered($grievance));

        return $grievance;
    }

    /**
     * @param  UploadedFile[]  $newAttachments
     * @param  int[]  $removeMediaIds
     */
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

    // --- USSD/SMS untouched from before, no media involved ---

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

        $grievance = $this->register(
            categoryId: $category->id, districtId: $district->id, description: $description,
            phone: $phone, channelCode: 'ussd',
            rawPayload: ['session_id' => $sessionId, 'text' => $rawText],
            actorRole: 'self_service',
        );

        return "END Thank you. Your grievance reference is {$grievance->reference_no}. You will receive an SMS confirmation.";
    }

    public function handleSms(string $from, string $text): array
    {
        $text = trim($text);

        if (preg_match('/^GRV\s+([A-Z0-9]+)\s+([A-Z0-9]+)\s+(.+)$/i', $text, $m)) {
            $category = $this->grievances->findCategoryByCode($m[1]);
            $district = $this->grievances->findDistrictByCode($m[2]);

            if ($category && $district) {
                $grievance = $this->register(
                    categoryId: $category->id, districtId: $district->id, description: $m[3],
                    phone: $from, channelCode: 'sms',
                    rawPayload: ['raw_text' => $text], actorRole: 'self_service',
                );

                return ['registered' => true, 'grievance' => $grievance];
            }
        }

        $inbound = InboundSms::create(['from_number' => $from, 'raw_message' => $text, 'status' => 'pending']);

        return ['registered' => false, 'inbound_sms' => $inbound];
    }

    protected function numberedMenu($items): string
    {
        return $items->values()->map(fn ($v, $i) => ($i + 1).". {$v}")->implode("\n");
    }

    public function registerPublic(array $data, array $attachments = []): Grievance
    {
        $category = GrievanceCategory::findOrFail($data['category_id']);
        $sourceGrievanceId = null;

        if (! empty($data['source_grievance_reference'])) {
            $sourceGrievanceId = Grievance::where('reference_no', $data['source_grievance_reference'])->value('id');
        }

        return DB::transaction(function () use ($data, $attachments, $category, $sourceGrievanceId) {
            $policy = DB::table('grievance_sla_policies')->where('is_active', true)->orderBy('id')->first();
            // RD can map each category to a responsible division. Until that
            // reference data is configured, use the existing district/region
            // mapping so no public case remains stranded in intake.
            $divisionId = $this->resolveRoutingDivisionId($category, $data['district_id'] ?? null);
            $status = $divisionId ? 'allocated_division' : Grievance::STATUS_SUBMITTED;
            $now = now();

            $grievance = $this->register(
                categoryId: $category->id,
                districtId: $data['district_id'] ?? null,
                description: $data['description'],
                phone: $data['is_anonymous'] ? null : ($data['contact_phone'] ?? null),
                channelCode: 'web',
                isAnonymous: $data['is_anonymous'],
                email: $data['is_anonymous'] ? null : ($data['contact_email'] ?? null),
                name: $data['is_anonymous'] ? null : ($data['contact_name'] ?? null),
                actorRole: 'self_service',
                attachments: $attachments,
            );

            $this->grievances->update($grievance, [
                'project_id' => $data['project_id'] ?? null,
                'source_grievance_id' => $sourceGrievanceId,
                'is_previously_lodged' => $data['is_previously_lodged'] ?? false,
                'is_previously_finalized' => $data['is_previously_finalized'] ?? false,
                'location_description' => $data['location_description'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'location_accuracy_meters' => $data['location_accuracy_meters'] ?? null,
                'preferred_language' => $data['preferred_language'] ?? 'en',
                'metadata' => $data['metadata'] ?? null,
                'division_id' => $divisionId,
                'status' => $status,
                'grievance_sla_policy_id' => $policy?->id,
                'first_response_due_at' => $now->copy()->addHours($policy?->acknowledgement_hours ?? 48),
                'resolution_due_at' => $now->copy()->addHours($policy?->resolution_hours ?? 168),
                'sla_due_at' => $now->copy()->addHours($policy?->acknowledgement_hours ?? 48),
            ]);

            if ($divisionId) {
                $this->grievances->recordStatus($grievance, Grievance::STATUS_SUBMITTED, $status, null, 'system', 'Automatically routed from grievance category.');
                DB::table('grievance_assignments')->insert([
                    'grievance_id' => $grievance->id, 'action' => 'allocated', 'to_division_id' => $divisionId,
                    'reason' => 'Automatic category routing.', 'created_at' => $now, 'updated_at' => $now,
                ]);
                Notification::send(User::role('Division Director')->where('division_id', $divisionId)->get(), new GrievanceAllocated($grievance));
            }

            DB::table('grievance_communications')->insert([
                'grievance_id' => $grievance->id,
                'message_type' => 'acknowledgement',
                'channel' => ! empty($data['contact_phone']) ? 'sms' : 'email',
                'recipient' => $data['is_anonymous'] ? null : ($data['contact_phone'] ?? $data['contact_email'] ?? null),
                'body' => "Grievance {$grievance->reference_no} received. You can track its status using this reference number.",
                'template_data' => json_encode(['reference_no' => $grievance->reference_no]),
                'delivery_status' => $data['is_anonymous'] ? 'not_applicable' : 'queued',
                'created_at' => $now, 'updated_at' => $now,
            ]);

            if (($data['is_previously_lodged'] ?? false) || ($data['is_previously_finalized'] ?? false)) {
                Notification::send(User::role('Responsible Manager')->get(), new GrievanceAllocated($grievance));
            }

            return $grievance->refresh();
        });
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
            return null; // contact didn't match — not this citizen's grievance
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
