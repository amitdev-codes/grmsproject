import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';


interface ModalLayoutProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;

    title: string;
    description?: string;

    mode?: 'create' | 'edit';

    processing?: boolean;

    onSubmit: (e: React.SyntheticEvent<HTMLFormElement>) => void;

    children: ReactNode;
}

export default function ModalLayout({
    open,
    onOpenChange,
    title,
    description,
    mode = 'create',
    processing = false,
    onSubmit,
    children,
}: ModalLayoutProps) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-2xl">
                <form onSubmit={onSubmit}>
                    <DialogHeader>
                        <DialogTitle>{title}</DialogTitle>

                        {description && (
                            <DialogDescription>{description}</DialogDescription>
                        )}
                    </DialogHeader>

                    <div className="max-h-[65vh] space-y-4 overflow-y-auto py-6">
                        {children}
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => onOpenChange(false)}
                        >
                            Cancel
                        </Button>

                        <Button type="submit" disabled={processing}>
                            {processing
                                ? mode === 'create'
                                    ? 'Saving...'
                                    : 'Updating...'
                                : mode === 'create'
                                  ? 'Save'
                                  : 'Update'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
