import {
    FileText,
    LayoutGrid,
    MessageSquareWarning,
    FolderKanban,
    TrendingUp,
    MessageSquareText,
    History,
    CheckCircle2,
    ScrollText,
    Settings,
    ShieldCheck,
    UserCog,
    Users,
    UsersRound,
    Globe,
    Database,
    MapPinned,
    Network,
    LayoutList,
    BriefcaseBusiness,
    Building2,
    Timer,
    ArrowUpRight,
    List,
    ClipboardCheck,
    Server,
} from 'lucide-react';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

export const primaryNavItems: NavItem[] = [
    {
        title: 'menu.dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'menu.user_management',
        href: '#',
        icon: UsersRound,
        items: [
            {
                title: 'menu.permissions',
                href: '/permissions',
                icon: ShieldCheck,
            },
            { title: 'menu.roles', href: '/roles', icon: UserCog },
            { title: 'menu.users', href: '/users', icon: Users },
        ],
    },
];

export const modulesNavItems: NavItem[] = [
    {
        title: 'menu.grievances',
        href: '#',
        icon: MessageSquareWarning,
        items: [
            {
                title: 'menu.grievance-categories',
                href: '/grievance-categories',
                icon: FolderKanban,
            },
            {
                title: 'menu.grievance-channels',
                href: '/grievance-channels',
                icon: FolderKanban,
            },
            { title: 'menu.grievances', href: '/grievances', icon: FileText },
            {
                title: 'menu.grievance-escalations',
                href: '/grievance-escalations',
                icon: TrendingUp,
            },
            {
                title: 'menu.grievance-messages',
                href: '/grievance-messages',
                icon: MessageSquareText,
            },
            {
                title: 'menu.grievance-status-history',
                href: '/grievance-status-histories',
                icon: History,
            },
            {
                title: 'menu.resolutions',
                href: '/resolutions',
                icon: CheckCircle2,
            },
        ],
    },
];
export const masterNavItems: NavItem[] = [
    {
        title: 'menu.master',
        href: '#',
        icon: Database, // Best for Master Data
        items: [
            {
                title: 'menu.districts',
                href: '/districts',
                icon: MapPinned, // Good for District / Location
            },
            {
                title: 'menu.divisions',
                href: '/divisions',
                icon: Network, // Represents Divisions / Structure
            },
            {
                title: 'menu.sections',
                href: '/sections',
                icon: LayoutList, // Clean for Sections / Lists
            },
            {
                title: 'menu.project_types',
                href: '/project-types',
                icon: FolderKanban,
            },
            {
                title: 'menu.service_providers',
                href: '/service-providers',
                icon: Building2,
            },
            {
                title: 'menu.projects',
                href: '/projects',
                icon: BriefcaseBusiness,
            },
            {
                title: 'menu.sla_policies',
                href: '/grievance-sla-policies',
                icon: Timer,
            },
            {
                title: 'menu.escalation_rules',
                href: '/grievance-escalation-rules',
                icon: ArrowUpRight,
            },
        ],
    },
];

export const settingsNavItems: NavItem[] = [
    {
        title: 'menu.settings',
        href: '#',
        icon: Settings,
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
            },
        ],
    },
];

export const reportsNavItems: NavItem[] = [
    {
        title: 'menu.reports',
        href: '/reports',
        icon: FileText,
    },
];

export const logsNavItems: NavItem[] = [
    {
        title: 'menu.logs',
        href: '#',
        icon: ScrollText,
        items: [
            {
                title: 'menu.activity_logs',
                href: '/logs/activity-logs',
                icon: List,
            },
            {
                title: 'menu.audit_logs',
                href: '/logs/audit-logs',
                icon: ClipboardCheck,
            },
            {
                title: 'menu.system_logs',
                href: '/logs/system-logs',
                icon: Server,
            },
        ],
    },
];
