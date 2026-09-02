import { DataTableColumnHeader } from '@/components/data-table/data-table-column-header';
import type { ColumnDef } from '@tanstack/react-table';

export interface Permission {
    id: number;
    name: string;
    created_at: string;
}

export const columns: ColumnDef<Permission>[] = [
    {
        accessorKey: 'name',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Name" />
        ),
    },
    {
        accessorKey: 'created_at',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Created" />
        ),
        cell: ({ row }) =>
            new Date(row.original.created_at).toLocaleDateString(),
    },
];
