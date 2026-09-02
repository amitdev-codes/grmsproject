<?php

namespace App\Repositories;

use App\Exports\DataTableExport;
use App\Imports\DataTableImport;
use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

abstract class BaseRepository implements RepositoryInterface
{
    public function __construct(protected Model $model) {}

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function all(array $with = []): Collection
    {
        return $this->query()->with($with)->get();
    }

    public function paginate(int $perPage = 15, array $with = []): LengthAwarePaginator
    {
        return $this->query()->with($with)->paginate($perPage)->withQueryString();
    }

    public function find(int|string $id, array $with = []): ?Model
    {
        return $this->query()->with($with)->find($id);
    }

    public function findOrFail(int|string $id, array $with = []): Model
    {
        return $this->query()->with($with)->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model->refresh();
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    public function bulkDelete(array $ids): int
    {
        return $this->query()->whereKey($ids)->get()->each->delete()->count();
    }

    public function export(array $columns, ?Builder $query = null, string $fileName = 'export.xlsx'): BinaryFileResponse
    {
        $rows = ($query ?? $this->query())->get();

        return Excel::download(new DataTableExport($rows, $columns), $fileName);
    }

    public function import(UploadedFile $file, array $map, array $rules): array
    {
        $import = new DataTableImport($map, $rules);

        Excel::import($import, $file);

        return [
            'imported' => $import->imported,
            'failures' => $import->failures,
        ];
    }
}
