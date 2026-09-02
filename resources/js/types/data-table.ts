export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
}

export interface FacetedFilterOption {
  label: string;
  value: string;
  icon?: React.ComponentType<{ className?: string }>;
}

/** Config for one column's filter control in the toolbar */
export interface DataTableFilterField {
  /** Must match the accessorKey / id of the column AND a `filterableColumns` entry on the backend */
  id: string;
  title: string;
  /** 'faceted' = dropdown with fixed options (e.g. status, role). 'text' = free-text input (e.g. email, name). Defaults to 'faceted'. */
  type?: 'faceted' | 'text';
  /** Required when type is 'faceted' (or omitted, since that's the default) */
  options?: FacetedFilterOption[];
}

export interface DataTableRoutes {
  /** Named Laravel route for the index page itself (used to refetch with query params) */
  index?: string;
  /** Named Laravel route for "create new" button, e.g. 'users.create' */
  create?: string;
  /** Named Laravel route for viewing a row, e.g. 'users.show' (param: id) */
  view?: string;
  /** Named Laravel route for editing a row, e.g. 'users.edit' (param: id) */
  edit?: string;
  /** Named Laravel route for deleting a row, e.g. 'users.destroy' (param: id) */
  destroy?: string;
  /** Named Laravel route for bulk delete, e.g. 'users.bulk-destroy' */
  bulkDestroy?: string;
  /** Named Laravel route for export, e.g. 'users.export' (?type=xlsx|csv|pdf|print) */
  export?: string;
  /** Named Laravel route for import, e.g. 'users.import' */
  import?: string;
}

export interface DataTableQueryState {
  search: string;
  sort: string;
  order: 'asc' | 'desc';
  per_page: number;
  page: number;
  filters: Record<string, string[]>;
}
