<?php

namespace Modules\Master\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Master\DataTable\SectionDataTable;
use Modules\Master\Models\Division;
use Modules\Master\Models\Section;
use Modules\Master\Repository\SectionRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SectionService
{
    public function __construct(
        protected SectionRepository $repository,
        protected SectionDataTable $dataTable
    ) {}

    public function table(Request $request): array
    {
        return $this->dataTable->toArray($request);
    }

    public function forCreate(): array
    {
        return ['section' => null, ...$this->lookups()];
    }

    public function forEdit(Section $section): array
    {
        return ['section' => $section->load(['division'])];
    }

    protected function lookups(): array
    {
        return [
            'divisions' => $this->divisionOptions(),
        ];
    }

    protected function divisionOptions(): Collection
    {
        return Division::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();
    }

    public function store(array $data): Section
    {
        return $this->repository->create($data);
    }

    public function update(Section $section, array $data): Section
    {
        $this->repository->update($section, $data);

        return $section->refresh();
    }

    public function destroy(Section $section): bool
    {
        return $this->repository->delete($section);
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
            'section-'.now()->format('Y-m-d_His').'.xlsx',
        );
    }
}
