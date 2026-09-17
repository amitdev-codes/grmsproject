<?php

namespace Modules\Master\Services;

use Illuminate\Http\Request;
use Modules\Master\DataTable\ProjectTypeDataTable;
use Modules\Master\Models\ProjectType;
use Modules\Master\Repository\ProjectTypeRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectTypeService
{
    public function __construct(
        protected ProjectTypeRepository $repository,
        protected ProjectTypeDataTable $dataTable
    ) {}

    public function table(Request $request): array
    {
        return $this->dataTable->toArray($request);
    }

    public function forCreate(): array
    {
        return ['projectType' => null];
    }

    public function forEdit(ProjectType $projectType): array
    {
        return ['projectType' => $projectType];
    }

    public function store(array $data): ProjectType
    {
        return $this->repository->create($data);
    }

    public function update(ProjectType $projectType, array $data): ProjectType
    {
        $this->repository->update($projectType, $data);
        return $projectType->refresh();
    }

    public function destroy(ProjectType $projectType): bool
    {
        return $this->repository->delete($projectType);
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
            'project-type-' . now()->format('Y-m-d_His') . '.xlsx',
        );
    }
}