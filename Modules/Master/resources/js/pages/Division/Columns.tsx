import { DataTableColumnHeader } from '@/components/data-table/data-table-column-header';
import { DateCell } from '@/components/data-table/date-cell';
import type { ColumnDef } from '@tanstack/react-table';

export interface Division {
    id: number;
    code: string;
    name: string;
    name_st: string | null;
    description: string | null;
    created_at: string;
}

export const Columns: ColumnDef<Division>[] = [
    {
        accessorKey: 'code',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Code" />
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
        accessorKey: 'created_at',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Created" />
        ),
        cell: ({ row }) => <DateCell value={row.original.created_at} />,
    },
];
