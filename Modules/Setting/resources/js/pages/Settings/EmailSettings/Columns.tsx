import { DataTableColumnHeader } from '@/components/data-table/data-table-column-header';
import { DateCell } from '@/components/data-table/date-cell';
import { StatusCell } from '@/components/data-table/status-cell';
import type { ColumnDef } from '@tanstack/react-table';

export interface EmailSetting {
    id: number;
    name: string;
    mailer: string;
    host: string;
    port: number;
    encryption: string | null;
    username: string | null;
    from_address: string;
    from_name: string;
    is_active: boolean | number | string;
    created_at: string;
}

export const Columns: ColumnDef<EmailSetting>[] = [
    {
        accessorKey: 'name',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Name" />
        ),
    },
    {
        accessorKey: 'mailer',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Mailer" />
        ),
    },
    {
        accessorKey: 'host',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Host" />
        ),
    },
    {
        accessorKey: 'port',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Port" />
        ),
    },
    {
        accessorKey: 'from_address',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="From address" />
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
