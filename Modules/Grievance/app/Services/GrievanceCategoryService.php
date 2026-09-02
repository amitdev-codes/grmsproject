<?php

namespace Modules\Grievance\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Modules\Grievance\Datatable\GrievanceCategoryDataTable;
use Modules\Grievance\Models\GrievanceCategory;
use Modules\Grievance\Repositories\GrievanceCategoryRepository;
use Modules\Master\Models\Division;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GrievanceCategoryService
{
    public function __construct(
        protected GrievanceCategoryRepository $repository,
        protected GrievanceCategoryDataTable $dataTable,
    ) {}

    public function table(Request $request): array
    {
        return $this->dataTable->toArray($request);
    }

    public function forCreate(): array
    {
        return [
            'grievanceCategory' => null,
            'divisions' => Division::orderBy('name')->get(['id', 'name']),
        ];
    }

    public function forEdit(GrievanceCategory $grievanceCategory): array
    {
        return [
            'grievanceCategory' => $grievanceCategory,
            'divisions' => $grievanceCategory->division_id
                ? Division::orderBy('name')->get(['id', 'name'])
                :Division::orderBy('name')->get(['id', 'name']),
        ];
    }

    public function store(array $data): GrievanceCategory
    {
        return $this->repository->create($data);
    }

    public function update(GrievanceCategory $grievanceCategory, array $data): GrievanceCategory
    {
        $this->repository->update($grievanceCategory, $data);

        return $grievanceCategory->refresh();
    }

    public function destroy(GrievanceCategory $grievanceCategory): bool
    {
        return $this->repository->delete($grievanceCategory);
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
            'grievanceCategory-'.now()->format('Y-m-d_His').'.xlsx',
        );
    }

    /**
     * @return array{created: int, failures: array}
     */
    public function import(UploadedFile $file): array
    {
        $parsed = $this->repository->import($file, $this->importMap(), $this->importRules());

        $created = 0;

        foreach ($parsed['imported'] as $row) {
            $this->repository->create([
                'name' => $row['name'],
                'name_st' => $row['name_st'] ?? null,
                'slug' => $row['slug'],
                'default_division_id' => $row['default_division_id'] ?? null,
                'icon' => $row['icon'] ?? null,
                'is_sensitive' => $row['is_sensitive'] ?? false,
                'is_active' => $row['is_active'] ?? true,
            ]);
            $created++;
        }

        return [
            'created' => $created,
            'failures' => $parsed['failures'],
        ];
    }

    protected function importMap(): array
    {
        return [
            'name' => 'name',
            'name_st' => 'name_st',
            'slug' => 'slug',
            'default_division_id' => 'default_division_id',
            'is_sensitive' => 'is_sensitive',
        ];
    }

    protected function importRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'name_st' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:grievance_categories,slug'],
            'default_division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'is_sensitive' => ['boolean'],
        ];
    }
}
