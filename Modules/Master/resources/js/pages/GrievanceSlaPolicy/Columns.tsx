import { DataTableColumnHeader } from '@/components/data-table/data-table-column-header';
import { DateCell } from '@/components/data-table/date-cell';
import { StatusCell } from '@/components/data-table/status-cell';
import { Badge } from '@/components/ui/badge';
import type { ColumnDef } from '@tanstack/react-table';

export interface GrievanceSlaPolicy {
    id: number;
    code: string;
    name: string;
    priority: string | null;
    acknowledgement_hours: number;
    resolution_hours: number;
    use_business_hours: boolean | number | string;
    is_active: boolean | number | string;
    created_at: string;
    updated_at: string;
}

export const Columns: ColumnDef<GrievanceSlaPolicy>[] = [
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
        accessorKey: 'priority',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Priority" />
        ),
        cell: ({ row }) =>
            row.original.priority ? (
                <Badge variant="secondary">{row.original.priority}</Badge>
            ) : '—',
    },
    {
        accessorKey: 'acknowledgement_hours',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Ack (hrs)" />
        ),
    },
    {
        accessorKey: 'resolution_hours',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Resolve (hrs)" />
        ),
    },
    {
        accessorKey: 'use_business_hours',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Biz Hours" />
        ),
        cell: ({ row }) => (
            <StatusCell
                value={row.original.use_business_hours}
                activeLabel="Yes"
                inactiveLabel="No"
            />
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