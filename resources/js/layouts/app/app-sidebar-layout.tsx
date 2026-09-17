import { AppContent } from '@/components/app-content';
import { AppFooter } from '@/components/app-footer';
import { AppShell } from '@/components/app-shell';
import { AppTopMenu } from '@/components/app-top-menu';
import { AppTopNavigation } from '@/components/app-top-navigation';
import type { AppLayoutProps } from '@/types';

export default function AppSidebarLayout({
    children
}: AppLayoutProps) {
    return (
        <AppShell>
            <AppTopNavigation />
            <AppTopMenu />
            <AppContent variant="sidebar" className="flex flex-col overflow-x-hidden">
                <div className="flex-1 p-4 md:p-6">{children}</div>
                <AppFooter />
            </AppContent>
        </AppShell>
    );
}
