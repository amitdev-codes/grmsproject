// resources/js/Pages/Resolutions/columns.tsx
import { DataTableColumnHeader } from '@/components/data-table/data-table-column-header';
import { DateCell } from '@/components/data-table/date-cell';
import { Badge } from '@/components/ui/badge';
import type { ColumnDef } from '@tanstack/react-table';

export interface Grievance {
    id: number;
    reference_number: string;
}
export interface User {
    id: number;
    name: string;
}
export interface Resolution {
    id: number;
    grievance_id: number;
    proposed_by: number | null;
    approved_by: number | null;
    resolution_text: string;
    approved_at: string | null;
    complainant_confirmed_at: string | null;
    rejected_reason: string | null;
    grievance?: Grievance | null;
    proposedBy?: User | null;
    approvedByUser?: User | null; // avoid colliding with the approved_by FK name
    created_at: string;
    updated_at: string;
}

type ResolutionState = 'proposed' | 'approved' | 'confirmed' | 'rejected';

const resolveState = (r: Resolution): ResolutionState => {
    if (r.rejected_reason) return 'rejected';
    if (r.complainant_confirmed_at) return 'confirmed';
    if (r.approved_at) return 'approved';
    return 'proposed';
};

const stateLabel = (state: ResolutionState) => {
    switch (state) {
        case 'proposed':
            return 'Proposed';
        case 'approved':
            return 'Approved';
        case 'confirmed':
            return 'Confirmed';
        case 'rejected':
            return 'Rejected';
    }
};

const stateVariant = (
    state: ResolutionState,
): 'default' | 'secondary' | 'outline' | 'destructive' => {
    switch (state) {
        case 'proposed':
            return 'outline';
        case 'approved':
            return 'secondary';
        case 'confirmed':
            return 'default';
        case 'rejected':
            return 'destructive';
    }
};

const truncate = (text: string, length = 80) =>
    text.length > length ? `${text.slice(0, length)}…` : text;

export const columns: ColumnDef<Resolution>[] = [
    {
        id: 'grievance',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Grievance" />
        ),
        cell: ({ row }) => row.original.grievance?.reference_number ?? '—',
        enableSorting: false,
    },
    {
        id: 'state',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="State" />
        ),
        cell: ({ row }) => {
            const state = resolveState(row.original);
            return (
                <Badge variant={stateVariant(state)}>{stateLabel(state)}</Badge>
            );
        },
        enableSorting: false,
    },
    {
        accessorKey: 'resolution_text',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Resolution" />
        ),
        cell: ({ row }) => (
            <span className="text-sm">
                {truncate(row.original.resolution_text)}
            </span>
        ),
        enableSorting: false,
    },
    {
        id: 'proposed_by',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Proposed By" />
        ),
        cell: ({ row }) => row.original.proposedBy?.name ?? '—',
        enableSorting: false,
    },
    {
        id: 'approved_by',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Approved By" />
        ),
        cell: ({ row }) => row.original.approvedByUser?.name ?? '—',
        enableSorting: false,
    },
    {
        accessorKey: 'approved_at',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Approved At" />
        ),
        cell: ({ row }) =>
            row.original.approved_at ? (
                <DateCell value={row.original.approved_at} />
            ) : (
                '—'
            ),
    },
    {
        accessorKey: 'complainant_confirmed_at',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Confirmed At" />
        ),
        cell: ({ row }) =>
            row.original.complainant_confirmed_at ? (
                <DateCell value={row.original.complainant_confirmed_at} />
            ) : (
                '—'
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
