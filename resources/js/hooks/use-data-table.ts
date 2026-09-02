import { router } from '@inertiajs/react';
import { useCallback, useRef, useState } from 'react';
import type { DataTableRoutes, PaginationMeta } from '@/types/data-table';

interface QueryState {
  search: string;
  sort: string;
  order: 'asc' | 'desc';
  page: number;
  per_page: number;
  filters: Record<string, string[]>;
}

interface UseDataTableArgs {
  routes: DataTableRoutes;
  meta: PaginationMeta;
  defaultSort?: string;
  defaultOrder?: 'asc' | 'desc';
}

/**
 * Drives every server-side interaction for the DataTable: global search,
 * per-column faceted filters, sorting, and pagination. Everything is sent
 * as an Inertia partial reload (only "data" + "meta" props come back),
 * so the rest of the page never re-renders/re-fetches.
 */
export function useDataTable({ routes, meta, defaultSort = '', defaultOrder = 'desc' }: UseDataTableArgs) {
  const [query, setQuery] = useState<QueryState>({
    search: '',
    sort: defaultSort,
    order: defaultOrder,
    page: meta.current_page,
    per_page: meta.per_page,
    filters: {},
  });
  const [isLoading, setIsLoading] = useState(false);
  const [rowSelection, setRowSelection] = useState<Record<string, boolean>>({});
  const debounceRef = useRef<ReturnType<typeof setTimeout> | undefined>(undefined);

  const go = useCallback(
    (next: Partial<QueryState>, options?: { debounce?: boolean }) => {
      const merged = { ...query, ...next };
      setQuery(merged);

      const execute = () => {
        setIsLoading(true);
        router.get(
          routes.index ? route(routes.index) : window.location.pathname,
          {
            search: merged.search || undefined,
            sort: merged.sort || undefined,
            order: merged.order,
            page: merged.page,
            per_page: merged.per_page,
            filters: merged.filters,
          },
          {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['data', 'meta'],
            onFinish: () => setIsLoading(false),
          }
        );
      };

      if (options?.debounce) {
        if (debounceRef.current) {
            clearTimeout(debounceRef.current);
        }

        debounceRef.current = setTimeout(execute, 400);
      } else {
        execute();
      }
    },
    [query, routes.index]
  );

  const selectedIds = Object.keys(rowSelection).filter((id) => rowSelection[id]);

  return {
    query,
    isLoading,
    rowSelection,
    setRowSelection,
    selectedIds,
    clearSelection: () => setRowSelection({}),

    setSearch: (value: string) => go({ search: value, page: 1 }, { debounce: true }),

    setSort: (columnId: string, desc: boolean) => go({ sort: columnId, order: desc ? 'desc' : 'asc', page: 1 }),

    clearSort: () => go({ sort: '', page: 1 }),

    setPage: (page: number) => go({ page }),

    setPerPage: (perPage: number) => go({ per_page: perPage, page: 1 }),

    setFilter: (columnId: string, values: string[]) =>
      go({ filters: { ...query.filters, [columnId]: values }, page: 1 }),

    resetFilters: () => go({ filters: {}, search: '', page: 1 }),

    hasActiveFilters: () => query.search !== '' || Object.values(query.filters).some((v) => v.length > 0),
  };
}
