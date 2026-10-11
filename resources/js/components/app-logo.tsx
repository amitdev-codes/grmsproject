import { usePage } from '@inertiajs/react';
import type { SharedData } from '@/types/shared-data';

export default function AppLogo() {
    const { applicationSettings } = usePage<SharedData>().props;
    const projectName =
        applicationSettings?.project_name ??
        'Grievance Redressal Management System';

    return (
        <div className="flex min-w-0 items-center gap-2">
            <img
                src={applicationSettings?.logo_url ?? '/logo.png'}
                alt={applicationSettings?.short_name ?? projectName}
                className="size-10 shrink-0 rounded-md object-contain"
                loading="eager"
            />
            <div className="grid min-w-0 flex-1 text-left leading-tight">
                <span className="truncate text-sm font-bold" title={projectName}>
                    {projectName}
                </span>
                <span className="truncate text-xs font-medium opacity-80">
                    {applicationSettings?.tagline ??
                        'Roads Directorate · Government of Lesotho'}
                </span>
            </div>
        </div>
    );
}