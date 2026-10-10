export interface ReportColumn {
    key: string;
    label: string;
    align?: 'left' | 'right';
}

export type ReportRow = Record<string, string | number | null>;

export function ReportTable({
    title,
    columns,
    rows = [],
    emptyMessage = 'No report data is available.',
}: {
    title: string;
    columns: ReportColumn[];
    rows: ReportRow[];
    emptyMessage?: string;
}) {
    return (
        <section className="overflow-hidden rounded-lg border bg-card">
            <div className="border-b px-4 py-3">
                <h2 className="font-semibold">{title}</h2>
            </div>
            <div className="overflow-x-auto">
                <table className="w-full min-w-[520px] text-left text-sm">
                    <thead className="bg-muted/60 text-muted-foreground">
                        <tr>
                            {columns.map((column) => (
                                <th
                                    key={column.key}
                                    scope="col"
                                    className={`px-4 py-3 font-medium ${
                                        column.align === 'right'
                                            ? 'text-right'
                                            : ''
                                    }`}
                                >
                                    {column.label}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {rows.length ? (
                            rows.map((row, index) => (
                                <tr
                                    key={`${title}-${index}`}
                                    className="hover:bg-muted/30"
                                >
                                    {columns.map((column) => (
                                        <td
                                            key={column.key}
                                            className={`px-4 py-3 ${
                                                column.align === 'right'
                                                    ? 'text-right tabular-nums'
                                                    : ''
                                            }`}
                                        >
                                            {row[column.key] ?? '—'}
                                        </td>
                                    ))}
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td
                                    colSpan={columns.length}
                                    className="px-4 py-10 text-center text-muted-foreground"
                                >
                                    {emptyMessage}
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </section>
    );
}
