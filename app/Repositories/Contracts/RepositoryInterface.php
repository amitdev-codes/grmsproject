<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

interface RepositoryInterface
{
    /**
     * Fresh query builder for the underlying model.
     */
    public function query(): Builder;

    /**
     * Fetch all records, optionally eager loading relations.
     */
    public function all(array $with = []): Collection;

    /**
     * Paginate records, optionally eager loading relations.
     */
    public function paginate(int $perPage = 15, array $with = []): LengthAwarePaginator;

    /**
     * Find a record by primary key, or null if it doesn't exist.
     */
    public function find(int|string $id, array $with = []): ?Model;

    /**
     * Find a record by primary key or throw a 404.
     */
    public function findOrFail(int|string $id, array $with = []): Model;

    /**
     * Create a new record.
     */
    public function create(array $data): Model;

    /**
     * Update an existing record.
     */
    public function update(Model $model, array $data): Model;

    /**
     * Delete a single record.
     */
    public function delete(Model $model): bool;

    /**
     * Delete many records by primary key. Returns the number deleted.
     */
    public function bulkDelete(array $ids): int;

    /**
     * Export a query's results to a spreadsheet download.
     *
     * @param  array<string,string>  $columns  ['attribute' => 'Column Heading']
     */
    public function export(array $columns, ?Builder $query = null, string $fileName = 'export.xlsx'): BinaryFileResponse;

    /**
     * Parse + validate an uploaded spreadsheet against a column map and rule set.
     * Does NOT persist anything — persistence is a service-layer concern because
     * it usually needs business logic (password hashing, role assignment, etc).
     *
     * @param  array<string,string>  $map    ['target_attribute' => 'source_header']
     * @param  array<string,string>  $rules  Laravel validation rules keyed by target attribute
     * @return array{imported: array<int, array<string, mixed>>, failures: array<int, array{row: int, errors: array}>}
     */
    public function import(UploadedFile $file, array $map, array $rules): array;
}
