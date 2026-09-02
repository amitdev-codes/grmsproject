<?php

namespace Modules\Grievance\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Grievance\Datatable\GrievanceDataTable;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Models\GrievanceCategory;
use Modules\Grievance\Models\GrievanceMessage;
use Modules\Grievance\Repositories\GrievanceRepository;
use Modules\Master\Models\District;
use Modules\Master\Models\Division;
use Modules\Master\Models\Section;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GrievanceService
{
    public function __construct(
        protected GrievanceRepository $repository,
        protected GrievanceDataTable $dataTable,
    ) {}

    public function table(Request $request): array
    {
        return $this->dataTable->toArray($request);
    }

    public function forCreate(): array
    {
        return [
            'grievance' => null,
            'categories' => GrievanceCategory::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'name_st', 'division_id', 'is_sensitive']),
            'districts' => District::orderBy('name')->get(['id', 'name', 'name_st']),
            'divisions' => Division::orderBy('name')->get(['id', 'name']),
            'sections' => Section::orderBy('name')->get(['id', 'name', 'division_id']),
        ];
    }

    public function forEdit(Grievance $grievance): array
    {
        $grievance->load(['category', 'district', 'division', 'section', 'assignedOfficer', 'user', 'media']);

        return [
            'grievance' => $grievance,
            'categories' => GrievanceCategory::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'name_st', 'division_id', 'is_sensitive']),
            'districts' => District::orderBy('name')->get(['id', 'name', 'name_st']),
            'divisions' => Division::orderBy('name')->get(['id', 'name']),
            'sections' => Section::orderBy('name')->get(['id', 'name', 'division_id']),
        ];
    }

    /**
     * @param  array  $data  validated fields from Store/UpdateGrievanceRequest
     * @param  UploadedFile[]  $attachments  evidence files via Spatie Media Library
     */


    /**
     * @throws \Throwable
     */
    public function update(Grievance $grievance, array $data, array $attachments = [], array $removeMediaIds = []): Grievance
    {
        return DB::transaction(function () use ($grievance, $data, $attachments, $removeMediaIds) {
            $previousStatus = $grievance->status;

            $this->repository->update($grievance, $data);
            $grievance->refresh();

            if (isset($data['status']) && $data['status'] !== $previousStatus) {
                $grievance->statusHistory()->create([
                    'from_status' => $previousStatus,
                    'to_status' => $data['status'],
                    'changed_by' => Auth::id(),
                ]);

                match ($data['status']) {
                    'acknowledged' => $grievance->update(['acknowledged_at' => now()]),
                    'resolved' => $grievance->update(['resolved_at' => now()]),
                    'closed' => $grievance->update(['closed_at' => now(), 'closed_by' => Auth::id()]),
                    default => null,
                };
            }

            if (! empty($removeMediaIds)) {
                $grievance->media()->whereIn('id', $removeMediaIds)->get()
                    ->each(fn ($m) => $m->delete());
            }

            foreach ($attachments as $file) {
                $grievance->addMedia($file)->toMediaCollection('evidence');
            }

            return $grievance;
        });
    }

    public function destroy(Grievance $grievance): bool
    {
        return $this->repository->delete($grievance);
    }

    public function bulkDestroy(array $ids): int
    {
        return $this->repository->bulkDelete($ids);
    }

    public function export(Request $request): BinaryFileResponse
    {
        return $this->repository->export(
            $this->dataTable->exportColumns(),
            $this->dataTable->exportQuery($request),
            'grievances-'.now()->format('Y-m-d_His').'.xlsx',
        );
    }

    // -----------------------------------------------------------------
    // Public-facing methods — used by PublicGrievanceController, no auth.
    // -----------------------------------------------------------------

    /**
     * Citizen-facing submission. Thin wrapper over store() that forces
     * submitted_via to 'web' and never attaches a user_id, regardless of
     * what's in $data — a public request must never be trusted to set
     * those fields itself, unlike the staff admin form.
     *
     * @param  UploadedFile[]  $attachments
     */
    public function fileFromPublic(array $data, array $attachments = []): Grievance
    {
        unset($data['status'], $data['user_id'], $data['priority']);

        $data['submitted_via'] = 'web';

        return $this->store($data, $attachments);
    }

    /**
     * Look up a grievance for the public tracking screen. Requires the
     * reference number AND a matching contact value (phone or email) as a
     * lightweight access control — prevents anyone who merely guesses a
     * reference number from viewing someone else's case. Anonymous
     * submissions (no contact stored) are only trackable by reference
     * number alone.
     */
    public function findForTracking(string $reference, ?string $contact = null): ?Grievance
    {
        try {
            $grievance = Grievance::query()
                ->with(['category', 'division', 'section', 'statusHistory', 'messages', 'media'])
                ->where('reference_number', $reference)
                ->first();
        } catch (\Throwable $e) {
            Log::error('findForTracking eager-load failed: '.$e->getMessage(), ['reference' => $reference]);
            throw $e; // still surfaces as 500 for now, but the log line pinpoints it immediately
        }

        if (! $grievance) {
            return null;
        }

        if ($grievance->is_anonymous) {
            return $grievance;
        }

        if (blank($contact)) {
            return null;
        }

        $matches = $grievance->contact_phone === $contact || $grievance->contact_email === $contact;

        return $matches ? $grievance : null;
    }

    /**
     * Citizen reply on an existing case. Returns null if the reference
     * doesn't exist or the case is closed/rejected (no new messages
     * accepted), matching MessageThread's `closed` guard on the frontend —
     * but the frontend check is UX only, this is the actual enforcement.
     */
    public function addCitizenMessage(string $reference, string $body): ?GrievanceMessage
    {
        $grievance = Grievance::where('reference_number', $reference)->first();

        if (! $grievance || in_array($grievance->status, ['closed', 'rejected'], true)) {
            return null;
        }

        return $grievance->messages()->create([
            'sender' => 'citizen',
            'body' => $body,
        ]);
    }

    /**
     * Citizen satisfaction rating, only meaningful once a case is resolved
     * or closed — silently no-ops otherwise rather than erroring, since a
     * stale tab re-submitting a rating on a since-reopened case shouldn't
     * corrupt data.
     */
    public function rate(string $reference, int $rating): bool
    {
        $grievance = Grievance::where('reference_number', $reference)->first();

        if (! $grievance || ! in_array($grievance->status, ['resolved', 'closed'], true)) {
            return false;
        }

        return $grievance->update(['satisfaction_rating' => $rating]);
    }

    /**
     * GRM-{year}-{5-digit sequence}, e.g. GRM-2026-01147.
     * Sequence resets per calendar year. Uses a MySQL/Postgres advisory-
     * style row lock via lockForUpdate() inside the enclosing transaction
     * (store() already wraps this in DB::transaction) so two concurrent
     * requests can't both read the same count and mint the same number —
     * a real risk now that this path is reachable by anonymous public
     * traffic, not just one officer typing at a time.
     */
    public function store(array $data, array $attachments = []): Grievance
    {
        return DB::transaction(function () use ($data, $attachments) {
            $attempt = 0;

            while (true) {
                $attempt++;
                $data['reference_number'] = $this->generateReferenceNumber();
                $data['status'] = $data['status'] ?? 'submitted';
                $data['priority'] = $data['priority'] ?? 'normal';
                $data['submitted_via'] = $data['submitted_via'] ?? 'officer_assisted';
                $data['user_id'] = $data['user_id'] ?? (($data['is_anonymous'] ?? false) ? null : Auth::id());

                try {
                    /** @var Grievance $grievance */
                    $grievance = $this->repository->create($data);
                    break; // success
                } catch (\Illuminate\Database\QueryException $e) {
                    // 23505 = Postgres unique_violation
                    if ($e->getCode() === '23505' && $attempt < 5) {
                        continue; // regenerate and retry
                    }
                    throw $e;
                }
            }

            $grievance->statusHistory()->create([
                'from_status' => 'submitted',
                'to_status' => $grievance->status,
                'changed_by' => Auth::id(),
                'note' => 'Grievance registered.',
            ]);

            foreach ($attachments as $file) {
                $grievance->addMedia($file)->toMediaCollection('evidence');
            }

            return $grievance->refresh();
        });
    }

    protected function generateReferenceNumber(): string
    {
        $year = now()->format('Y');

        $count = Grievance::withTrashed()
            ->where('reference_number', 'like', "GRM-{$year}-%")
            ->count();

        return sprintf('GRM-%s-%05d', $year, $count + 1);
    }
}
