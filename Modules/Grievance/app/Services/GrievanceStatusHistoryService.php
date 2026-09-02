<?php

namespace Modules\Grievance\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Grievance\Datatable\GrievanceStatusHistoryDataTable;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Models\GrievanceStatusHistory;
use Modules\Grievance\Repositories\GrievanceStatusHistoryRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GrievanceStatusHistoryService
{
    public function __construct(
        protected GrievanceStatusHistoryRepository $repository,
        protected GrievanceStatusHistoryDataTable $dataTable,
    ) {}

    public function table(Request $request): array
    {
        return $this->dataTable->toArray($request);
    }
    public function forCreate(): array
    {
        return [
            'grievanceStatusHistory' => null,
            ...$this->lookups(),
        ];
    }

    public function forEdit(GrievanceStatusHistory $grievanceStatusHistory): array
    {
        return [
            'grievanceStatusHistory' => $grievanceStatusHistory->load(['grievance', 'changedBy']),
            ...$this->lookups(),
        ];
    }
    protected function lookups(): array
    {
        return [
            'grievances' => $this->grievanceOptions(),
            'changedBy' => $this->officerOptions(),
        ];
    }
    protected function grievanceOptions(): Collection
    {
        return Grievance::query()
            ->select(['id', 'reference_number'])
            ->orderBy('reference_number')
            ->get();
    }

    protected function officerOptions(): Collection
    {
        return User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();
    }
    public function store(array $data): GrievanceStatusHistory
    {
        return $this->repository->create($data);
    }
    public function update(GrievanceStatusHistory $grievanceStatusHistory, array $data): GrievanceStatusHistory
    {
        $this->repository->update($grievanceStatusHistory, $data);

        return $grievanceStatusHistory->refresh();
    }

    public function destroy(GrievanceStatusHistory $grievanceStatusHistory): bool
    {
        return $this->repository->delete($grievanceStatusHistory);
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
            'grievance-status-history-'.now()->format('Y-m-d_His').'.xlsx',
        );
    }

}
