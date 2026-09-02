<?php

namespace Modules\Grievance\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Grievance\Interface\GrievanceRepositoryInterface;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Models\GrievanceCategory;
use Modules\Grievance\Models\GrievanceChannel;
use Modules\Grievance\Models\GrievanceMessage;
use Modules\Grievance\Models\GrievanceStatusHistory;
use Modules\Grievance\Models\ReferenceSequence;
use Modules\Master\Models\District;

class GrievanceRepository implements GrievanceRepositoryInterface
{
    public function create(array $data): Grievance
    {
        return Grievance::create($data);
    }

    public function update(Grievance $grievance, array $data): Grievance
    {
        $grievance->update($data);
        return $grievance->refresh();
    }

    public function findByReference(string $referenceNo): ?Grievance
    {
        return Grievance::where('reference_no', $referenceNo)->first();
    }

    public function findCategoryByCode(string $code): ?GrievanceCategory
    {
        return GrievanceCategory::where('code', strtoupper($code))->active()->first();
    }

    public function findDistrictByCode(string $code): ?District
    {
        return District::where('code', strtoupper($code))->first();
    }

    public function findChannelByCode(string $code): ?GrievanceChannel
    {
        return GrievanceChannel::where('code', $code)->where('is_active', true)->first();
    }

    public function nextReferenceNumber(): string
    {
        $year = now()->year;

        return DB::transaction(function () use ($year) {
            $sequence = ReferenceSequence::where('scope', 'grievance')->where('year', $year)->lockForUpdate()->first();

            if (!$sequence) {
                ReferenceSequence::create(['scope' => 'grievance', 'year' => $year, 'last_number' => 0]);
                $sequence = ReferenceSequence::where('scope', 'grievance')->where('year', $year)->lockForUpdate()->first();
            }

            $sequence->increment('last_number');

            return sprintf('GRM-%d-%06d', $year, $sequence->last_number);
        });
    }

    protected function filtered(array $filters): Builder
    {
        return Grievance::query()
            ->with(['category', 'channel', 'district'])
            ->when($filters['channel'] ?? null, fn ($q, $v) => $q->whereHas('channel', fn ($q2) => $q2->where('code', $v)))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['category_id'] ?? null, fn ($q, $v) => $q->where('grievance_category_id', $v))
            ->when($filters['district_id'] ?? null, fn ($q, $v) => $q->where('district_id', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where(function ($q2) use ($v) {
                $q2->where('reference_no', 'like', "%{$v}%")
                    ->orWhere('complainant_phone', 'like', "%{$v}%")
                    ->orWhere('description', 'like', "%{$v}%");
            }));
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->filtered($filters)->latest()->paginate($perPage)->withQueryString();
    }

    public function queryForExport(array $filters = []): Builder
    {
        return $this->filtered($filters)->latest();
    }

    public function findMany(array $ids): Collection
    {
        return Grievance::whereIn('id', $ids)->get();
    }

    public function bulkDelete(array $ids): int
    {
        return Grievance::whereIn('id', $ids)->delete(); // soft delete
    }

    public function recordStatus(Grievance $grievance, ?string $from, string $to, ?int $actorId = null, ?string $actorRole = null, ?string $reason = null): void
    {
        GrievanceStatusHistory::create([
            'grievance_id' => $grievance->id,
            'from_status' => $from,
            'to_status' => $to,
            'actor_id' => $actorId,
            'actor_role' => $actorRole,
            'reason' => $reason,
        ]);
    }
    // EloquentGrievanceRepository

    public function findForTracking(string $referenceNo, ?string $contact): ?Grievance
    {
        $grievance = Grievance::with(['category', 'channel', 'district', 'statusHistories', 'messages'])
            ->where('reference_no', $referenceNo)
            ->first();

        if (!$grievance) {
            return null;
        }

        if ($grievance->is_anonymous) {
            return $grievance; // reference number is the only credential by design
        }

        if (!$contact) {
            return null;
        }

        $contact = trim($contact);
        $matchesPhone = $grievance->complainant_phone && $this->normalizePhone($grievance->complainant_phone) === $this->normalizePhone($contact);
        $matchesEmail = $grievance->complainant_email && strcasecmp($grievance->complainant_email, $contact) === 0;

        return ($matchesPhone || $matchesEmail) ? $grievance : null;
    }

    protected function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone);
    }

    public function addMessage(Grievance $grievance, string $sender, ?int $userId, string $body): GrievanceMessage
    {
        return $grievance->messages()->create(['sender' => $sender, 'user_id' => $userId, 'body' => $body]);
    }

    public function rate(Grievance $grievance, int $rating): Grievance
    {
        $grievance->update(['satisfaction_rating' => $rating]);

        return $grievance;
    }
    // EloquentGrievanceRepository

    public function queueForResponsibleManager(int $perPage = 20): LengthAwarePaginator
    {
        return Grievance::query()
            ->with(['category', 'channel', 'district'])
            ->where('status', 'submitted')
            ->whereNull('division_id')
            ->oldest() // FIFO — oldest unallocated first
            ->paginate($perPage);
    }

    public function queueForDivision(int $divisionId, int $perPage = 20): LengthAwarePaginator
    {
        return Grievance::query()
            ->with(['category', 'channel', 'district'])
            ->where('status', 'allocated_division')
            ->where('division_id', $divisionId)
            ->oldest()
            ->paginate($perPage);
    }

    public function queueForSection(int $sectionId, int $perPage = 20): LengthAwarePaginator
    {
        return Grievance::query()
            ->with(['category', 'channel', 'district'])
            ->where('status', 'allocated_section')
            ->where('section_id', $sectionId)
            ->oldest()
            ->paginate($perPage);
    }
}
