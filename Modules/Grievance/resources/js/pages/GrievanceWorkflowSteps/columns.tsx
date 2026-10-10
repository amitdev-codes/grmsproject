import { DataTableColumnHeader } from '@/components/data-table/data-table-column-header';
import { StatusCell } from '@/components/data-table/status-cell';
import { Badge } from '@/components/ui/badge';
import type { ColumnDef } from '@tanstack/react-table';

export interface GrievanceWorkflowStep {
    id: number;
    step_number: number;
    name: string;
    description: string | null;
    role_name: string | null;
    approver_user_id: number | null;
    approver_user?: { id: number; name: string } | null;
    approval_action: 'advance' | 'resolve';
    rejection_action: 'reject' | 'return_to_step';
    rejection_target_step: number | null;
    is_final_approval: boolean;
    is_active: boolean;
}

export const columns: ColumnDef<GrievanceWorkflowStep>[] = [
    {
        accessorKey: 'step_number',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Level" />
        ),
        cell: ({ row }) => (
            <Badge variant="outline">Level {row.original.step_number}</Badge>
        ),
    },
    {
        accessorKey: 'name',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Step" />
        ),
        cell: ({ row }) => (
            <div className="flex flex-col">
                <span className="font-medium">{row.original.name}</span>
                {row.original.description && (
                    <span className="max-w-sm truncate text-xs text-muted-foreground">
                        {row.original.description}
                    </span>
                )}
            </div>
        ),
    },
    {
        id: 'approver',
        header: 'Approver',
        cell: ({ row }) =>
            row.original.approver_user?.name ?? row.original.role_name ?? '—',
        enableSorting: false,
    },
    {
        accessorKey: 'approval_action',
        header: 'On Approval',
        cell: ({ row }) =>
            row.original.approval_action === 'resolve'
                ? 'Resolve grievance'
                : 'Advance to next level',
    },
    {
        accessorKey: 'rejection_action',
        header: 'On Rejection',
        cell: ({ row }) =>
            row.original.rejection_action === 'return_to_step'
                ? `Return to Level ${row.original.rejection_target_step}`
                : 'Reject grievance',
    },
    {
        accessorKey: 'is_final_approval',
        header: 'Final Approval',
        cell: ({ row }) =>
            row.original.is_final_approval ? (
                <Badge>Final approval</Badge>
            ) : (
                '—'
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
];
