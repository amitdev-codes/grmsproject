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

    return (
        <div className="flex shrink-0 items-center gap-2 whitespace-nowrap">
            <span className="text-xs font-medium text-muted-foreground">
                {selectedIds.length} row{selectedIds.length > 1 ? 's' : ''}{' '}
                selected
            </span>
            <AlertDialog>
                <AlertDialogTrigger asChild>
                    <Button
                        variant="destructive"
                        size="sm"
                        className="h-6.5 px-2 text-xs"
                    >
                        <Trash2 className="mr-1 h-3 w-3" />
                        Delete selected
                    </Button>
                </AlertDialogTrigger>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            Delete {selectedIds.length} selected row
                            {selectedIds.length > 1 ? 's' : ''}?
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            This action cannot be undone.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction onClick={handleDelete}>
                            Delete
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    );
}
