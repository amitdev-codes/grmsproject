<?php

namespace Modules\Master\Services;

use Illuminate\Http\Request;
use Modules\Master\DataTable\DivisionDataTable;
use Modules\Master\Models\Division;
use Modules\Master\Repository\DivisionRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DivisionService
{
    public function __construct(
        protected DivisionRepository $repository,
        protected DivisionDataTable $dataTable
    ){}
    public function table(Request $request): array
    {
        return $this->dataTable->toArray($request);
    }
    public function forCreate(): array
    {
        return ['division' => null];
    }
    public function forEdit(Division $division): array
    {
        return ['division' => $division];
    }
    public function store(array $data): Division
    {
        return $this->repository->create($data);
    }
    public function update(Division $division, array $data): Division
    {
        $this->repository->update($division, $data);
        return $division->refresh();
    }
    public function destroy(Division $division): bool
    {
        return $this->repository->delete($division);
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
            'division-'.now()->format('Y-m-d_His').'.xlsx',
        );
    }

}
