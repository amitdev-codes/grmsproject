import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { cn } from '@/lib/utils';
import type { PaginationMeta } from '@/types/data-table';

interface DataTablePaginationProps {
    meta: PaginationMeta;
    selectedCount: number;
    perPageOptions?: number[];
    onPageChange: (page: number) => void;
    onPerPageChange: (perPage: number) => void;
}

type PageToken = number | 'ellipsis-start' | 'ellipsis-end';

/**
 * Windowed page list: always shows first + last page, the current page and
 * one sibling on each side, collapsing the rest into an ellipsis. e.g. for
 * current=6, last=12 → [1, '…', 5, 6, 7, '…', 12]
 */
function getPageTokens(
    current: number,
    last: number,
    siblingCount = 1,
): PageToken[] {
    const totalVisible = siblingCount * 2 + 5; // first, last, current, 2 ellipses

    if (last <= totalVisible) {
        return Array.from({ length: last }, (_, i) => i + 1);
    }

    const leftSibling = Math.max(current - siblingCount, 1);
    const rightSibling = Math.min(current + siblingCount, last);

    const showLeftEllipsis = leftSibling > 2;
    const showRightEllipsis = rightSibling < last - 1;

    const tokens: PageToken[] = [1];

    if (showLeftEllipsis) {
        tokens.push('ellipsis-start');
    }

    for (let page = leftSibling; page <= rightSibling; page++) {
        if (page !== 1 && page !== last) {
tokens.push(page);
}
    }

    if (showRightEllipsis) {
tokens.push('ellipsis-end');
}

    tokens.push(last);

    return tokens;
}

export function DataTablePagination({
    meta,
    selectedCount,
    perPageOptions = [10, 25, 50, 100],
    onPageChange,
    onPerPageChange,
}: DataTablePaginationProps) {
    // Defensive fallbacks — if the backend/meta hook ever sends these through as
    // undefined, "Page {current} of {last}" used to silently render
    // "Page undefined of NaN" instead of failing loudly.
    const currentPage = meta.current_page ?? 1;
    const lastPage = Math.max(meta.last_page ?? 1, 1);

    // Same fix as the toolbar select: guarantee meta.per_page has a matching
    // option so the trigger never renders blank.
    const effectivePerPageOptions = Array.from(
        new Set([...perPageOptions, meta.per_page]),
    ).sort((a, b) => a - b);

    return (
        <div className="flex flex-col-reverse items-center justify-between gap-2 text-xs sm:flex-row">
            <div className="flex-1 text-xs text-muted-foreground">
                {selectedCount > 0 ? (
                    <span>
                        {selectedCount} of {meta.total} row(s) selected.
                    </span>
                ) : (
                    <span>
                        Showing {meta.from ?? 0}–{meta.to ?? 0} of {meta.total}
                    </span>
                )}
            </div>
            <div className="flex items-center gap-4 lg:gap-5">
                <div className="flex items-center gap-1.5">
                    <p className="text-xs font-medium">Rows per page</p>
                    <Select
                        value={`${meta.per_page}`}
                        onValueChange={(value) =>
                            onPerPageChange(Number(value))
                        }
                    >
                        <SelectTrigger className="h-6.5 w-12 px-1.5 text-[11px] [&>svg]:size-3">
                            <SelectValue placeholder={meta.per_page} />
                        </SelectTrigger>
                        <SelectContent side="top">
                            {effectivePerPageOptions.map((size) => (
                                <SelectItem
                                    key={size}
                                    value={`${size}`}
                                    className="text-xs"
                                >
                                    {size}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
                <div className="flex items-center gap-1">
                    <Button
                        variant="outline"
                        className="h-6.5 w-6.5 p-0"
                        onClick={() => onPageChange(currentPage - 1)}
                        disabled={currentPage <= 1}
                    >
                        <ChevronLeft className="h-3.5 w-3.5" />
                    </Button>

                    {getPageTokens(currentPage, lastPage).map((token, i) =>
                        typeof token === 'number' ? (
                            <Button
                                key={token}
                                variant={
                                    token === currentPage
                                        ? 'default'
                                        : 'outline'
                                }
                                className={cn(
                                    'h-6.5 w-6.5 p-0 text-xs',
                                    token === currentPage &&
                                        'pointer-events-none',
                                )}
                                onClick={() => onPageChange(token)}
                            >
                                {token}
                            </Button>
                        ) : (
                            <span
                                key={`${token}-${i}`}
                                className="flex h-6.5 w-6.5 items-center justify-center text-xs text-muted-foreground"
                            >
                                …
                            </span>
                        ),
                    )}

                    <Button
                        variant="outline"
                        className="h-6.5 w-6.5 p-0"
                        onClick={() => onPageChange(currentPage + 1)}
                        disabled={currentPage >= lastPage}
                    >
                        <ChevronRight className="h-3.5 w-3.5" />
                    </Button>
                </div>
            </div>
        </div>
    );
}
