import { Link, usePage } from '@inertiajs/react';
import { Menu } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { LocaleSwitcher } from '@/components/locale-switcher';
import { ThemeToggle } from '@/components/theme-toggle';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { UserMenuContent } from '@/components/user-menu-content';
import { dashboard } from '@/routes';
import type { SharedData } from '@/types/shared-data';

/** The common product bar. Navigation stays in the dedicated sidebar below it. */
export function AppTopNavigation() {
    const { auth } = usePage<SharedData>().props;

    return (
        <header className="sticky top-0 z-40 flex h-16 shrink-0 items-center border-b border-sidebar-border bg-sidebar px-4 text-sidebar-foreground shadow-sm lg:px-6">
            <div className="flex min-w-0 flex-1 items-center gap-3">
                <Link href={dashboard()} prefetch className="flex min-w-0 items-center"><AppLogo /></Link>
                <SidebarTrigger className="text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-foreground" aria-label="Toggle sidebar">
                    <Menu className="size-5" />
                </SidebarTrigger>
            </div>
            <div className="flex shrink-0 items-center gap-1">
                <LocaleSwitcher />
                <ThemeToggle />
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <button type="button" aria-label="Open user menu" className="ml-1 rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sidebar-ring">
                            <Avatar className="size-9 border border-sidebar-border">
                                <AvatarImage src={auth.user?.avatar} alt={auth.user?.name} />
                                <AvatarFallback className="bg-sidebar-primary text-sidebar-primary-foreground">{auth.user?.name?.charAt(0).toUpperCase()}</AvatarFallback>
                            </Avatar>
                        </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" className="w-56"><UserMenuContent user={auth.user!} /></DropdownMenuContent>
                </DropdownMenu>
            </div>
        </header>
    );
}
