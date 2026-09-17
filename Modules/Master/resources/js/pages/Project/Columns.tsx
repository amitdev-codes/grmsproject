import { DataTableColumnHeader } from '@/components/data-table/data-table-column-header';
import { DateCell } from '@/components/data-table/date-cell';
import { StatusCell } from '@/components/data-table/status-cell';
import { Badge } from '@/components/ui/badge';
import type { ColumnDef } from '@tanstack/react-table';

export interface Project {
    id: number;
    code: string;
    title: string;
    description: string | null;
    project_type_id: number | null;
    district_id: number | null;
    division_id: number | null;
    consultant_id: number | null;
    contractor_id: number | null;
    contract_amount: number | null;
    currency: string;
    starts_on: string | null;
    expected_completion_on: string | null;
    completed_on: string | null;
    status: string;
    metadata: Record<string, unknown> | null;
    project_type?: { id: number; name: string } | null;
    district?: { id: number; name: string } | null;
    contractor?: { id: number; name: string } | null;
    created_at: string;
    updated_at: string;
}

export const Columns: ColumnDef<Project>[] = [
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
        accessorKey: 'title',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Title" />
        ),
    },
    {
        accessorKey: 'project_type',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Type" />
        ),
        cell: ({ row }) => row.original.project_type?.name ?? '—',
    },
    {
        accessorKey: 'district',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="District" />
        ),
        cell: ({ row }) => row.original.district?.name ?? '—',
    },
    {
        accessorKey: 'contractor',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Contractor" />
        ),
        cell: ({ row }) => row.original.contractor?.name ?? '—',
    },
    {
        accessorKey: 'contract_amount',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Amount" />
        ),
        cell: ({ row }) =>
            row.original.contract_amount
                ? `${row.original.currency} ${Number(row.original.contract_amount).toLocaleString()}`
                : '—',
    },
    {
        accessorKey: 'status',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Status" />
        ),
        cell: ({ row }) => (
            <Badge variant="outline">{row.original.status}</Badge>
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