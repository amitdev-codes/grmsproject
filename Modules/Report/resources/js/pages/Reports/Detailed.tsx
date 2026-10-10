import { Link } from '@inertiajs/react';
import { ReportFilters } from './ReportFilters';
import type { ReportFilterOptions, ReportFilterValues } from './ReportFilters';
import { ReportLayout } from './ReportLayout';

interface FinalizedGrievance {
    id: number;
    reference_no: string;
    status: string;
    created_at: string;
    closed_at: string | null;
    category: string | null;
    division: string | null;
    approved_at: string | null;
    approver: string | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface DetailedReportProps {
    filters: ReportFilterValues;
    options: ReportFilterOptions;
    grievances: {
        data: FinalizedGrievance[];
        current_page: number;
        last_page: number;
        total: number;
        links: PaginationLink[];
    };
}

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }).format(new Date(value));
}

export default function Detailed({
    grievances,
    filters,
    options,
}: DetailedReportProps) {
    return (
        <ReportLayout
            title="Detailed"
            description="Grievances that have reached a resolved or closed status, or have an approved resolution."
        >
            <ReportFilters options={options} filters={filters} />
            <section className="overflow-hidden rounded-lg border bg-card">
                <div className="flex flex-wrap items-center justify-between gap-2 border-b px-4 py-3">
                    <h2 className="font-semibold">Finalized grievances</h2>
                    <span className="text-sm text-muted-foreground">
                        {grievances.total.toLocaleString()} records
                    </span>
                </div>
                <div className="overflow-x-auto">
                    <table className="w-full min-w-[900px] text-left text-sm">
                        <thead className="bg-muted/60 text-muted-foreground">
                            <tr>
                                {[
                                    'Reference',
                                    'Category type',
                                    'Division',
                                    'Status',
                                    'Date lodged',
                                    'Finalized / approved',
                                    'Approved by',
                                ].map((column) => (
                                    <th
                                        key={column}
                                        scope="col"
                                        className="px-4 py-3 font-medium"
                                    >
                                        {column}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {grievances.data.length ? (
                                grievances.data.map((grievance) => (
                                    <tr
                                        key={grievance.id}
                                        className="hover:bg-muted/30"
                                    >
                                        <td className="px-4 py-3 font-mono text-xs font-medium">
                                            {grievance.reference_no}
                                        </td>
                                        <td className="px-4 py-3">
                                            {grievance.category ?? '—'}
                                        </td>
                                        <td className="px-4 py-3">
                                            {grievance.division ?? '—'}
                                        </td>
                                        <td className="px-4 py-3 capitalize">
                                            {grievance.status.replaceAll(
                                                '_',
                                                ' ',
                                            )}
                                        </td>
                                        <td className="px-4 py-3">
                                            {formatDate(grievance.created_at)}
                                        </td>
                                        <td className="px-4 py-3">
                                            {formatDate(
                                                grievance.approved_at ??
                                                    grievance.closed_at,
                                            )}
                                        </td>
                                        <td className="px-4 py-3">
                                            {grievance.approver ?? '—'}
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="px-4 py-12 text-center text-muted-foreground"
                                    >
                                        No finalized grievances found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
                {grievances.last_page > 1 && (
                    <nav
                        aria-label="Detailed report pages"
                        className="flex flex-wrap items-center justify-center gap-1 border-t p-3"
                    >
                        {grievances.links.map((link, index) => (
                            <Link
                                key={`${link.label}-${index}`}
                                href={link.url ?? '#'}
                                preserveScroll
                                aria-disabled={!link.url}
                                className={`rounded-md border px-3 py-1.5 text-sm ${
                                    link.active
                                        ? 'border-primary bg-primary text-primary-foreground'
                                        : 'hover:bg-muted'
                                } ${link.url ? '' : 'pointer-events-none opacity-50'}`}
                            >
                                {link.label
                                    .replace(/<[^>]*>/g, '')
                                    .replaceAll('&laquo;', '«')
                                    .replaceAll('&raquo;', '»')}
                            </Link>
                        ))}
                    </nav>
                )}
            </section>
        </ReportLayout>
    );
}
