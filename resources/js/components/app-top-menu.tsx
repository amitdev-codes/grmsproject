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
    ClipboardList,
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
import { cn } from '@/lib/utils';

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
    badge?: number;
    visible?: boolean;
}

function isActiveHref(currentUrl: string, href: string) {
    return href !== '#' && currentUrl.startsWith(href);
}

function hasPermission(permissions: string[], permission?: string): boolean {
    if (!permission) {
        return true;
    }

    return permissions.includes(permission);
}

export function AppTopMenu() {
    const page = usePage();
    const { t } = useTranslation();

    const auth = page.props.auth as
        { user?: { permissions?: string[] } } | undefined;
    const permissions = auth?.user?.permissions ?? [];
    const roleNames =
        auth?.user?.role_names?.split(',').map((role) => role.trim()) ?? [];
    const isPrivileged = roleNames.some((role) =>
        ['Super Admin', 'IT Admin', 'Admin', 'Developer'].includes(role),
    );
    const pendingGrievances = page.props.pendingGrievances as {
        count: number;
        href: string;
        label: string;
        visible: boolean;
    };

    const allGroups: MenuGroup[] = [
        { label: 'menu.dashboard', href: '/dashboard', icon: LayoutGrid },
        {
            label: 'menu.user_management',
            icon: UsersRound,
            items: [
                {
                    title: 'menu.permissions',
                    href: '/permissions',
                    icon: ShieldCheck,
                    permission: 'permissions.view',
                },
                {
                    title: 'menu.roles',
                    href: '/roles',
                    icon: UserCog,
                    permission: 'roles.view',
                },
                {
                    title: 'menu.users',
                    href: '/users',
                    icon: Users,
                    permission: 'users.view',
                },
            ],
            permission: 'users.view',
            visible: isPrivileged,
        },
        {
            label: pendingGrievances.label,
            href: pendingGrievances.href,
            icon: ClipboardList,
            permission: 'grievances.view',
            badge: pendingGrievances.count,
            visible: pendingGrievances.visible,
        },
        {
            label: 'menu.grievances',
            icon: MessageSquareWarning,
            items: [
                {
                    title: 'menu.grievance-categories',
                    href: '/grievance-categories',
                    icon: FolderKanban,
                    permission: 'grievances.view',
                },
                {
                    title: 'menu.grievance-channels',
                    href: '/grievance-channels',
                    icon: FolderKanban,
                    permission: 'grievances.view',
                },
                {
                    title: 'menu.grievances',
                    href: '/grievances',
                    icon: FileText,
                    permission: 'grievances.view',
                },
                {
                    title: 'menu.grievance-escalations',
                    href: '/grievance-escalations',
                    icon: TrendingUp,
                    permission: 'grievances.escalate',
                },
                {
                    title: 'menu.grievance-messages',
                    href: '/grievance-messages',
                    icon: MessageSquareText,
                    permission: 'grievances.view',
                },
                {
                    title: 'menu.grievance-status-history',
                    href: '/grievance-status-histories',
                    icon: History,
                    permission: 'grievances.view',
                },
                {
                    title: 'menu.resolutions',
                    href: '/resolutions',
                    icon: CheckCircle2,
                    permission: 'grievances.resolve',
                },
            ],
            permission: 'grievances.view',
            visible: isPrivileged,
        },
        {
            label: 'menu.master',
            icon: Database,
            items: [
                {
                    title: 'menu.districts',
                    href: '/districts',
                    icon: MapPinned,
                    permission: 'districts.view',
                },
                {
                    title: 'menu.divisions',
                    href: '/divisions',
                    icon: Network,
                    permission: 'divisions.view',
                },
                {
                    title: 'menu.sections',
                    href: '/sections',
                    icon: LayoutList,
                    permission: 'sections.view',
                },
                {
                    title: 'menu.project_types',
                    href: '/project-types',
                    icon: FolderKanban,
                    permission: 'projects.view',
                },
                {
                    title: 'menu.service_providers',
                    href: '/service-providers',
                    icon: Building2,
                    permission: 'projects.view',
                },
                {
                    title: 'menu.projects',
                    href: '/projects',
                    icon: BriefcaseBusiness,
                    permission: 'projects.view',
                },
                {
                    title: 'menu.sla_policies',
                    href: '/grievance-sla-policies',
                    icon: Timer,
                    permission: 'grievances.view',
                },
                {
                    title: 'menu.escalation_rules',
                    href: '/grievance-escalation-rules',
                    icon: ArrowUpRight,
                    permission: 'grievances.escalate',
                },
            ],
            permission: 'districts.view',
            visible: isPrivileged,
        },
        {
            label: 'menu.reports',
            icon: FileText,
            items: [
                {
                    title: 'menu.reports',
                    href: '/reports',
                    icon: FileText,
                    permission: 'reports.view',
                },
                {
                    title: 'Summary report',
                    href: '/reports/summary',
                    icon: FileText,
                    permission: 'reports.view',
                },
            ],
            permission: 'reports.view',
            visible: isPrivileged,
        },
        {
            label: 'menu.logs',
            icon: ScrollText,
            items: [
                {
                    title: 'menu.logs',
                    href: '/logs',
                    icon: ScrollText,
                    permission: 'audit_logs.view',
                },
            ],
            permission: 'audit_logs.view',
            visible: isPrivileged,
        },
        {
            label: 'menu.settings',
            icon: Settings,
            visible: isPrivileged,
            items: [
                {
                    title: 'menu.profile_settings',
                    href: '/edit-profile',
                    icon: UserCog,
                },
                {
                    title: 'menu.application_settings',
                    href: '/settings/application',
                    icon: Globe,
                    permission: 'settings.edit',
                },
            ],
        },
    ];

    // Filter groups and items based on permissions
    const groups = allGroups
        .filter((group) => hasPermission(permissions, group.permission))
        .filter((group) => group.visible !== false)
        .map((group) => ({
            ...group,
            items:
                group.items?.filter((item) =>
                    hasPermission(permissions, item.permission),
                ) ?? [],
        }))
        .filter(
            (group) => !group.items || group.items.length > 0 || group.href,
        );

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
                    : (group.items?.some((item) =>
                          isActiveHref(page.url, item.href),
                      ) ?? false);

                const trigger = (
                    <Button
                        variant="ghost"
                        size="sm"
                        className={`h-9 gap-2 rounded-md px-3 text-sm font-medium text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none ${
                            active
                                ? 'bg-sidebar-accent text-sidebar-accent-foreground'
                                : ''
                        }`}
                    >
                        {Icon && <Icon className="size-4" />}
                        <span>{label}</span>
                        {typeof group.badge === 'number' && (
                            <span className="inline-flex min-w-5 items-center justify-center rounded-full bg-sidebar-accent px-1.5 text-xs">
                                {group.badge > 99 ? '99+' : group.badge}
                            </span>
                        )}
                        {group.items && group.items.length > 0 && (
                            <ChevronDown className="size-4" />
                        )}
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
                                const itemActive = isActiveHref(
                                    page.url,
                                    item.href,
                                );
                                const ItemIcon = item.icon;

                                return (
                                    <DropdownMenuItem
                                        key={item.href}
                                        asChild
                                        style={{ cursor: 'pointer' }}
                                        className={cn(
                                            'cursor-pointer! rounded-sm px-2 py-1.5 text-sm focus:bg-accent focus:text-accent-foreground',
                                            itemActive &&
                                                'bg-accent text-accent-foreground',
                                        )}
                                    >
                                        <Link href={item.href} prefetch>
                                            {ItemIcon && (
                                                <ItemIcon className="mr-2 size-4" />
                                            )}
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
