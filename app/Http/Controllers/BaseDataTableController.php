<?php

namespace App\Http\Controllers;

use App\Exports\DynamicExport;
use App\Imports\DynamicImport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Extend this controller for any Eloquent model to get a fully dynamic,
 * frontend-driven index (search, faceted filters, sort, pagination),
 * export (xlsx/csv/pdf/print), import, destroy and bulk-destroy.
 *
 * Child controllers only need to declare a handful of properties and,
 * optionally, override a couple of hook methods.
 */
abstract class BaseDataTableController extends Controller
{
    /** Fully qualified model class, e.g. \App\Models\User::class */
    protected string $model;

    /** Columns allowed for the global "search" box (LIKE %term%) */
    protected array $searchableColumns = [];

    /** Columns allowed for exact/`whereIn` column filters (faceted filters) */
    protected array $filterableColumns = [];

    /** Columns allowed to be sorted on (whitelist to avoid SQL injection via column name) */
    protected array $sortableColumns = ['id'];

    /** Columns exported when calling export() -> ['db_column' => 'Excel/PDF Heading'] */
    protected array $exportColumns = [];

    /** Eager-load relations */
    protected array $with = [];

    protected string $defaultSort = 'id';

    protected string $defaultSortDirection = 'desc';

    protected int $defaultPerPage = 10;

    protected array $perPageOptions = [10, 25, 50, 100];

    /**
     * Hook for adding relation-based / custom search conditions.
     * Override in child controller if needed. No-op by default.
     */
    protected function applyExtraSearch(Builder $query, string $search): void
    {
        //
    }

    /**
     * Hook to transform/validate a single filter value before it is applied.
     * Override to support things like date ranges, e.g. filters[created_at] = ["2024-01-01","2024-01-31"]
     */
    protected function applyFilter(Builder $query, string $column, mixed $value): void
    {
        if (is_array($value)) {
            $query->whereIn($column, array_values($value));
        } else {
            $query->where($column, $value);
        }
    }

    /**
     * Builds the base query. Override to add global scopes / relations / withCount etc.
     */
    protected function baseQuery(): Builder
    {
        /** @var Model $model */
        $model = new $this->model;
        $query = $model->newQuery();

        if (! empty($this->with)) {
            $query->with($this->with);
        }

        return $query;
    }

    /**
     * Applies search + filters + sort from the request. Reused by index() and export().
     */
    protected function applyQueryParams(Builder $query, Request $request): Builder
    {
        // Global / advanced search
        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function (Builder $q) use ($search) {
                foreach ($this->searchableColumns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
                $this->applyExtraSearch($q, $search);
            });
        }

        // Faceted / column filters -> filters[column] = value | value[]
        $filters = (array) $request->input('filters', []);
        foreach ($filters as $column => $value) {
            if ($value === null || $value === '' || (is_array($value) && count($value) === 0)) {
                continue;
            }
            if (! in_array($column, $this->filterableColumns, true)) {
                continue;
            }
            $this->applyFilter($query, $column, $value);
        }

        // Sorting
        $sort = (string) $request->input('sort', $this->defaultSort);
        $order = strtolower((string) $request->input('order', $this->defaultSortDirection)) === 'asc' ? 'asc' : 'desc';

        if (! in_array($sort, $this->sortableColumns, true)) {
            $sort = $this->defaultSort;
        }

        $query->orderBy($sort, $order);

        return $query;
    }

    /**
     * JSON payload consumed by the React <DataTable/> component.
     * Call this from your controller's index() and pass the result into Inertia::render().
     */
    protected function getTableData(Request $request): array
    {
        $query = $this->applyQueryParams($this->baseQuery(), $request);

        $perPage = (int) $request->input('per_page', $this->defaultPerPage);
        if (! in_array($perPage, $this->perPageOptions, true)) {
            $perPage = $this->defaultPerPage;
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', (int) $request->input('page', 1))
            ->withQueryString();

        return [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }

    /**
     * GET /{resource}/export?type=xlsx|csv|pdf|print
     * Respects the same search/filters/sort as the current table state.
     */
    public function export(Request $request)
    {
        $request->validate(['type' => 'required|in:xlsx,csv,pdf,print']);

        $query = $this->applyQueryParams($this->baseQuery(), $request);
        $rows = $query->limit(50000)->get();

        $columns = $this->exportColumns ?: array_combine($this->searchableColumns, $this->searchableColumns);
        $fileName = strtolower(class_basename($this->model)).'-'.now()->format('Y-m-d-His');

        return match ($request->input('type')) {
            'xlsx' => Excel::download(new DynamicExport($rows, $columns), "{$fileName}.xlsx"),
            'csv' => Excel::download(new DynamicExport($rows, $columns), "{$fileName}.csv", \Maatwebsite\Excel\Excel::CSV),
            'pdf' => Pdf::loadView('exports.table', [
                'rows' => $rows, 'columns' => $columns, 'title' => class_basename($this->model),
            ])->download("{$fileName}.pdf"),
            'print' => response()->view('exports.table', [
                'rows' => $rows, 'columns' => $columns, 'title' => class_basename($this->model), 'autoPrint' => true,
            ]),
        };
    }

    /**
     * POST /{resource}/import
     * Expects a multipart file under "file". Uses DynamicImport with the
     * mapping/validation supplied by importRules()/importMap() overrides.
     */
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv,txt']);

        Excel::import(
            new DynamicImport($this->model, $this->importMap(), $this->importRules()),
            $request->file('file')
        );

        return back()->with('success', class_basename($this->model).' imported successfully.');
    }

    /**
     * Maps spreadsheet column headers (lowercased, snake_cased by Maatwebsite)
     * to model attributes. Override in the child controller.
     * e.g. ['full_name' => 'name', 'email_address' => 'email']
     */
    protected function importMap(): array
    {
        return [];
    }

    /** Validation rules applied to every imported row. Override as needed. */
    protected function importRules(): array
    {
        return [];
    }

    /** DELETE /{resource}/{id} */
    public function destroy($id)
    {
        $model = $this->model::findOrFail($id);
        $model->delete();

        return back()->with('success', class_basename($this->model).' deleted successfully.');
    }

    /** POST /{resource}/bulk-destroy  body: { ids: number[] } */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        DB::transaction(function () use ($validated) {
            $this->model::whereIn('id', $validated['ids'])->delete();
        });

        return back()->with('success', count($validated['ids']).' record(s) deleted successfully.');
    }
}
