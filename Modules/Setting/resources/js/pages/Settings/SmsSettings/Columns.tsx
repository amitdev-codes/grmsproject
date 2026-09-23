import { DataTableColumnHeader } from '@/components/data-table/data-table-column-header';
import { DateCell } from '@/components/data-table/date-cell';
import { StatusCell } from '@/components/data-table/status-cell';
import type { ColumnDef } from '@tanstack/react-table';

export interface SmsSetting {
    id: number;
    name: string;
    provider: string;
    base_url: string | null;
    sender_id: string | null;
    default_country_code: string;
    is_active: boolean | number | string;
    created_at: string;
}

export const Columns: ColumnDef<SmsSetting>[] = [
    {
        accessorKey: 'name',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Name" />
        ),
    },
    {
        accessorKey: 'provider',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Provider" />
        ),
    },
    {
        accessorKey: 'base_url',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Base URL" />
        ),
    },
    {
        accessorKey: 'sender_id',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Sender ID" />
        ),
    },
    {
        accessorKey: 'default_country_code',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Country code" />
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
