<?php

namespace Modules\Grievance\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Grievance\Datatable\ResolutionDataTable;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Models\GrievanceMessage;
use Modules\Grievance\Models\Resolution;
use Modules\Grievance\Repositories\ResolutionRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResolutionService
{
    public function __construct(
        protected ResolutionRepository $repository,
        protected ResolutionDataTable $dataTable,
    ) {}
    public function table(Request $request): array
    {
        return $this->dataTable->toArray($request);
    }
    public function forCreate(): array
    {
        return [
            'resolution' => null,
            ...$this->lookups(),
        ];
    }
    public function forEdit(Resolution $resolution): array
    {
        return [
            'resolution' => $resolution->load(['grievance', 'user']),
            ...$this->lookups(),
        ];
    }
    protected function lookups(): array
    {
        return [
            'grievances' => $this->grievanceOptions(),
            'users' => $this->userOptions(),
        ];
    }
    protected function grievanceOptions(): Collection
    {
        return Grievance::query()
            ->select(['id', 'reference_number'])
            ->orderBy('reference_number')
            ->get();
    }

    protected function userOptions(): Collection
    {
        return User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();
    }
    public function store(array $data): Resolution
    {
        return $this->repository->create($data);
    }
    public function update(Resolution $resolution, array $data): Resolution
    {
        $this->repository->update($resolution, $data);

        return $resolution->refresh();
    }
    public function destroy(Resolution $resolution): bool
    {
        return $this->repository->delete($resolution);
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
            'grievance-messages-'.now()->format('Y-m-d_His').'.xlsx',
        );
    }
}
