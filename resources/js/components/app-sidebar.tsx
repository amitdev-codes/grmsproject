import { Link } from '@inertiajs/react';
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
export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
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
