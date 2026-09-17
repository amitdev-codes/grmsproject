import { AppContent } from '@/components/app-content';
import { AppFooter } from '@/components/app-footer';
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
            <AppContent variant="sidebar" className="flex flex-col overflow-x-hidden">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                <div className="flex-1 p-4 md:p-6">{children}</div>
                <AppFooter />
            </AppContent>
        </AppShell>
    );
}