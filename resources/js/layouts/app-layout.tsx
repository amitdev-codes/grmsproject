import { usePage } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import { toast } from 'sonner';
import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem } from '@/types';

interface FlashProps {
    success?: string;
    error?: string;
    import_failures?: unknown;
}

export default function AppLayout({
                                      breadcrumbs = [],
                                      children,
                                  }: {
    breadcrumbs?: BreadcrumbItem[];
    children: React.ReactNode;
}) {
    const { flash } = usePage().props as { flash?: FlashProps };

    // Prevent the same flash message re-firing on unrelated re-renders
    const lastFlashKey = useRef<string | null>(null);

    useEffect(() => {
        if (!flash) {
            return;
        }

        const key = JSON.stringify(flash);

        if (key === lastFlashKey.current) {
            return;
        }

        lastFlashKey.current = key;

        if (flash.success) {
            toast.success(flash.success);
        }

        if (flash.error) {
            toast.error(flash.error);
        }
    }, [flash]);

    return (
        <AppLayoutTemplate breadcrumbs={breadcrumbs}>
            {children}
        </AppLayoutTemplate>
    );
}
