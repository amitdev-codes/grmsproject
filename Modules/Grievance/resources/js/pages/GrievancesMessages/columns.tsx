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
export interface GrievanceMessage {
    id: number;
    grievance_id: number;
    user_id: number | null;
    sender_type: 'officer' | 'complainant' | 'system';
    message: string;
    is_internal: boolean;
    grievance?: Grievance | null;
    user?: User | null;
    created_at: string;
    updated_at: string;
}

const senderTypeLabel = (type: GrievanceMessage['sender_type']) => {
    switch (type) {
        case 'officer':
            return 'Officer';
        case 'complainant':
            return 'Complainant';
        case 'system':
            return 'System';
        default:
            return type;
    }
};

const senderTypeVariant = (
    type: GrievanceMessage['sender_type'],
): 'default' | 'secondary' | 'outline' =>
    type === 'officer'
        ? 'default'
        : type === 'complainant'
          ? 'secondary'
          : 'outline';

const truncate = (text: string, length = 80) =>
    text.length > length ? `${text.slice(0, length)}…` : text;

export const columns: ColumnDef<GrievanceMessage>[] = [
    {
        id: 'grievance',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Grievance" />
        ),
        cell: ({ row }) => row.original.grievance?.reference_number ?? '—',
        enableSorting: false,
    },
    {
        accessorKey: 'sender_type',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Sender" />
        ),
        cell: ({ row }) => (
            <Badge variant={senderTypeVariant(row.original.sender_type)}>
                {senderTypeLabel(row.original.sender_type)}
            </Badge>
        ),
    },
    {
        id: 'sender_name',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="From" />
        ),
        cell: ({ row }) => row.original.user?.name ?? '—',
        enableSorting: false,
    },
    {
        accessorKey: 'message',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Message" />
        ),
        cell: ({ row }) => (
            <span className="text-sm">{truncate(row.original.message)}</span>
        ),
        enableSorting: false,
    },
    {
        accessorKey: 'is_internal',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Visibility" />
        ),
        cell: ({ row }) =>
            row.original.is_internal ? (
                <Badge
                    variant="outline"
                    className="border-amber-400 text-amber-700 dark:text-amber-400"
                >
                    Internal
                </Badge>
            ) : (
                <Badge variant="secondary">Visible to complainant</Badge>
            ),
    },
    {
        accessorKey: 'created_at',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Sent" />
        ),
        cell: ({ row }) => <DateCell value={row.original.created_at} />,
    },
];
