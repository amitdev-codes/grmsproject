import { DataTableColumnHeader } from '@/components/data-table/data-table-column-header';
import { DateCell } from '@/components/data-table/date-cell';
import { StatusCell } from '@/components/data-table/status-cell';
import { Badge } from '@/components/ui/badge';
import type { ColumnDef } from '@tanstack/react-table';

export interface GrievanceChannel {
    id: number;
    code: string;
    name: string;
    is_active: boolean | number | string;
    created_at: string;
    updated_at: string;
    deleted_at?: string | null;
}

const codeLabel = (code: string) => {
    const labels: Record<string, string> = {
        web: 'Web',
        mobile_app: 'Mobile App',
        sms: 'SMS',
        ussd: 'USSD',
        whatsapp: 'WhatsApp',
        helpdesk: 'Helpdesk',
        box: 'Suggestion Box',
        grc: 'GRC',
        chief: "Chief's Office",
        social_media: 'Social Media',
    };

    return labels[code] ?? code;
};

export const columns: ColumnDef<GrievanceChannel>[] = [
    {
        accessorKey: 'code',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Code" />
        ),
        cell: ({ row }) => (
            <Badge variant="outline" className="font-mono text-xs">
                {row.original.code}
            </Badge>
        ),
    },
    {
        accessorKey: 'name',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Name" />
        ),
        cell: ({ row }) => (
            <div className="flex flex-col">
                <span className="font-medium">{row.original.name}</span>
                <span className="text-xs text-muted-foreground">
                    {codeLabel(row.original.code)}
                </span>
            </div>
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
