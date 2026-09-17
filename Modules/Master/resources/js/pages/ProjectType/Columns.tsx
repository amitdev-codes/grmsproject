import { DataTableColumnHeader } from '@/components/data-table/data-table-column-header';
import { DateCell } from '@/components/data-table/date-cell';
import { StatusCell } from '@/components/data-table/status-cell';
import { Badge } from '@/components/ui/badge';
import type { ColumnDef } from '@tanstack/react-table';

export interface ProjectType {
    id: number;
    code: string;
    name: string;
    name_st: string | null;
    sort_order: number;
    is_active: boolean | number | string;
    created_at: string;
    updated_at: string;
}

export const Columns: ColumnDef<ProjectType>[] = [
    {
        accessorKey: 'code',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Code" />
        ),
        cell: ({ row }) => (
            <span className="font-mono text-xs">{row.original.code}</span>
        ),
    },
    {
        accessorKey: 'name',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Name" />
        ),
    },
    {
        accessorKey: 'name_st',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Name ST" />
        ),
        cell: ({ row }) => row.original.name_st ?? '—',
    },
    {
        accessorKey: 'sort_order',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Order" />
        ),
        cell: ({ row }) => (
            <Badge variant="outline">{row.original.sort_order}</Badge>
        ),
    },
    {
        accessorKey: 'is_active',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Status" />
        ),
        cell: ({ row }) => (
            <StatusCell
                value={row.original.is_active}
                activeLabel="Active"
                inactiveLabel="Inactive"
            />
        ),
    },
    {
        accessorKey: 'created_at',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Created" />
        ),
        cell: ({ row }) => <DateCell value={row.original.created_at} />,
    },
];