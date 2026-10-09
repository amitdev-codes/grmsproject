import { DataTableColumnHeader } from '@/components/data-table/data-table-column-header';
import { DateCell } from '@/components/data-table/date-cell';
import { StatusCell } from '@/components/data-table/status-cell';
import { Badge } from '@/components/ui/badge';
import { useTranslation } from '@/hooks/use-translation';
import type { ColumnDef } from '@tanstack/react-table';
import { icons as LucideIcons } from 'lucide-react';

export interface GrievanceCategory {
    id: number;
    code: string;
    name_en: string;
    name_st: string;
    slug: string;
    icon: string | null;
    is_sensitive: boolean | number | string;
    form_fields: Record<string, unknown> | null;
    sort_order: number;
    is_active: boolean | number | string;
    created_at: string;
    updated_at: string;
}

export function useGrievanceCategoryColumns(): ColumnDef<GrievanceCategory>[] {
    const { t } = useTranslation();

    return [
        {
            accessorKey: 'code',
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title={t('Code')} />
            ),
            cell: ({ row }) => (
                <span className="font-mono text-xs">{row.original.code}</span>
            ),
        },
        {
            accessorKey: 'name_en',
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title={t('Name')} />
            ),
            cell: ({ row }) => (
                <div className="flex flex-col">
                    <span className="font-medium">{row.original.name_en}</span>
                    <span className="text-xs text-muted-foreground">
                        {row.original.name_st}
                    </span>
                </div>
            ),
        },
        {
            accessorKey: 'slug',
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title={t('Slug')} />
            ),
            cell: ({ row }) => (
                <span className="font-mono text-xs">{row.original.slug}</span>
            ),
        },
        {
            id: 'icon',
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title={t('Icon')} />
            ),
            cell: ({ row }) => {
                const iconName = row.original.icon;
                const Icon = iconName
                    ? LucideIcons[iconName as keyof typeof LucideIcons]
                    : null;

                return (
                    <div className="flex items-center gap-2">
                        {Icon && <Icon className="h-4 w-4" />}
                        <span className="text-xs text-muted-foreground">
                            {iconName ?? '—'}
                        </span>
                    </div>
                );
            },
            enableSorting: false,
        },
        {
            accessorKey: 'is_sensitive',
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title={t('Sensitive')} />
            ),
            cell: ({ row }) => (
                <StatusCell
                    value={row.original.is_sensitive}
                    activeLabel={t('Sensitive')}
                    inactiveLabel={t('Standard')}
                />
            ),
        },
        {
            accessorKey: 'sort_order',
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title={t('Order')} />
            ),
            cell: ({ row }) => (
                <Badge variant="outline">{row.original.sort_order}</Badge>
            ),
        },
        {
            accessorKey: 'is_active',
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title={t('Status')} />
            ),
            cell: ({ row }) => (
                <StatusCell
                    value={row.original.is_active}
                    activeLabel={t('Active')}
                    inactiveLabel={t('Inactive')}
                />
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

