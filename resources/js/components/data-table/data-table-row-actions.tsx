import { router } from '@inertiajs/react';
import { Eye, Pencil, Trash2 } from 'lucide-react';
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
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { cn } from '@/lib/utils';
import type { DataTableRoutes } from '@/types/data-table';

interface DataTableRowActionsProps<TData> {
    id: string;
    row: TData;
    routes: DataTableRoutes;
    label?: string;
    onView?: (row: TData) => void;
    onEdit?: (row: TData) => void;
}

function safeRoute(name: string, params?: Record<string, unknown>) {
    if (typeof route !== 'function') {
        console.warn(
            '[DataTableRowActions] `route()` is not defined globally — cannot build URL.',
        );

        return null;
    }

    return route(name, params);
}

export function DataTableRowActions<TData>({
    id,
    row,
    routes,
    label,
    onView,
    onEdit,
}: DataTableRowActionsProps<TData>) {
    const showView = Boolean(onView || routes.view);
    const showEdit = Boolean(onEdit || routes.edit);
    const showDelete = Boolean(routes.destroy);

    const handleView = () => {
        if (onView) {
            return onView(row);
        }

        const url = safeRoute(routes.view as string, { id });

        if (url) {
            router.visit(url);
        }
    };

    const handleEdit = () => {
        if (onEdit) {
            return onEdit(row);
        }

        const url = safeRoute(routes.edit as string, { id });

        if (url) {
            router.visit(url);
        }
    };

    const handleDelete = () => {
        const url = safeRoute(routes.destroy as string, { id });

        if (url) {
            router.delete(url, { preserveScroll: true });
        }
    };

    return (
        <div className="flex items-center gap-1">
            {showView && (
                <Tooltip>
                    <TooltipTrigger asChild>
                        <Button
                            variant="outline"
                            size="icon"
                            className={cn(
                                'h-6.5 w-6.5 border-green-200 text-green-600',
                                'hover:bg-green-50 hover:text-green-700',
                                'dark:border-green-900 dark:text-green-500 dark:hover:bg-green-950 dark:hover:text-green-400',
                            )}
                            onClick={handleView}
                        >
                            <Eye className="h-3.5 w-3.5" />
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent className="border-green-600 bg-green-600 fill-green-600 text-white">
                        View
                    </TooltipContent>
                </Tooltip>
            )}

            {showEdit && (
                <Tooltip>
                    <TooltipTrigger asChild>
                        <Button
                            variant="outline"
                            size="icon"
                            className={cn(
                                'h-6.5 w-6.5 border-purple-200 text-purple-600',
                                'hover:bg-purple-50 hover:text-purple-700',
                                'dark:border-purple-900 dark:text-purple-500 dark:hover:bg-purple-950 dark:hover:text-purple-400',
                            )}
                            onClick={handleEdit}
                        >
                            <Pencil className="h-3.5 w-3.5" />
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent className="border-purple-600 bg-purple-600 fill-purple-600 text-white">
                        Edit
                    </TooltipContent>
                </Tooltip>
            )}

            {showDelete && (
                <AlertDialog>
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <AlertDialogTrigger asChild>
                                <Button
                                    variant="outline"
                                    size="icon"
                                    className="h-6.5 w-6.5 border-red-200 text-red-600 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-950 dark:hover:text-red-400"
                                >
                                    <Trash2 className="h-3.5 w-3.5" />
                                </Button>
                            </AlertDialogTrigger>
                        </TooltipTrigger>
                        <TooltipContent>Delete</TooltipContent>
                    </Tooltip>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>
                                Delete{label ? ` "${label}"` : ' this record'}?
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
            )}
        </div>
    );
}
