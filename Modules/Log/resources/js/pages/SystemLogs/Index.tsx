import { Head, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { Search, Clock } from 'lucide-react';

import IndexLayout from '@/components/index-layout';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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
import { LevelBadge } from '../LogsCells';

interface SystemRow {
    id: number;
    timestamp: string;
    level: string;
    message: string;
}

interface Props {
    data: SystemRow[];
    meta: PaginationMeta;
    selectedDate: string;
    availableDates: string[];
}

export default function SystemLogs({ data, meta, selectedDate, availableDates }: Props) {
    const { t } = useTranslation();
    const [search, setSearch] = useState('');
    const [date, setDate] = useState<string>(selectedDate);
    const searchDebounce = useRef<ReturnType<typeof setTimeout> | undefined>(undefined);

    const currentMeta = meta ?? {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
        from: null,
        to: null,
    };

    const reload = (overrides: { page?: number; per_page?: number; date?: string }) => {
        const page = overrides.page ?? 1;
        const perPage = overrides.per_page ?? currentMeta.per_page;
        const usedDate = overrides.date ?? date;

        const params: Record<string, string | number> = {
            page,
            per_page: perPage,
            date: usedDate,
        };

        if (search) {
            params.search = search;
        }

        router.get(route('system-logs.index'), params, {
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

    const onDateChange = (next: string) => {
        clearTimeout(searchDebounce.current);
        setDate(next);
        reload({ date: next, page: 1 });
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
            <Head title={t('menu.system_logs')} />
            <IndexLayout
                title={t('menu.logs')}
                breadcrumbs={[
                    { label: t('menu.logs'), icon: Clock },
                    { label: t('menu.system_logs') },
                ]}
            >
                <div className="flex flex-col gap-3">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <div className="flex items-center gap-2">
                            <h1 className="text-xl font-semibold">
                                {t('menu.system_logs')}
                            </h1>
                            {availableDates.length ? (
                                <Select value={date} onValueChange={onDateChange}>
                                    <SelectTrigger className="h-7 w-44 text-xs">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {availableDates.map((d) => (
                                            <SelectItem key={d} value={d} className="text-xs">
                                                {d}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            ) : (
                                <span className="text-xs text-muted-foreground">
                                    {t('menu.no_log_files')}
                                </span>
                            )}
                        </div>

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
                                    <TableHead className="text-[11px] uppercase">{t('menu.log_date')}</TableHead>
                                    <TableHead className="text-[11px] uppercase">{t('menu.log_level')}</TableHead>
                                    <TableHead className="text-[11px] uppercase">{t('menu.log_message')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {data.length ? (
                                    data.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell className="whitespace-nowrap text-xs">
                                                {row.timestamp}
                                            </TableCell>
                                            <TableCell className="whitespace-nowrap">
                                                <LevelBadge level={row.level} />
                                            </TableCell>
                                            <TableCell className="max-w-3xl">
                                                {row.message ? (
                                                    <pre className="max-h-28 overflow-auto whitespace-pre-wrap break-all text-xs">
                                                        {row.message}
                                                    </pre>
                                                ) : (
                                                    '—'
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    ))
                                ) : (
                                    <TableRow>
                                        <TableCell colSpan={3} className="h-20 text-center text-muted-foreground">
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
