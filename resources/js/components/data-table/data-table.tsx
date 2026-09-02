import { router } from '@inertiajs/react';
import type { ColumnDef, SortingState } from '@tanstack/react-table';
import {
    flexRender,
    getCoreRowModel,
    useReactTable,
} from '@tanstack/react-table';
import { Download, Upload, Settings2, Plus, Search } from 'lucide-react';
import React, { useMemo, useState } from 'react';
import { DataTableBulkActionsBar } from '@/components/data-table/data-table-bulk-actions-bar';
import { DataTableFilterRow } from '@/components/data-table/data-table-filter-row';
import { DataTablePagination } from '@/components/data-table/data-table-pagination';
import { DataTableRowActions } from '@/components/data-table/data-table-row-actions';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useDataTable } from '@/hooks/use-data-table';
import type {
    DataTableFilterField,
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';

interface DataTableProps<TData, TValue> {
    columns: ColumnDef<TData, TValue>[];
    data: TData[];
    meta: PaginationMeta;
    routes: DataTableRoutes;
    /** Per-column filters, rendered INSIDE the table, right below the header row */
    filterFields?: DataTableFilterField[];
    title?: string;
    description?: string;
    searchPlaceholder?: string;
    resourceLabel?: string;
    perPageOptions?: number[];
    defaultSort?: string;
    defaultOrder?: 'asc' | 'desc';
    /** Enable checkbox column + bulk delete bar. Defaults to true when routes.bulkDestroy is set. */
    enableRowSelection?: boolean;
    /** Enable the auto view/edit/delete actions column. Defaults to true when any of view/edit/destroy routes are set, or viewContent is passed. */
    enableRowActions?: boolean;
    getRowId?: (row: TData) => string;
    getRowLabel?: (row: TData) => string | undefined;
    /** If provided, "View" (row action) opens a Dialog rendering this instead of navigating to routes.view */
    viewContent?: (row: TData, close: () => void) => React.ReactNode;
    onCreate?: () => void;
    onEdit?: (row: TData) => void;
    /** Header actions — omit a handler to hide that button */
    onExport?: () => void;
    onImport?: () => void;
    exportLabel?: string;
    importLabel?: string;
}

export function DataTable<TData extends { id: number | string }, TValue>({
    columns,
    data,
    meta,
    routes,
    filterFields = [],
    title = 'Records',
    description,
    searchPlaceholder = 'Search...',
    resourceLabel = 'New',
    perPageOptions = [10, 25, 50, 100],
    defaultSort = '',
    defaultOrder = 'desc',
    enableRowSelection,
    enableRowActions,
    getRowId = (row) => String(row.id),
    getRowLabel,
    viewContent,
    onCreate,
    onEdit,
    onExport,
    onImport,
    exportLabel = 'Export',
    importLabel = 'Import',
}: DataTableProps<TData, TValue>) {
    const dt = useDataTable({ routes, meta, defaultSort, defaultOrder });
    const [viewRow, setViewRow] = useState<TData | null>(null);

    const showSelection = enableRowSelection ?? !!routes.bulkDestroy;
    const showActions =
        enableRowActions ??
        !!(routes.view || routes.edit || routes.destroy || viewContent);

    // Header action buttons: an explicit `onExport`/`onImport`/`onCreate` callback always wins.
    // Otherwise, fall back to navigating via the matching named route, if one was supplied.
    const showExport = Boolean(onExport || routes.export);
    const showImport = Boolean(onImport || routes.import);
    const showCreate = Boolean(onCreate || routes.create);

    // `route()` is assumed to be Ziggy's global helper. If it isn't registered globally in
    // this app, replace the calls below with your actual navigation/URL-building logic.
    const handleExport =
        onExport ??
        (routes.export
            ? () => {
                  if (typeof route !== 'function') {
                      console.warn(
                          '[DataTable] `route()` is not defined globally — cannot build export URL.',
                      );

                      return;
                  }

                  window.location.href = route(routes.export as string, {
                      ...dt.query,
                  });
              }
            : undefined);

    const handleImport =
        onImport ??
        (routes.import
            ? () => {
                  if (typeof route !== 'function') {
                      console.warn(
                          '[DataTable] `route()` is not defined globally — cannot navigate to import page.',
                      );

                      return;
                  }

                  router.visit(route(routes.import as string));
              }
            : undefined);

    const handleCreate =
        onCreate ??
        (routes.create
            ? () => {
                  if (typeof route !== 'function') {
                      console.warn(
                          '[DataTable] `route()` is not defined globally — cannot navigate to create page.',
                      );

                      return;
                  }

                  router.visit(route(routes.create as string));
              }
            : undefined);

    // Guarantees the "Show entries" select always has a matching option to
    // display — if the backend's default per_page isn't one of the values in
    // `perPageOptions`, the trigger renders blank/oversized (Radix shows no
    // label when the current value doesn't match any item).
    const effectivePerPageOptions = useMemo(() => {
        const opts = new Set(perPageOptions);
        opts.add(meta.per_page);

        return Array.from(opts).sort((a, b) => a - b);
    }, [perPageOptions, meta.per_page]);

    const fullColumns = useMemo<ColumnDef<TData, TValue>[]>(() => {
        const cols: ColumnDef<TData, TValue>[] = [];

        if (showSelection) {
            cols.push({
                id: 'select',
                header: ({ table }) => (
                    <Checkbox
                        checked={
                            table.getIsAllPageRowsSelected() ||
                            (table.getIsSomePageRowsSelected() &&
                                'indeterminate')
                        }
                        onCheckedChange={(value) =>
                            table.toggleAllPageRowsSelected(!!value)
                        }
                        aria-label="Select all"
                    />
                ),
                cell: ({ row }) => (
                    <Checkbox
                        checked={row.getIsSelected()}
                        onCheckedChange={(value) => row.toggleSelected(!!value)}
                        aria-label="Select row"
                    />
                ),
                enableSorting: false,
                enableHiding: false,
            } as ColumnDef<TData, TValue>);
        }

        cols.push(...columns);

        if (showActions) {
            cols.push({
                id: 'actions',
                header: () => <div className="text-right">Actions</div>,
                cell: ({ row }) => (
                    <div className="flex justify-end">
                        <DataTableRowActions
                            id={getRowId(row.original)}
                            row={row.original}
                            routes={routes}
                            label={getRowLabel?.(row.original)}
                            onView={
                                viewContent ? (r) => setViewRow(r) : undefined
                            }
                            onEdit={onEdit}
                        />
                    </div>
                ),
                enableSorting: false,
                enableHiding: false,
            } as ColumnDef<TData, TValue>);
        }

        return cols;
    }, [showSelection, columns, showActions, getRowId, routes, getRowLabel, viewContent, onEdit]);

    const sortingState: SortingState = dt.query.sort
        ? [{ id: dt.query.sort, desc: dt.query.order === 'desc' }]
        : [];

    const table = useReactTable({
        data,
        columns: fullColumns,
        getCoreRowModel: getCoreRowModel(),
        manualPagination: true,
        manualSorting: true,
        manualFiltering: true,
        pageCount: meta.last_page,
        state: {
            sorting: sortingState,
            rowSelection: dt.rowSelection,
        },
        getRowId: (row) => getRowId(row),
        onRowSelectionChange: (updater) => {
            const next =
                typeof updater === 'function'
                    ? updater(dt.rowSelection)
                    : updater;
            dt.setRowSelection(next);
        },
        onSortingChange: (updater) => {
            const next =
                typeof updater === 'function' ? updater(sortingState) : updater;

            if (next.length === 0) {
                dt.clearSort();
            } else {
                dt.setSort(next[0].id, next[0].desc);
            }
        },
        enableRowSelection: showSelection,
    });

    return (
        <Card className="gap-0 py-0">
            {/* ── Header: "List {Resource}" on the left, action cluster on the right ── */}
            <CardHeader className="flex flex-row flex-nowrap items-center justify-between gap-2 border-b px-3 py-2.5!">
                <div className="flex min-w-0 flex-col justify-center">
                    <CardTitle className="text-sm leading-tight font-semibold">
                        List {title}
                    </CardTitle>
                    {description && (
                        <CardDescription className="text-[11px] leading-tight">
                            {description}
                        </CardDescription>
                    )}
                </div>

                <div className="flex shrink-0 items-center gap-1">
                    {showExport && (
                        <Button
                            variant="outline"
                            size="sm"
                            className="h-6.5 px-2 text-xs"
                            onClick={handleExport}
                        >
                            <Download className="mr-1 h-3 w-3" />
                            {exportLabel}
                        </Button>
                    )}
                    {showImport && (
                        <Button
                            variant="outline"
                            size="sm"
                            className="h-6.5 px-2 text-xs"
                            onClick={handleImport}
                        >
                            <Upload className="mr-1 h-3 w-3" />
                            {importLabel}
                        </Button>
                    )}
                    {/* View — column visibility toggle */}
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button
                                variant="outline"
                                size="sm"
                                className="h-6.5 px-2 text-xs"
                            >
                                <Settings2 className="mr-1 h-3 w-3" />
                                View
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" className="w-44">
                            <DropdownMenuLabel className="text-xs">
                                Toggle columns
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            {table
                                .getAllColumns()
                                .filter((column) => column.getCanHide())
                                .map((column) => (
                                    <DropdownMenuCheckboxItem
                                        key={column.id}
                                        className="text-xs capitalize"
                                        checked={column.getIsVisible()}
                                        onCheckedChange={(value) =>
                                            column.toggleVisibility(value)
                                        }
                                    >
                                        {column.id}
                                    </DropdownMenuCheckboxItem>
                                ))}
                        </DropdownMenuContent>
                    </DropdownMenu>
                    {showCreate && (
                        <Button
                            size="sm"
                            className="h-6.5 px-2 text-xs"
                            onClick={handleCreate}
                        >
                            <Plus className="mr-1 h-3 w-3" />
                            {resourceLabel}
                        </Button>
                    )}
                </div>
            </CardHeader>

            <CardContent className="flex flex-col gap-2 p-3">
                {/* ── Body top row: "Show N entries" fixed on the left. Bulk actions
                    (when rows are selected) and search are grouped on the right and
                    wrap onto their own line on narrow widths — never overlapping. ── */}
                <div className="flex flex-wrap items-center justify-between gap-2 border-b pb-2">
                    <div className="flex shrink-0 items-center gap-1.5 text-xs text-muted-foreground">
                        <span>Show</span>
                        <Select
                            value={String(meta.per_page)}
                            onValueChange={(value) =>
                                dt.setPerPage(Number(value))
                            }
                        >
                            <SelectTrigger className="h-6.5 w-12 px-1.5 text-[11px] [&>svg]:size-3">
                                <SelectValue placeholder={meta.per_page} />
                            </SelectTrigger>
                            <SelectContent side="top">
                                {effectivePerPageOptions.map((pageSize) => (
                                    <SelectItem
                                        key={pageSize}
                                        value={String(pageSize)}
                                        className="text-xs"
                                    >
                                        {pageSize}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <span>entries</span>
                    </div>

                    <div className="flex flex-1 flex-wrap items-center justify-end gap-2">
                        {showSelection && dt.selectedIds.length > 0 && (
                            <DataTableBulkActionsBar
                                selectedIds={dt.selectedIds}
                                routes={routes}
                                onCleared={dt.clearSelection}
                            />
                        )}

                        <div className="relative w-full shrink-0 sm:w-56">
                            <Search className="pointer-events-none absolute top-1/2 left-2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                value={dt.query.search ?? ''}
                                onChange={(e) => dt.setSearch(e.target.value)}
                                placeholder={searchPlaceholder}
                                className="h-6.5 pl-7 text-xs"
                            />
                        </div>
                    </div>
                </div>

                {/* ── Table: header row + per-column filters + body ── */}
                <div
                    className={`rounded-md border ${dt.isLoading ? 'opacity-60 transition-opacity' : ''}`}
                >
                    <Table>
                        <TableHeader>
                            {table.getHeaderGroups().map((headerGroup) => (
                                <TableRow
                                    key={headerGroup.id}
                                    className="bg-muted/40 hover:bg-muted/40"
                                >
                                    {headerGroup.headers.map((header) => (
                                        <TableHead
                                            key={header.id}
                                            className="h-8 py-1.5 text-[11px] font-semibold tracking-wide text-muted-foreground uppercase"
                                        >
                                            {header.isPlaceholder
                                                ? null
                                                : flexRender(
                                                      header.column.columnDef
                                                          .header,
                                                      header.getContext(),
                                                  )}
                                        </TableHead>
                                    ))}
                                </TableRow>
                            ))}

                            {/* Per-column filters live here, directly under the header row */}
                            <DataTableFilterRow
                                table={table}
                                filterFields={filterFields}
                                activeFilters={dt.query.filters}
                                onFilterChange={dt.setFilter}
                            />
                        </TableHeader>
                        <TableBody>
                            {table.getRowModel().rows.length ? (
                                table.getRowModel().rows.map((row) => (
                                    <TableRow
                                        key={row.id}
                                        data-state={
                                            row.getIsSelected() && 'selected'
                                        }
                                    >
                                        {row.getVisibleCells().map((cell) => (
                                            <TableCell
                                                key={cell.id}
                                                className="py-1.5 text-sm"
                                            >
                                                {flexRender(
                                                    cell.column.columnDef.cell,
                                                    cell.getContext(),
                                                )}
                                            </TableCell>
                                        ))}
                                    </TableRow>
                                ))
                            ) : (
                                <TableRow>
                                    <TableCell
                                        colSpan={fullColumns.length}
                                        className="h-20 text-center text-sm text-muted-foreground"
                                    >
                                        No results.
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                </div>

                <DataTablePagination
                    meta={meta}
                    selectedCount={dt.selectedIds.length}
                    perPageOptions={perPageOptions}
                    onPageChange={dt.setPage}
                    onPerPageChange={dt.setPerPage}
                />
            </CardContent>

            {viewContent && (
                <Dialog
                    open={!!viewRow}
                    onOpenChange={(open) => !open && setViewRow(null)}
                >
                    <DialogContent className="sm:max-w-lg">
                        {viewRow &&
                            viewContent(viewRow, () => setViewRow(null))}
                    </DialogContent>
                </Dialog>
            )}
        </Card>
    );
}
