import { usePage } from '@inertiajs/react';
import type { SharedData } from '@/types/shared-data';

export function AppFooter() {
    const { name, app_author } = usePage<SharedData>().props;
    const currentYear = new Date().getFullYear();

    return (
        <footer className="sticky bottom-0 z-30 shrink-0 border-t border-sidebar-border bg-sidebar px-4 py-3 text-[13px] text-sidebar-foreground md:px-6">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <span className="font-medium">{name}</span>
                <span>
                    &copy; {currentYear} {app_author || 'Roads Directorate · Government of Lesotho'}
                </span>
            </div>
        </footer>
    );
}