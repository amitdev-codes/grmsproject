<?php

namespace Modules\Grievance\Interface;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Models\GrievanceCategory;
use Modules\Grievance\Models\GrievanceChannel;
use Modules\Grievance\Models\GrievanceMessage;
use Modules\Master\Models\District;

interface GrievanceRepositoryInterface
{
    public function create(array $data): Grievance;

    public function update(Grievance $grievance, array $data): Grievance;

    public function findByReference(string $referenceNo): ?Grievance;

    public function findCategoryByCode(string $code): ?GrievanceCategory;

    public function findDistrictByCode(string $code): ?District;

    public function findChannelByCode(string $code): ?GrievanceChannel;

    public function nextReferenceNumber(): string;

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function queryForExport(array $filters = []): Builder;

    public function findMany(array $ids): Collection;

    public function bulkDelete(array $ids): int;

    public function recordStatus(Grievance $grievance, ?string $from, string $to, ?int $actorId = null, ?string $actorRole = null, ?string $reason = null): void;

    public function findForTracking(string $referenceNo, ?string $contact): ?Grievance;

    public function addMessage(Grievance $grievance, string $sender, ?int $userId, string $body): GrievanceMessage;

    public function rate(Grievance $grievance, int $rating): Grievance;
    // interface
    public function queueForResponsibleManager(int $perPage = 20): LengthAwarePaginator;
    public function queueForDivision(int $divisionId, int $perPage = 20): LengthAwarePaginator;
    public function queueForSection(int $sectionId, int $perPage = 20): LengthAwarePaginator;
}
