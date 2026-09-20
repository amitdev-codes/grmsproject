import { Head, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { Search, Clock } from 'lucide-react';

import IndexLayout from '@/components/index-layout';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { DataTablePagination } from '@/components/data-table/data-table-pagination';
import { useTranslation } from '@/hooks/use-translation';
import type { PaginationMeta } from '@/types/data-table';
import { JsonCell, formatDateTime } from '../LogsCells';

interface ActivityRow {
    id: number;
    log_name: string | null;
    description: string;
    event: string | null;
    causer: string;
    attribute_changes: unknown | null;
    properties: unknown | null;
    created_at: string | null;
}

interface Props {
    data: ActivityRow[];
    meta: PaginationMeta;
}

export default function ActivityLogs({ data, meta }: Props) {
    const { t } = useTranslation();
    const [search, setSearch] = useState('');
    const searchDebounce = useRef<ReturnType<typeof setTimeout> | undefined>(undefined);

    const currentMeta = meta ?? {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
        from: null,
        to: null,
    };

    const reload = (overrides: { page?: number; per_page?: number }) => {
        const page = overrides.page ?? 1;
        const perPage = overrides.per_page ?? currentMeta.per_page;

        const params: Record<string, string | number> = { page, per_page: perPage };

        if (search) {
            params.search = search;
        }

        router.get(route('activity-logs.index'), params, {
            only: ['data', 'meta'],
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const onSearchChange = (value: string) => {
        setSearch(value);
        clearTimeout(searchDebounce.current);
        searchDebounce.current = setTimeout(() => {
            reload({ page: 1 });
        }, 400);
    };

    const onPageChange = (page: number) => {
        reload({ page });
    };

    const onPerPageChange = (perPage: number) => {
        reload({ page: 1, per_page: perPage });
    };

    useEffect(() => () => clearTimeout(searchDebounce.current), []);

    return (
        <>
            <Head title={t('menu.activity_logs')} />
            <IndexLayout
                title={t('menu.logs')}
                breadcrumbs={[
                    { label: t('menu.logs'), icon: Clock },
                    { label: t('menu.activity_logs') },
                ]}
            >
                <div className="flex flex-col gap-3">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <h1 className="text-xl font-semibold">
                            {t('menu.activity_logs')}
                        </h1>
                        <div className="relative w-full max-w-sm">
                            <Search className="pointer-events-none absolute top-1/2 left-2.5 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                value={search}
                                onChange={(e) => onSearchChange(e.target.value)}
                                placeholder={t('menu.search_placeholder')}
                                className="h-7 pl-8 text-xs"
                            />
                        </div>
                    </div>

                    <div className="border bg-card rounded-md">
                        <Table>
                            <TableHeader>
                                <TableRow className="hover:bg-muted/40">
                                    <TableHead className="text-[11px] uppercase">{t('menu.log_name')}</TableHead>
                                    <TableHead className="text-[11px] uppercase">{t('menu.log_description')}</TableHead>
                                    <TableHead className="text-[11px] uppercase">{t('menu.log_event')}</TableHead>
                                    <TableHead className="text-[11px] uppercase">{t('menu.log_causer')}</TableHead>
                                    <TableHead className="text-[11px] uppercase">{t('menu.log_old_values')}</TableHead>
                                    <TableHead className="text-[11px] uppercase">{t('menu.created_at')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {data.length ? (
                                    data.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell>
                                                <Badge variant="outline" className="font-mono">
                                                    {row.log_name || '—'}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="max-w-md">{row.description}</TableCell>
                                            <TableCell>
                                                {row.event ? (
                                                    <Badge variant="secondary">{row.event}</Badge>
                                                ) : (
                                                    '—'
                                                )}
                                            </TableCell>
                                            <TableCell className="font-mono text-xs">{row.causer}</TableCell>
                                            <TableCell>
                                                <JsonCell value={row.attribute_changes} />
                                            </TableCell>
                                            <TableCell className="whitespace-nowrap text-xs">
                                                {formatDateTime(row.created_at)}
                                            </TableCell>
                                        </TableRow>
                                    ))
                                ) : (
                                    <TableRow>
                                        <TableCell colSpan={6} className="h-20 text-center text-muted-foreground">
                                            {t('menu.no_results')}
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                        <DataTablePagination
                            meta={currentMeta}
                            selectedCount={0}
                            onPageChange={onPageChange}
                            onPerPageChange={onPerPageChange}
                        />
                    </div>
                </div>
            </IndexLayout>
        </>
    );
}
