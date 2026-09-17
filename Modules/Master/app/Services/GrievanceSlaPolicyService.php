<?php

namespace Modules\Master\Services;

use Illuminate\Http\Request;
use Modules\Master\DataTable\GrievanceSlaPolicyDataTable;
use Modules\Master\Models\GrievanceSlaPolicy;
use Modules\Master\Repository\GrievanceSlaPolicyRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GrievanceSlaPolicyService
{
    public function __construct(
        protected GrievanceSlaPolicyRepository $repository,
        protected GrievanceSlaPolicyDataTable $dataTable
    ) {}

    public function table(Request $request): array
    {
        return $this->dataTable->toArray($request);
    }

    public function forCreate(): array
    {
        return ['grievanceSlaPolicy' => null];
    }

    public function forEdit(GrievanceSlaPolicy $grievanceSlaPolicy): array
    {
        return ['grievanceSlaPolicy' => $grievanceSlaPolicy];
    }

    public function store(array $data): GrievanceSlaPolicy
    {
        return $this->repository->create($data);
    }

    public function update(GrievanceSlaPolicy $grievanceSlaPolicy, array $data): GrievanceSlaPolicy
    {
        $this->repository->update($grievanceSlaPolicy, $data);
        return $grievanceSlaPolicy->refresh();
    }

    public function destroy(GrievanceSlaPolicy $grievanceSlaPolicy): bool
    {
        return $this->repository->delete($grievanceSlaPolicy);
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
            'grievance-sla-policy-' . now()->format('Y-m-d_His') . '.xlsx',
        );
    }
}