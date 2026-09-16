import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { AppTopMenu } from '@/components/app-top-menu';
import { AppTopNavigation } from '@/components/app-top-navigation';
import type { AppLayoutProps } from '@/types';

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
}: AppLayoutProps) {
    return (
        <AppShell>
            <AppTopNavigation />
            <AppTopMenu />
            <AppContent variant="sidebar" className="overflow-x-hidden">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                <div className="p-4 md:p-6">{children}</div>
            </AppContent>
        </AppShell>
    );
}
