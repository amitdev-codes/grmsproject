import { router } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';
import type { DataTableRoutes } from '@/types/data-table';

interface DataTableBulkActionsBarProps {
    selectedIds: (string | number)[];
    routes: DataTableRoutes;
    onCleared: () => void;
}

/**
 * Compact bulk-actions strip: "N rows selected" + a single "Delete selected"
 * button behind a confirmation dialog. Renders nothing once selection is
 * empty — deleting (or the caller clearing selection) removes it automatically,
 * so there's no separate "clear" control to manage or overlap.
 */
export function DataTableBulkActionsBar({
    selectedIds,
    routes,
    onCleared,
}: DataTableBulkActionsBarProps) {
    const { t } = useTranslation();
    if (selectedIds.length === 0 || !routes.bulkDestroy) {
        return null;
    }

    const handleDelete = () => {
        if (typeof route !== 'function') {
            console.warn(
                '[DataTableBulkActionsBar] `route()` is not defined globally — cannot build bulk-destroy URL.',
            );

            return;
        }

        router.delete(
            route(routes.bulkDestroy as string, { ids: selectedIds }),
            {
                preserveScroll: true,
                onSuccess: () => onCleared(),
            },
        );
    };

    const count = selectedIds.length;
    const rowLabel = count > 1 ? t('menu.rows') : t('menu.row');

    return (
        <div className="flex shrink-0 items-center gap-2 whitespace-nowrap">
            <span className="text-xs font-medium text-muted-foreground">
                {count} {rowLabel} {t('menu.selected_count', { count: '' }).trim()}
            </span>
            <AlertDialog>
                <AlertDialogTrigger asChild>
                    <Button
                        variant="destructive"
                        size="sm"
                        className="h-6.5 px-2 text-xs"
                    >
                        <Trash2 className="mr-1 h-3 w-3" />
                        {t('menu.delete_selected')}
                    </Button>
                </AlertDialogTrigger>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            {t('menu.delete')} {count} {rowLabel}?
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            {t('menu.cannot_be_undone')}
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>{t('menu.cancel')}</AlertDialogCancel>
                        <AlertDialogAction onClick={handleDelete}>
                            {t('menu.delete')}
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    );
}
