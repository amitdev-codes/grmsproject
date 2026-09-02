import { router } from '@inertiajs/react';
import type { Table } from '@tanstack/react-table';
import { Plus, X } from 'lucide-react';
import { DataTableExportImport } from '@/components/data-table/data-table-export-import';
import { DataTableViewOptions } from '@/components/data-table/data-table-view-options';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { DataTableRoutes, DataTableQueryState } from '@/types/data-table';

interface DataTableToolbarProps<TData> {
    table: Table<TData>;
    search: string;
    onSearchChange: (value: string) => void;
    onReset: () => void;
    hasActiveFilters: boolean;
    routes: DataTableRoutes;
    currentQuery: Partial<DataTableQueryState>;
    searchPlaceholder?: string;
    resourceLabel?: string;
    onCreate?: () => void;
}

/**
 * Global search + reset + view-options (column visibility) + export/import + create.
 * Per-column filters (faceted/text) now live inside the table itself —
 * see <DataTableFilterRow/>, rendered right below the header row.
 */
export function DataTableToolbar<TData>({
    table,
    search,
    onSearchChange,
    onReset,
    hasActiveFilters,
    routes,
    currentQuery,
    searchPlaceholder = 'Search…',
    resourceLabel = 'New',
    onCreate,
}: DataTableToolbarProps<TData>) {
    return (
        <div className="flex flex-wrap items-center justify-between gap-2">
            <div className="flex flex-1 flex-wrap items-center gap-2">
                <Input
                    placeholder={searchPlaceholder}
                    defaultValue={search}
                    onChange={(e) => onSearchChange(e.target.value)}
                    className="h-8 w-45 lg:w-65"
                />

                {hasActiveFilters && (
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={onReset}
                        className="h-8 px-2 lg:px-3"
                    >
                        Reset
                        <X className="ml-2 h-4 w-4" />
                    </Button>
                )}
            </div>

            <div className="flex items-center gap-2">
                <DataTableExportImport
                    routes={routes}
                    currentQuery={currentQuery}
                />
                <DataTableViewOptions table={table} />
                {(routes.create || onCreate) && (
                    <Button
                        size="sm"
                        className="h-8"
                        onClick={() => {
                            if (onCreate) {
                                onCreate();
                            } else {
                                router.visit(route(routes.create!));
                            }
                        }}
                    >
                        <Plus className="mr-2 h-4 w-4" />
                        {resourceLabel}
                    </Button>
                )}
            </div>
        </div>
    );
}
