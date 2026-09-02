import { DataTableColumnHeader } from '@/components/data-table/data-table-column-header';
import { DateCell } from '@/components/data-table/date-cell';
import { StatusCell } from '@/components/data-table/status-cell';
import { Badge } from '@/components/ui/badge';
import type { ColumnDef } from '@tanstack/react-table';

export interface Grievance {
    id: number;
    reference_number: string;
}

export interface EscalatedToUser {
    id: number;
    name: string;
}

export interface GrievanceEscalation {
    id: number;
    grievance_id: number;
    escalation_level: number;
    escalated_to: number | null;
    sla_breached_at: string;
    escalated_at: string;
    reason: string | null;
    resolved: boolean | number | string;
    grievance?: Grievance | null;
    escalated_officer?: EscalatedToUser | null; // match whatever key your controller sends for escalatedTo()
    created_at: string;
}

const levelLabel = (level: number) => {
    switch (level) {
        case 1:
            return 'Level 1 – Zonal Officer';
        case 2:
            return 'Level 2 – Regional Head';
        case 3:
            return 'Level 3 – Director';
        default:
            return `Level ${level}`;
    }
};

const levelVariant = (level: number): 'default' | 'secondary' | 'outline' =>
    level === 1 ? 'outline' : level === 2 ? 'secondary' : 'default';

export const columns: ColumnDef<GrievanceEscalation>[] = [
    {
        id: 'grievance',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Grievance" />
        ),
        cell: ({ row }) => row.original.grievance?.reference_number ?? '—',
        enableSorting: false,
    },
    {
        accessorKey: 'escalation_level',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Level" />
        ),
        cell: ({ row }) => (
            <Badge variant={levelVariant(row.original.escalation_level)}>
                {levelLabel(row.original.escalation_level)}
            </Badge>
        ),
    },
    {
        id: 'escalated_to',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Escalated To" />
        ),
        cell: ({ row }) => row.original.escalated_officer?.name ?? '—',
        enableSorting: false,
    },
    {
        accessorKey: 'sla_breached_at',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="SLA Breached" />
        ),
        cell: ({ row }) => <DateCell value={row.original.sla_breached_at} />,
    },
    {
        accessorKey: 'escalated_at',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Escalated At" />
        ),
        cell: ({ row }) => <DateCell value={row.original.escalated_at} />,
    },
    {
        accessorKey: 'resolved',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Resolved" />
        ),
        cell: ({ row }) => (
            <StatusCell
                value={row.original.resolved}
                activeLabel="Resolved"
                inactiveLabel="Open"
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
