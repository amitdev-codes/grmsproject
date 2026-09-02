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
        href: '/logs',
        icon: ScrollText,
    },
];
