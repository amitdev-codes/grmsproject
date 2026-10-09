import { DataTableColumnHeader } from '@/components/data-table/data-table-column-header';
import { DateCell } from '@/components/data-table/date-cell';
import { Badge } from '@/components/ui/badge';
import { GRIEVANCE_STATUS_OPTIONS } from '@/types/grievance-status';
import { useTranslation } from '@/hooks/use-translation';
import type { ColumnDef } from '@tanstack/react-table';

export interface Category {
    id: number;
    code: string;
    name_en: string;
}

export interface Channel {
    id: number;
    code: string;
    name: string;
}

export interface District {
    id: number;
    code?: string;
    name: string;
}

export interface Division {
    id: number;
    code?: string;
    name: string;
}

export interface Section {
    id: number;
    code?: string;
    name: string;
}

export interface Attachment {
    id: number;
    name: string;
    file_name: string;
    mime_type: string;
    size: number;
    url: string;
    thumb_url: string;
}

export interface StatusHistory {
    id: number;
    from_status: string | null;
    to_status: string;
    to_status_label?: string;
    actor_role: string | null;
    reason: string | null;
    created_at: string;
}

export interface Grievance {
    id: number;
    reference_no: string;
    description: string;
    remarks?: string | null;
    status: string;
    // Not yet present on GrievanceResource — add it server-side to populate this column.
    priority?: 'low' | 'normal' | 'high';
    is_anonymous: boolean | number | string;
    complainant_name: string | null;
    complainant_phone: string | null;
    complainant_email: string | null;
    category?: Category | null;
    channel?: Channel | null;
    district?: District | null;
    // Not yet present on GrievanceResource — add once the movement/routing module lands.
    division?: Division | null;
    section?: Section | null;
    assigned_officer_id?: number | null;
    assigned_officer?: { id: number; name: string } | null;
    sla_due_at?: string | null;
    attachments: Attachment[];
    status_histories?: StatusHistory[];
    created_at: string;
}

const statusLabel = (status: string) =>
    GRIEVANCE_STATUS_OPTIONS.find((o) => o.value === status)?.label ??
    status.replace(/_/g, ' ');

const priorityVariant = (
    priority?: string,
): 'default' | 'secondary' | 'outline' | 'destructive' => {
    switch (priority) {
        case 'high':
            return 'destructive';
        case 'low':
            return 'outline';
        default:
            return 'secondary';
    }
};

export function useGrievanceColumns(): ColumnDef<Grievance>[] {
    const { t } = useTranslation();

    return [
        {
            accessorKey: 'reference_no',
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title={t('Reference')} />
            ),
            cell: ({ row }) => (
                <span className="font-mono text-xs font-medium">
                    {row.original.reference_no}
                </span>
            ),
        },
        {
            id: 'complainant',
            header: ({ column }) => (
                <DataTableColumnHeader
                    column={column}
                    title={t('Complainant')}
                />
            ),
            cell: ({ row }) =>
                row.original.is_anonymous ? (
                    <Badge variant="outline">{t('Anonymous')}</Badge>
                ) : (
                    <div className="flex flex-col">
                        <span className="font-medium">
                            {row.original.complainant_name ?? '—'}
                        </span>
                        <span className="text-xs text-muted-foreground">
                            {row.original.complainant_phone ??
                                row.original.complainant_email ??
                                ''}
                        </span>
                    </div>
                ),
            enableSorting: false,
        },
        {
            id: 'category',
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title={t('Category')} />
            ),
            cell: ({ row }) => row.original.category?.name_en ?? '—',
            enableSorting: false,
        },
        {
            id: 'channel',
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title={t('Channel')} />
            ),
            cell: ({ row }) => row.original.channel?.name ?? '—',
            enableSorting: false,
        },
        {
            id: 'district',
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title={t('District')} />
            ),
            cell: ({ row }) => row.original.district?.name ?? '—',
            enableSorting: false,
        },
        {
            accessorKey: 'priority',
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title={t('Priority')} />
            ),
            cell: ({ row }) => (
                <Badge variant={priorityVariant(row.original.priority)}>
                    {t(row.original.priority ?? 'normal')}
                </Badge>
            ),
        },
        {
            accessorKey: 'status',
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title={t('Status')} />
            ),
            cell: ({ row }) => (
                <Badge variant="secondary" className="capitalize">
                    {statusLabel(row.original.status)}
                </Badge>
            ),
        },
        {
            accessorKey: 'sla_due_at',
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title={t('SLA Due')} />
            ),
            cell: ({ row }) =>
                row.original.sla_due_at ? (
                    <DateCell value={row.original.sla_due_at} />
                ) : (
                    <span className="text-xs text-muted-foreground">—</span>
                ),
        },
        {
            accessorKey: 'created_at',
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title={t('Created')} />
            ),
            cell: ({ row }) => <DateCell value={row.original.created_at} />,
        },
    ];
}
