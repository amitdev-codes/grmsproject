import { Link, usePage } from '@inertiajs/react';
import AppLogo from '@/components/app-logo';
import { LocaleSwitcher } from '@/components/locale-switcher';
import { ThemeToggle } from '@/components/theme-toggle';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { UserMenuContent } from '@/components/user-menu-content';
import { dashboard } from '@/routes';
import type { SharedData } from '@/types/shared-data';

/** The common product bar. Navigation stays in the dedicated sidebar below it. */
export function AppTopNavigation() {
    const { auth } = usePage<SharedData>().props;

    return (
        <header className="app-top-bar sticky top-0 z-40 flex h-16 shrink-0 items-center px-4 text-white lg:px-6">
            <div className="flex min-w-0 flex-1 items-center gap-3">
                <Link href={dashboard()} prefetch className="flex min-w-0 items-center"><AppLogo /></Link>
            </div>
            <div className="flex shrink-0 items-center gap-1">
                <LocaleSwitcher />
                <ThemeToggle />
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <button type="button" aria-label="Open user menu" className="ml-1 rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60">
                            <Avatar className="size-9 border border-white/30">
                                <AvatarImage src={auth.user?.avatar} alt={auth.user?.name} />
                                <AvatarFallback className="bg-white/20 text-white">{auth.user?.name?.charAt(0).toUpperCase()}</AvatarFallback>
                            </Avatar>
                        </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" className="w-56"><UserMenuContent user={auth.user!} /></DropdownMenuContent>
                </DropdownMenu>
            </div>
        </header>
    );
}