import { Link, usePage } from '@inertiajs/react';
import {
    ArrowUpRight,
    CheckCircle2,
    ClipboardList,
    FileText,
    Mic,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { SidebarUserProfile } from '@/components/sidebar-user-profile';
import {
    Sidebar,
    SidebarContent,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import {
    primaryNavItems,
    modulesNavItems,
    masterNavItems,
    reportsNavItems,
    logsNavItems,
    settingsNavItems,
} from '@/config/navigation';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';
export function AppSidebar() {
    const page = usePage();
    const auth = page.props.auth as
        | { user?: { role_names?: string; permissions?: string[] } }
        | undefined;
    const roleNames =
        auth?.user?.role_names?.split(',').map((role) => role.trim()) ?? [];
    const isAdminListUser = roleNames.some((role) =>
        ['Admin', 'Super Admin'].includes(role),
    );
    const isDirector = roleNames.includes('Director');
    const canViewGrievances =
        auth?.user?.permissions?.includes('grievances.view') ?? false;
    const grievanceListItems: NavItem[] = isAdminListUser
        ? [
              {
                  title: 'List grievances',
                  href: '#',
                  icon: ClipboardList,
                  items: [
                      {
                          title: 'Pending grievances',
                          href: '/grievances?list=pending',
                          icon: ClipboardList,
                      },
                      {
                          title: 'Resolved grievances',
                          href: '/grievances?list=resolved',
                          icon: CheckCircle2,
                      },
                      {
                          title: 'Rejected grievance',
                          href: '/grievances?list=rejected',
                          icon: FileText,
                      },
                      {
                          title: 'Voice grievance',
                          href: '/voice-grievances',
                          icon: Mic,
                      },
                  ],
              },
          ]
        : isDirector
          ? [
              {
                  title: 'List grievances',
                  href: '#',
                  icon: ClipboardList,
                  items: [
                      {
                          title: 'Resolved grievances',
                          href: '/grievances?list=resolved',
                          icon: CheckCircle2,
                      },
                      {
                          title: 'Voice grievance',
                          href: '/voice-grievances',
                          icon: Mic,
                      },
                  ],
              },
          ]
          : [
              {
                  title: 'List grievances',
                  href: '#',
                  icon: ClipboardList,
                  items: [
                      {
                          title: 'Forwarded grievance',
                          href: '/grievances?list=forwarded',
                          icon: ArrowUpRight,
                      },
                      {
                          title: 'Voice grievance',
                          href: '/voice-grievances',
                          icon: Mic,
                      },
                  ],
              },
          ];

    return (
        <Sidebar
            collapsible="icon"
            variant="inset"
            className="app-sidebar-below-header"
        >
            <SidebarHeader className="border-b border-sidebar-border/70 pb-0">
                <SidebarMenu className="border-b border-sidebar-border/70 pb-3">
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
                <SidebarUserProfile />
            </SidebarHeader>
            <SidebarContent className="py-3">
                <NavMain items={primaryNavItems} />
                <NavMain items={modulesNavItems} label="Modules" />
                {canViewGrievances && <NavMain items={grievanceListItems} />}
                <NavMain items={masterNavItems} label="Master" />
                <NavMain items={settingsNavItems} label="Settings" />
                <NavMain items={reportsNavItems} label="Reports" />
                <NavMain items={logsNavItems} label="Logs" />
            </SidebarContent>
            {/*<SidebarFooter>*/}
            {/*    <NavUser />*/}
            {/*</SidebarFooter>*/}
        </Sidebar>
    );
}
