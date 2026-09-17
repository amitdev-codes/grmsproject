import { DataTableColumnHeader } from '@/components/data-table/data-table-column-header';
import { DateCell } from '@/components/data-table/date-cell';
import { StatusCell } from '@/components/data-table/status-cell';
import { Badge } from '@/components/ui/badge';
import type { ColumnDef } from '@tanstack/react-table';

export interface ServiceProvider {
    id: number;
    code: string;
    name: string;
    provider_type: string;
    contact_name: string | null;
    phone: string | null;
    email: string | null;
    is_active: boolean | number | string;
    created_at: string;
    updated_at: string;
}

export const Columns: ColumnDef<ServiceProvider>[] = [
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
        accessorKey: 'provider_type',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Type" />
        ),
        cell: ({ row }) => (
            <Badge variant="secondary">{row.original.provider_type}</Badge>
        ),
    },
    {
        accessorKey: 'contact_name',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Contact" />
        ),
    },
    {
        accessorKey: 'phone',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Phone" />
        ),
    },
    {
        accessorKey: 'email',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Email" />
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