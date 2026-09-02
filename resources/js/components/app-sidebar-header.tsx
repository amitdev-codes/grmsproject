import { usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { LocaleSwitcher } from '@/components/locale-switcher';
import { ThemeToggle } from '@/components/theme-toggle';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { SidebarTrigger, useSidebar } from '@/components/ui/sidebar';
import { UserMenuContent } from '@/components/user-menu-content';
import { useIsMobile } from '@/hooks/use-mobile';
import { cn } from '@/lib/utils';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';
import type { SharedData } from '@/types/shared-data';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    const { auth } = usePage<SharedData>().props;
    const [profileOpen, setProfileOpen] = useState(false);
    const isMobile = useIsMobile();
    const { state } = useSidebar();
    // Profile actions

    return (
        <header className="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border/50 px-6 transition-[width,height] ease-linear md:px-4">
            {/* Left side */}
            <div className="flex items-center gap-2">
                <SidebarTrigger className="-ml-1" />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>

            {/* Right side */}
            <div className="flex items-center gap-2">
                {/* Language Switch — English / Sesotho, flag toggle */}
                <LocaleSwitcher />

                {/* Icon-only Dark/Light Toggle */}
                <ThemeToggle />

                {/* Profile Dropdown */}
                <DropdownMenu open={profileOpen} onOpenChange={setProfileOpen}>
                    <DropdownMenuTrigger asChild>
                        <button
                            type="button"
                            className={cn(
                                'relative h-9 w-9 rounded-full border transition-all hover:border-primary/40 hover:bg-accent focus-visible:outline-none dark:border-neutral-700 dark:hover:border-neutral-600',
                                profileOpen &&
                                    'border-primary ring-2 ring-primary/30 ring-offset-2 ring-offset-background',
                            )}
                        >
                            <Avatar className="h-8 w-8">
                                <AvatarImage
                                    src={auth.user.avatar}
                                    alt={auth.user.name}
                                />
                                <AvatarFallback className="bg-primary text-primary-foreground">
                                    {auth.user.name.charAt(0).toUpperCase()}
                                </AvatarFallback>
                            </Avatar>
                        </button>
                    </DropdownMenuTrigger>

                    <DropdownMenuContent
                        className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                        align="end"
                        side={
                            isMobile
                                ? 'bottom'
                                : state === 'collapsed'
                                    ? 'left'
                                    : 'bottom'
                        }
                    >
                        <UserMenuContent user={auth.user} />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </header>
    );
}
