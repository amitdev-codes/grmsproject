<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Frontend\Http\Requests\StoreGrievanceRequest;
use Modules\Grievance\Enums\GrievanceStatus;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Models\GrievanceCategory;
use Modules\Grievance\Models\GrievanceMessage;
use Modules\Grievance\Models\GrievanceStatusHistory;

class GrievanceController extends Controller
{
    /**
     * POST /api/grievances
     * Matches submitGrievance() / buildSubmitFormData() in file-grievance.tsx.
     */
    public function store(StoreGrievanceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $grievance = DB::transaction(function () use ($validated, $request) {
            $category = GrievanceCategory::findOrFail($validated['category_id']);

            $grievance = Grievance::create([
                'reference_number' => $this->generateReferenceNumber(),
                'user_id' => $request->user()?->id,
                'is_anonymous' => $validated['is_anonymous'],
                'contact_name' => $validated['is_anonymous'] ? null : ($validated['contact_name'] ?? null),
                'contact_phone' => $validated['is_anonymous'] ? null : ($validated['contact_phone'] ?? null),
                'contact_email' => $validated['is_anonymous'] ? null : ($validated['contact_email'] ?? null),
                'category_id' => $category->id,
                'district_id' => $validated['district_id'] ?? null,
                'division_id' => $validated['division_id'] ?? $category->division_id,
                'description' => $validated['description'],
                'location_description' => $validated['location_description'] ?? null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'metadata' => $validated['metadata'] ?? null,
                'status' => GrievanceStatus::Submitted->value,
                'priority' => $category->is_sensitive ? 'high' : 'normal',
                'submitted_via' => $validated['submitted_via'] ?? 'web',
                'sla_due_at' => $this->initialSlaDueAt($category),
            ]);

            GrievanceStatusHistory::create([
                'grievance_id' => $grievance->id,
                'from_status' => null,
                'to_status' => GrievanceStatus::Submitted->value,
                'changed_by' => $request->user()?->id,
                'note' => null,
            ]);

            foreach ($request->file('attachments', []) as $file) {
                $path = $file->store("grievance-attachments/{$grievance->reference_number}", 'public');

                GrievanceAttachment::create([
                    'grievance_id' => $grievance->id,
                    'url' => Storage::disk('public')->url($path),
                    'original_filename' => $file->getClientOriginalName(),
                ]);
            }

            return $grievance;
        });

        // Shape matches what submitGrievance() in the frontend reads: reference_number, sla_due_at.
        return response()->json([
            'data' => [
                'reference_number' => $grievance->reference_number,
                'sla_due_at' => $grievance->sla_due_at?->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * GET /api/grievances/track?ref=&contact=
     * Matches trackGrievance() in file-grievance.tsx.
     */
    public function track(Request $request): JsonResponse
    {
        $request->validate([
            'ref' => ['required', 'string'],
            'contact' => ['nullable', 'string'],
        ]);

        $grievance = Grievance::with([
            'category',
            'district',
            'division',
            'statusHistories' => fn ($q) => $q->orderBy('created_at'),
            'messages' => fn ($q) => $q->where('is_internal', false)->orderBy('created_at'),
            'attachments',
            'escalations' => fn ($q) => $q->latest('escalated_at'),
            'resolution',
        ])
            ->where('reference_number', strtoupper(trim($request->string('ref'))))
            ->first();

        if (! $grievance || ! $this->contactMatches($grievance, $request->string('contact')->trim()->value())) {
            return response()->json(['message' => 'No matching case found.'], 404);
        }

        return response()->json($this->transformGrievance($grievance));
    }

    /**
     * POST /api/grievances/{reference}/messages
     * Matches sendCitizenMessage() in file-grievance.tsx.
     */
    public function addMessage(Request $request, string $reference): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $grievance = Grievance::where('reference_number', strtoupper($reference))->firstOrFail();

        abort_if(
            in_array($grievance->status, [GrievanceStatus::Closed->value, GrievanceStatus::Rejected->value], true),
            422,
            'This case is closed and no longer accepting messages.'
        );

        $message = GrievanceMessage::create([
            'grievance_id' => $grievance->id,
            'user_id' => $request->user()?->id,
            'sender_type' => 'complainant',
            'message' => $validated['body'],
            'is_internal' => false,
        ]);

        return response()->json([
            'id' => $message->id,
            'sender' => 'citizen',
            'body' => $message->message,
            'created_at' => $message->created_at->toIso8601String(),
        ], 201);
    }

    /**
     * POST /api/grievances/{reference}/rating
     * Matches submitRating() in file-grievance.tsx.
     */
    public function rate(Request $request, string $reference): JsonResponse
    {
        $validated = $request->validate([
            'satisfaction_rating' => ['required', 'integer', 'between:1,5'],
        ]);

        $grievance = Grievance::where('reference_number', strtoupper($reference))
            ->whereIn('status', [GrievanceStatus::Resolved->value, GrievanceStatus::Closed->value])
            ->firstOrFail();

        $grievance->update(['satisfaction_rating' => $validated['satisfaction_rating']]);

        return response()->json(['satisfaction_rating' => $grievance->satisfaction_rating]);
    }

    /**
     * Reference numbers look like GRM-2026-01147: prefix + year + 5-digit sequence.
     * A Postgres advisory lock scoped to the year serializes concurrent inserts so two
     * requests can never be handed the same number — even the very first of the year,
     * where a row-level lock on `grievances` would have nothing yet to lock.
     */
    private function generateReferenceNumber(): string
    {
        $year = now()->year;

        DB::statement('SELECT pg_advisory_xact_lock(?)', [crc32("grievance_ref_{$year}")]);

        $prefix = "GRM-{$year}-";

        $last = Grievance::withTrashed()
            ->where('reference_number', 'like', "{$prefix}%")
            ->orderByDesc('reference_number')
            ->value('reference_number');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    /**
     * First SLA checkpoint. Escalation levels are 48hr / 5day / 7day (see
     * grievance_escalations.escalation_level) — this sets the level-1 target;
     * escalating a case pushes sla_due_at out to the next level elsewhere in the workflow.
     */
    private function initialSlaDueAt(GrievanceCategory $category): Carbon
    {
        return $category->is_sensitive ? now()->addHours(24) : now()->addHours(48);
    }

    private function contactMatches(?Grievance $grievance, string $contact): bool
    {
        if (! $grievance) {
            return false;
        }

        if ($grievance->is_anonymous) {
            return true; // anonymous cases carry no contact to check against
        }

        return $contact !== ''
            && ($contact === $grievance->contact_phone || $contact === $grievance->contact_email);
    }

    /**
     * Shapes the Eloquent model into the TrackedGrievance JSON the frontend expects.
     */
    private function transformGrievance(Grievance $grievance): array
    {
        $escalation = $grievance->escalations->first();

        return [
            'reference_number' => $grievance->reference_number,
            'status' => $grievance->status,
            'priority' => $grievance->priority,
            'category_name' => $grievance->category->name,
            'division_name' => $grievance->division?->name,
            'section_name' => $grievance->district?->name,
            'description' => $grievance->description,
            'location_description' => $grievance->location_description,
            'sla_due_at' => $grievance->sla_due_at?->toIso8601String(),
            'satisfaction_rating' => $grievance->satisfaction_rating,
            'history' => $grievance->statusHistories->map(fn ($h) => [
                'id' => $h->id,
                'from_status' => $h->from_status,
                'to_status' => $h->to_status,
                'note' => $h->note,
                'changed_at' => $h->created_at->toIso8601String(),
            ]),
            'messages' => $grievance->messages->map(fn ($m) => [
                'id' => $m->id,
                'sender' => $m->sender_type === 'officer' ? 'officer' : 'citizen',
                'body' => $m->message,
                'created_at' => $m->created_at->toIso8601String(),
            ]),
            'escalation' => $escalation ? [
                'id' => $escalation->id,
                'level' => match ($escalation->escalation_level) {
                    1 => 'Zonal Officer',
                    2 => 'Regional Head',
                    default => 'Director',
                },
                'reason' => $escalation->reason,
                'escalated_at' => $escalation->escalated_at->toIso8601String(),
            ] : null,
            'resolution' => $grievance->resolution ? [
                'id' => $grievance->resolution->id,
                'summary' => $grievance->resolution->summary,
                'resolved_at' => $grievance->resolution->resolved_at->toIso8601String(),
            ] : null,
            'attachments' => $grievance->attachments->map(fn ($a) => [
                'id' => $a->id,
                'url' => $a->url,
                'original_filename' => $a->original_filename,
            ]),
        ];
    }
}
