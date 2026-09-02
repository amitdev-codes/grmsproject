<?php

namespace App\DataTables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

abstract class BaseDataTable
{
    /** @var class-string<Model> */
    protected string $model;

    /** @var string[] */
    protected array $searchableColumns = [];

    /** @var string[] Columns allowed in the `filter` request input. */
    protected array $filterableColumns = [];

    /** @var string[] Subset of $filterableColumns that should use LIKE instead of an exact/whereIn match. */
    protected array $textFilterColumns = [];

    /** @var string[] Columns allowed in the `sort` request input. */
    protected array $sortableColumns = [];

    /** @var array<string,string> ['attribute' => 'Column Heading'] used only for export. */
    protected array $exportColumns = [];

    /** @var string[] Relations to eager load. */
    protected array $with = [];
    protected array $withCount = [];
    protected string $defaultSort = 'id';

    protected string $defaultSortDirection = 'desc';

    protected int $defaultPerPage = 15;

    public function query(): Builder
    {
        $query = $this->model::query()->with($this->with);

        if (! empty($this->withCount)) {
            $query->withCount($this->withCount);
        }

        return $query;
    }

    /**
     * Build the payload consumed by the Inertia index page.
     */
    public function toArray(Request $request): array
    {
        $query = $this->baseQuery($request);

        $paginator = $query
            ->paginate((int) $request->integer('per_page', $this->defaultPerPage))
            ->withQueryString();

        return [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'filters' => $request->only(['search', 'filters', 'sort', 'direction']),
        ];
    }

    /**
     * Same filtering/sorting as toArray(), but unpaginated — used for export.
     */
    public function exportQuery(Request $request): Builder
    {
        return $this->baseQuery($request);
    }

    public function exportColumns(): array
    {
        return $this->exportColumns;
    }

    protected function baseQuery(Request $request): Builder
    {
        $query = $this->query();

        $this->applySearch($query, $request->string('search')->toString());
        $this->applyFilters($query, $request);
        $this->applySort($query, $request);

        return $query;
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (blank($search) || empty($this->searchableColumns)) {
            return;
        }

        $term = '%'.Str::lower($search).'%';

        $query->where(function (Builder $q) use ($term): void {
            foreach ($this->searchableColumns as $column) {
                $this->whereLike($q, $column, $term);
            }
        });
    }
    protected function whereLike(Builder $query, string $column, string $term, string $boolean = 'or'): void
    {
        if ($query->getConnection()->getDriverName() === 'pgsql') {
            $query->where($column, 'ilike', $term, $boolean);
            return;
        }

        $query->whereRaw("LOWER({$column}) LIKE ?", [$term], $boolean);
    }
    protected function applyFilters(Builder $query, Request $request): void
    {
        $filters = (array) $request->input('filters', []);

        foreach ($filters as $column => $value) {
            if (! in_array($column, $this->filterableColumns, true) || $value === null || $value === '') {
                continue;
            }

            $this->applyFilter($query, $column, $value);
        }
    }

    /**
     * Default filter behaviour. Override in a concrete DataTable for columns
     * that aren't directly whereIn-able (relations, casts, etc).
     */
    protected function applyFilter(Builder $query, string $column, mixed $value): void
    {
        $values = is_array($value)
            ? array_values(array_filter($value, fn ($v) => filled($v)))
            : [$value];

        if (empty($values)) {
            return;
        }

        if (in_array($column, $this->textFilterColumns, true)) {
            $query->where(function (Builder $q) use ($column, $values) {
                foreach ($values as $text) {
                    $this->whereLike($q, $column, '%'.Str::lower((string) $text).'%');
                }
            });

            return;
        }

        count($values) === 1
            ? $query->where($column, $values[0])
            : $query->whereIn($column, $values);
    }

    protected function applySort(Builder $query, Request $request): void
    {
        $sort = $request->string('sort', $this->defaultSort)->toString();
        $direction = strtolower($request->string('direction', $this->defaultSortDirection)->toString());
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : $this->defaultSortDirection;

        if (! in_array($sort, $this->sortableColumns, true)) {
            $sort = $this->defaultSort;
        }

        $query->orderBy($sort, $direction);
    }
}
