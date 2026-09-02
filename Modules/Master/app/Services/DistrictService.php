<?php

namespace Modules\Master\Services;

use Illuminate\Http\Request;
use Modules\Master\DataTable\DistrictDataTable;
use Modules\Master\Models\District;
use Modules\Master\Repository\DistrictRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DistrictService
{
    public function __construct(
        protected DistrictRepository $repository,
        protected DistrictDataTable $dataTable
    ){}
    public function table(Request $request): array
    {
        return $this->dataTable->toArray($request);
    }
    public function forCreate(): array
    {
        return ['district' => null];
    }
    public function forEdit(District $district): array
    {
        return ['district' => $district];
    }
    public function store(array $data): District
    {
        return $this->repository->create($data);
    }
    public function update(District $district, array $data): District
    {
        $this->repository->update($district, $data);
        return $district->refresh();
    }
    public function destroy(District $district): bool
    {
        return $this->repository->delete($district);
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
            'district-'.now()->format('Y-m-d_His').'.xlsx',
        );
    }
}
