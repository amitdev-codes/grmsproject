import { Table } from '@tanstack/react-table';
import { TableHead, TableRow } from '@/components/ui/table';
import { DataTableFacetedFilter } from '@/components/data-table/data-table-faceted-filter';
import { DataTableTextFilter } from '@/components/data-table/data-table-text-filter';
import type { DataTableFilterField } from '@/types/data-table';

interface DataTableFilterRowProps<TData> {
  table: Table<TData>;
  filterFields: DataTableFilterField[];
  activeFilters: Record<string, string[]>;
  onFilterChange: (columnId: string, values: string[]) => void;
}

/**
 * Renders one extra row inside <TableHeader>, right below the column titles,
 * with a filter control under each column that has a matching entry in
 * `filterFields` (matched by column id). Columns without a filter (avatar,
 * select, actions, etc.) just get an empty cell so alignment stays intact.
 */
export function DataTableFilterRow<TData>({
  table,
  filterFields,
  activeFilters,
  onFilterChange,
}: DataTableFilterRowProps<TData>) {
  if (filterFields.length === 0) return null;

  const fieldMap = new Map(filterFields.map((f) => [f.id, f]));
  const headerGroup = table.getHeaderGroups()[0];

  return (
    <TableRow className="hover:bg-transparent">
      {headerGroup.headers.map((header) => {
        const field = fieldMap.get(header.column.id);

        return (
          <TableHead key={`filter-${header.id}`} className="border-t bg-muted/30 py-2 align-top">
            {field ? (
              field.type === 'text' ? (
                <DataTableTextFilter
                  title={field.title}
                  value={activeFilters[field.id]?.[0] ?? ''}
                  onChange={(value) => onFilterChange(field.id, value ? [value] : [])}
                />
              ) : (
                <DataTableFacetedFilter
                  title={field.title}
                  options={field.options ?? []}
                  selected={activeFilters[field.id] ?? []}
                  onChange={(values) => onFilterChange(field.id, values)}
                />
              )
            ) : null}
          </TableHead>
        );
      })}
    </TableRow>
  );
}
