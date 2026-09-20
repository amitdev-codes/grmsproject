import { usePage } from '@inertiajs/react';
import type { SharedData } from '@/types/shared-data';

export function AppFooter() {
    const props = usePage<SharedData>().props;
    const { name, app_author, applicationSettings } = props;
    const settings = (applicationSettings as Record<string, any>) || {};
    const currentYear = new Date().getFullYear();
    const siteTotal = settings?.site_total ?? null;

    return (
        <footer className="sticky bottom-0 z-30 shrink-0 border-t border-sidebar-border bg-sidebar px-4 py-3 text-[13px] text-sidebar-foreground md:px-6">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <span className="font-medium">{name}</span>
                <div className="flex items-center gap-4">
                    {siteTotal !== null && siteTotal !== undefined && (
                        <span className="flex items-center gap-1.5 text-xs text-muted-foreground">
                            <span className="inline-block h-1.5 w-1.5 rounded-full bg-emerald-500" />
                            Total Site Visits:{' '}
                            <strong className="font-mono font-semibold text-foreground">
                                {Number(siteTotal).toLocaleString()}
                            </strong>
                        </span>
                    )}
                    <span>
                        &copy; {currentYear}{' '}
                        {app_author ||
                            'Roads Directorate · Government of Lesotho'}
                    </span>
                </div>
            </div>
        </footer>
    );
}
