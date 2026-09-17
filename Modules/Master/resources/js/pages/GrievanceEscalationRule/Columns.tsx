import { DataTableColumnHeader } from '@/components/data-table/data-table-column-header';
import { DateCell } from '@/components/data-table/date-cell';
import { StatusCell } from '@/components/data-table/status-cell';
import { Badge } from '@/components/ui/badge';
import type { ColumnDef } from '@tanstack/react-table';

export interface GrievanceEscalationRule {
    id: number;
    grievance_sla_policy_id: number | null;
    escalation_level: number;
    breach_after_hours: number;
    extension_hours: number | null;
    target_role: string;
    requires_manual_review: boolean | number | string;
    is_active: boolean | number | string;
    sla_policy?: { id: number; name: string } | null;
    created_at: string;
    updated_at: string;
}

export const Columns: ColumnDef<GrievanceEscalationRule>[] = [
    {
        accessorKey: 'escalation_level',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Level" />
        ),
        cell: ({ row }) => (
            <Badge variant="outline">{row.original.escalation_level}</Badge>
        ),
    },
    {
        accessorKey: 'sla_policy',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="SLA Policy" />
        ),
        cell: ({ row }) => row.original.sla_policy?.name ?? '—',
    },
    {
        accessorKey: 'breach_after_hours',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Breach (hrs)" />
        ),
    },
    {
        accessorKey: 'extension_hours',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Extension (hrs)" />
        ),
        cell: ({ row }) => row.original.extension_hours ?? '—',
    },
    {
        accessorKey: 'target_role',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Target Role" />
        ),
    },
    {
        accessorKey: 'requires_manual_review',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Manual Review" />
        ),
        cell: ({ row }) => (
            <StatusCell
                value={row.original.requires_manual_review}
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