import { Link, usePage } from '@inertiajs/react';
import {
    LayoutGrid,
    MessageSquareWarning,
    Database,
    FileText,
    ScrollText,
    Settings,
    ChevronDown,
    ShieldCheck,
    UserCog,
    Users,
    UsersRound,
    FolderKanban,
    TrendingUp,
    MessageSquareText,
    History,
    CheckCircle2,
    MapPinned,
    Network,
    LayoutList,
    Building2,
    BriefcaseBusiness,
    Timer,
    ArrowUpRight,
    Globe,
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
        items?: { title: string; href: string; icon?: ComponentType<{ className?: string }> }[];
    }[] = [
        { label: 'menu.dashboard', href: dashboard(), icon: LayoutGrid },
        {
            label: 'menu.user_management',
            icon: UsersRound,
            items: [
                { title: 'menu.permissions', href: '/permissions', icon: ShieldCheck },
                { title: 'menu.roles', href: '/roles', icon: UserCog },
                { title: 'menu.users', href: '/users', icon: Users },
            ],
        },
        {
            label: 'menu.grievances',
            icon: MessageSquareWarning,
            items: [
                { title: 'menu.grievance-categories', href: '/grievance-categories', icon: FolderKanban },
                { title: 'menu.grievance-channels', href: '/grievance-channels', icon: FolderKanban },
                { title: 'menu.grievances', href: '/grievances', icon: FileText },
                { title: 'menu.grievance-escalations', href: '/grievance-escalations', icon: TrendingUp },
                { title: 'menu.grievance-messages', href: '/grievance-messages', icon: MessageSquareText },
                { title: 'menu.grievance-status-history', href: '/grievance-status-histories', icon: History },
                { title: 'menu.resolutions', href: '/resolutions', icon: CheckCircle2 },
            ],
        },
        {
            label: 'menu.master',
            icon: Database,
            items: [
                { title: 'menu.districts', href: '/districts', icon: MapPinned },
                { title: 'menu.divisions', href: '/divisions', icon: Network },
                { title: 'menu.sections', href: '/sections', icon: LayoutList },
                { title: 'menu.project_types', href: '/project-types', icon: FolderKanban },
                { title: 'menu.service_providers', href: '/service-providers', icon: Building2 },
                { title: 'menu.projects', href: '/projects', icon: BriefcaseBusiness },
                { title: 'menu.sla_policies', href: '/grievance-sla-policies', icon: Timer },
                { title: 'menu.escalation_rules', href: '/grievance-escalation-rules', icon: ArrowUpRight },
            ],
        },
        {
            label: 'menu.reports',
            icon: FileText,
            items: [
                { title: 'menu.reports', href: '/reports', icon: FileText },
                { title: 'Summary report', href: '/reports/summary', icon: FileText },
            ],
        },
        {
            label: 'menu.logs',
            icon: ScrollText,
            items: [{ title: 'menu.logs', href: '/logs', icon: ScrollText }],
        },
        {
            label: 'menu.settings',
            icon: Settings,
            items: [
                { title: 'menu.profile_settings', href: '/edit-profile', icon: UserCog },
                { title: 'menu.application_settings', href: '/settings/application', icon: Globe },
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
                                const ItemIcon = item.icon;

                                return (
                                    <DropdownMenuItem
                                        key={item.title}
                                        asChild
                                        className={`rounded-sm px-2 py-1.5 text-sm focus:bg-accent focus:text-accent-foreground ${
                                            itemActive ? 'bg-accent text-accent-foreground' : ''
                                        }`}
                                    >
                                        <Link href={item.href} prefetch>
                                            {ItemIcon && <ItemIcon className="mr-2 size-4" />}
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