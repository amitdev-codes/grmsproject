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

interface MenuItem {
    title: string;
    href: string;
    icon?: ComponentType<{ className?: string }>;
    permission?: string;
}

interface MenuGroup {
    label: string;
    href?: string;
    icon?: ComponentType<{ className?: string }>;
    items?: MenuItem[];
    permission?: string;
}

function isActiveHref(currentUrl: string, href: string) {
    return href !== '#' && currentUrl.startsWith(href);
}

function hasPermission(permissions: string[], permission?: string): boolean {
    if (!permission) return true;
    return permissions.includes(permission);
}

export function AppTopMenu() {
    const page = usePage();
    const { t } = useTranslation();

    const auth = page.props.auth as { user?: { permissions?: string[] } } | undefined;
    const permissions = auth?.user?.permissions ?? [];

    const allGroups: MenuGroup[] = [
        { label: 'menu.dashboard', href: dashboard(), icon: LayoutGrid },
        {
            label: 'menu.user_management',
            icon: UsersRound,
            items: [
                { title: 'menu.permissions', href: '/permissions', icon: ShieldCheck, permission: 'permissions.view' },
                { title: 'menu.roles', href: '/roles', icon: UserCog, permission: 'roles.view' },
                { title: 'menu.users', href: '/users', icon: Users, permission: 'users.view' },
            ],
            permission: 'users.view',
        },
        {
            label: 'menu.grievances',
            icon: MessageSquareWarning,
            items: [
                { title: 'menu.grievance-categories', href: '/grievance-categories', icon: FolderKanban, permission: 'grievances.view' },
                { title: 'menu.grievance-channels', href: '/grievance-channels', icon: FolderKanban, permission: 'grievances.view' },
                { title: 'menu.grievances', href: '/grievances', icon: FileText, permission: 'grievances.view' },
                { title: 'menu.grievance-escalations', href: '/grievance-escalations', icon: TrendingUp, permission: 'grievances.escalate' },
                { title: 'menu.grievance-messages', href: '/grievance-messages', icon: MessageSquareText, permission: 'grievances.view' },
                { title: 'menu.grievance-status-history', href: '/grievance-status-histories', icon: History, permission: 'grievances.view' },
                { title: 'menu.resolutions', href: '/resolutions', icon: CheckCircle2, permission: 'grievances.resolve' },
            ],
            permission: 'grievances.view',
        },
        {
            label: 'menu.master',
            icon: Database,
            items: [
                { title: 'menu.districts', href: '/districts', icon: MapPinned, permission: 'districts.view' },
                { title: 'menu.divisions', href: '/divisions', icon: Network, permission: 'divisions.view' },
                { title: 'menu.sections', href: '/sections', icon: LayoutList, permission: 'sections.view' },
                { title: 'menu.project_types', href: '/project-types', icon: FolderKanban, permission: 'projects.view' },
                { title: 'menu.service_providers', href: '/service-providers', icon: Building2, permission: 'projects.view' },
                { title: 'menu.projects', href: '/projects', icon: BriefcaseBusiness, permission: 'projects.view' },
                { title: 'menu.sla_policies', href: '/grievance-sla-policies', icon: Timer, permission: 'grievances.view' },
                { title: 'menu.escalation_rules', href: '/grievance-escalation-rules', icon: ArrowUpRight, permission: 'grievances.escalate' },
            ],
            permission: 'districts.view',
        },
        {
            label: 'menu.reports',
            icon: FileText,
            items: [
                { title: 'menu.reports', href: '/reports', icon: FileText, permission: 'reports.view' },
                { title: 'Summary report', href: '/reports/summary', icon: FileText, permission: 'reports.view' },
            ],
            permission: 'reports.view',
        },
        {
            label: 'menu.logs',
            icon: ScrollText,
            items: [{ title: 'menu.logs', href: '/logs', icon: ScrollText, permission: 'audit_logs.view' }],
            permission: 'audit_logs.view',
        },
        {
            label: 'menu.settings',
            icon: Settings,
            items: [
                { title: 'menu.profile_settings', href: '/edit-profile', icon: UserCog },
                { title: 'menu.application_settings', href: '/settings/application', icon: Globe, permission: 'settings.edit' },
            ],
        },
    ];

    // Filter groups and items based on permissions
    const groups = allGroups
        .filter((group) => hasPermission(permissions, group.permission))
        .map((group) => ({
            ...group,
            items: group.items?.filter((item) => hasPermission(permissions, item.permission)) ?? [],
        }))
        .filter((group) => !group.items || group.items.length > 0 || group.href);

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