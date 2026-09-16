import { Link, usePage } from '@inertiajs/react';
import {
    LayoutGrid,
    MessageSquareWarning,
    Database,
    FileText,
    ScrollText,
    Settings,
    ChevronDown,
} from 'lucide-react';
import type { ComponentType } from 'react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useTranslation } from '@/hooks/use-translation';
import { dashboard } from '@/routes';

function isActiveHref(currentUrl: string, href: string) {
    return href !== '#' && currentUrl.startsWith(href);
}

export function AppTopMenu() {
    const page = usePage();
    const { t } = useTranslation();

    const groups: {
        label: string;
        href?: string;
        icon?: ComponentType<{ className?: string }>;
        items?: { title: string; href: string }[];
    }[] = [
        { label: 'menu.dashboard', href: dashboard(), icon: LayoutGrid },
        {
            label: 'menu.grievances',
            icon: MessageSquareWarning,
            items: [
                { title: 'menu.grievance-categories', href: '/grievance-categories' },
                { title: 'menu.grievance-channels', href: '/grievance-channels' },
                { title: 'menu.grievances', href: '/grievances' },
                { title: 'menu.grievance-escalations', href: '/grievance-escalations' },
                { title: 'menu.grievance-messages', href: '/grievance-messages' },
                { title: 'menu.grievance-status-history', href: '/grievance-status-histories' },
                { title: 'menu.resolutions', href: '/resolutions' },
            ],
        },
        {
            label: 'menu.master',
            icon: Database,
            items: [
                { title: 'menu.districts', href: '/districts' },
                { title: 'menu.divisions', href: '/divisions' },
                { title: 'menu.sections', href: '/sections' },
                { title: 'menu.project_types', href: '/project-types' },
                { title: 'menu.service_providers', href: '/service-providers' },
                { title: 'menu.projects', href: '/projects' },
                { title: 'menu.sla_policies', href: '/grievance-sla-policies' },
                { title: 'menu.escalation_rules', href: '/grievance-escalation-rules' },
            ],
        },
        {
            label: 'menu.reports',
            icon: FileText,
            items: [
                { title: 'menu.reports', href: '/reports' },
                { title: 'Summary report', href: '/reports/summary' },
            ],
        },
        {
            label: 'menu.logs',
            icon: ScrollText,
            items: [{ title: 'menu.logs', href: '/logs' }],
        },
        {
            label: 'menu.settings',
            icon: Settings,
            items: [
                { title: 'menu.profile_settings', href: '/edit-profile' },
                { title: 'menu.application_settings', href: '/settings/application' },
            ],
        },
    ];

    return (
        <nav
            className="sticky top-16 z-30 flex h-12 shrink-0 items-center justify-center gap-2 overflow-x-auto border-b border-sidebar-border bg-sidebar px-2 text-sidebar-foreground md:px-4"
            aria-label="Primary navigation"
        >
            {groups.map((group) => {
                const label = t(group.label);
                const Icon = group.icon;
                const active = group.href
                    ? isActiveHref(page.url, group.href)
                    : group.items?.some((item) => isActiveHref(page.url, item.href)) ?? false;

                const trigger = (
                    <Button
                        variant="ghost"
                        size="sm"
                        className={`h-9 gap-2 rounded-md px-3 text-sm font-medium text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none ${
                            active ? 'bg-sidebar-accent text-sidebar-accent-foreground' : ''
                        }`}
                    >
                        {Icon && <Icon className="size-4" />}
                        <span>{label}</span>
                        {group.items && <ChevronDown className="size-4" />}
                    </Button>
                );

                if (group.href) {
                    return (
                        <Link key={group.label} href={group.href} prefetch>
                            {trigger}
                        </Link>
                    );
                }

                return (
                    <DropdownMenu key={group.label}>
                        <DropdownMenuTrigger asChild>
                            {trigger}
                        </DropdownMenuTrigger>
                        <DropdownMenuContent
                            align="start"
                            className="min-w-56 rounded-md border bg-popover p-1 text-popover-foreground shadow-lg"
                        >
                            {(group.items ?? []).map((item) => {
                                const itemActive = isActiveHref(page.url, item.href);

                                return (
                                    <DropdownMenuItem
                                        key={item.title}
                                        asChild
                                        className={`rounded-sm px-2 py-1.5 text-sm focus:bg-accent focus:text-accent-foreground ${
                                            itemActive ? 'bg-accent text-accent-foreground' : ''
                                        }`}
                                    >
                                        <Link href={item.href} prefetch>
                                            {t(item.title)}
                                        </Link>
                                    </DropdownMenuItem>
                                );
                            })}
                        </DropdownMenuContent>
                    </DropdownMenu>
                );
            })}
        </nav>
    );
}