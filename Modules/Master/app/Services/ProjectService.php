<?php

namespace Modules\Master\Services;

use Illuminate\Http\Request;
use Modules\Master\DataTable\ProjectDataTable;
use Modules\Master\Models\Project;
use Modules\Master\Repository\ProjectRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectService
{
    public function __construct(
        protected ProjectRepository $repository,
        protected ProjectDataTable $dataTable
    ) {}

    public function table(Request $request): array
    {
        return $this->dataTable->toArray($request);
    }

    public function forCreate(): array
    {
        return ['project' => null];
    }

    public function forEdit(Project $project): array
    {
        return ['project' => $project];
    }

    public function store(array $data): Project
    {
        return $this->repository->create($data);
    }

    public function update(Project $project, array $data): Project
    {
        $this->repository->update($project, $data);
        return $project->refresh();
    }

    public function destroy(Project $project): bool
    {
        return $this->repository->delete($project);
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
            'project-' . now()->format('Y-m-d_His') . '.xlsx',
        );
    }
}