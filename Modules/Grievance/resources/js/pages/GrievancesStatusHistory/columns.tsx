// resources/js/Pages/GrievanceStatusHistories/columns.tsx
import { DataTableColumnHeader } from '@/components/data-table/data-table-column-header';
import { DateCell } from '@/components/data-table/date-cell';
import { statusLabel } from '@/types/grievance-status';
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
export interface GrievanceStatusHistory {
    id: number;
    grievance_id: number;
    from_status: string | null;
    to_status: string;
    changed_by: number | null;
    note: string | null;
    grievance?: Grievance | null;
    changedBy?: User | null;
    created_at: string;
}

const truncate = (text: string, length = 60) =>
    text.length > length ? `${text.slice(0, length)}…` : text;

export const columns: ColumnDef<GrievanceStatusHistory>[] = [
    {
        id: 'grievance',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Grievance" />
        ),
        cell: ({ row }) => row.original.grievance?.reference_number ?? '—',
        enableSorting: false,
    },
    {
        id: 'transition',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Transition" />
        ),
        cell: ({ row }) => (
            <div className="flex items-center gap-1.5 text-sm">
                {row.original.from_status ? (
                    <>
                        <Badge variant="outline">{statusLabel(row.original.from_status)}</Badge>
                        <span className="text-muted-foreground">→</span>
                    </>
                ) : (
                    <span className="text-xs text-muted-foreground">{'(initial)'}</span>
                )}
                <Badge>{statusLabel(row.original.to_status)}</Badge>
            </div>
        ),
        enableSorting: false,
    },
    {
        id: 'changed_by',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Changed By" />
        ),
        cell: ({ row }) => row.original.changedBy?.name ?? 'System',
        enableSorting: false,
    },
    {
        accessorKey: 'note',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Note" />
        ),
        cell: ({ row }) =>
            row.original.note ? (
                <span className="text-sm">{truncate(row.original.note)}</span>
            ) : (
                <span className="text-sm text-muted-foreground">—</span>
            ),
        enableSorting: false,
    },
    {
        accessorKey: 'created_at',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Recorded At" />
        ),
        cell: ({ row }) => <DateCell value={row.original.created_at} />,
    },
];
