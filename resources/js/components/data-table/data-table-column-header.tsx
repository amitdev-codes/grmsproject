import type { Column } from '@tanstack/react-table';
import { ArrowDown, ArrowUp, ChevronsUpDown, EyeOff } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useTranslation } from '@/hooks/use-translation';
import { cn } from '@/lib/utils';

interface DataTableColumnHeaderProps<
    TData,
    TValue,
> extends React.HTMLAttributes<HTMLDivElement> {
    column: Column<TData, TValue>;
    title: string;
}

/**
 * Use inside a column's `header:` definition, e.g.
 *   header: ({ column }) => <DataTableColumnHeader column={column} title="Name" />
 * Sorting is manual/server-side: it reads/writes the sort state via
 * `table.options.meta` set up by <DataTable/>.
 */
export function DataTableColumnHeader<TData, TValue>({
    column,
    title,
    className,
}: DataTableColumnHeaderProps<TData, TValue>) {
    const { t, tLabel } = useTranslation();
    const translatedTitle = tLabel(title);

    if (!column.getCanSort()) {
        return (
            <div className={cn('text-[11px] font-semibold', className)}>
                {translatedTitle}
            </div>
        );
    }

    const sorted = column.getIsSorted();

    return (
        <div className={cn('flex items-center', className)}>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button
                        variant="ghost"
                        size="sm"
                        className="-ml-2 h-6.5 px-2 text-[11px] font-semibold tracking-wide uppercase data-[state=open]:bg-accent"
                    >
                        <span>{translatedTitle}</span>
                        {sorted === 'desc' ? (
                            <ArrowDown className="ml-1.5 h-3 w-3" />
                        ) : sorted === 'asc' ? (
                            <ArrowUp className="ml-1.5 h-3 w-3" />
                        ) : (
                            <ChevronsUpDown className="ml-1.5 h-3 w-3 opacity-50" />
                        )}
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="start">
                    <DropdownMenuItem
                        onClick={() => column.toggleSorting(false)}
                    >
                        <ArrowUp className="mr-2 h-3.5 w-3.5 text-muted-foreground/70" />
                        {t('menu.ascending')}
                    </DropdownMenuItem>
                    <DropdownMenuItem
                        onClick={() => column.toggleSorting(true)}
                    >
                        <ArrowDown className="mr-2 h-3.5 w-3.5 text-muted-foreground/70" />
                        {t('menu.descending')}
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem
                        onClick={() => column.toggleVisibility(false)}
                    >
                        <EyeOff className="mr-2 h-3.5 w-3.5 text-muted-foreground/70" />
                        {t('menu.hide_column')}
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}
