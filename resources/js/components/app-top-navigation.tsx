import { Link, router, usePage } from '@inertiajs/react';
import { Bell, Check, Circle } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { LocaleSwitcher } from '@/components/locale-switcher';
import { ThemeToggle } from '@/components/theme-toggle';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { UserMenuContent } from '@/components/user-menu-content';
import { dashboard } from '@/routes';
import type { SharedData } from '@/types/shared-data';

function CountBadge({ count }: { count: number }) {
    if (count < 1) {
        return null;
    }

    return (
        <span className="absolute -top-1 -right-1 flex min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] leading-4 text-white">
            {count > 99 ? '99+' : count}
        </span>
    );
}

/** The common product bar. Navigation stays in the dedicated sidebar below it. */
export function AppTopNavigation() {
    const { auth, notifications } = usePage<SharedData>().props;

    return (
        <header className="app-top-bar sticky top-0 z-40 flex h-16 shrink-0 items-center px-4 text-white lg:px-6">
            <div className="flex min-w-0 flex-1 items-center gap-3">
                <Link
                    href={dashboard()}
                    prefetch
                    className="flex min-w-0 items-center"
                >
                    <AppLogo />
                </Link>
            </div>
            <div className="flex shrink-0 items-center gap-1">
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <button
                            type="button"
                            aria-label={`Notifications${notifications.count ? ` (${notifications.count} unread)` : ''}`}
                            className="relative inline-flex h-9 w-9 items-center justify-center rounded-md transition-colors hover:bg-white/15 focus-visible:ring-2 focus-visible:ring-white/60 focus-visible:outline-none"
                        >
                            <Bell className="size-5" />
                            <CountBadge count={notifications.count} />
                        </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        align="end"
                        className="w-[min(24rem,calc(100vw-2rem))] p-0"
                    >
                        <DropdownMenuLabel className="flex items-center justify-between px-4 py-3">
                            <span>Notifications</span>
                            <span className="text-xs font-normal text-muted-foreground">
                                {notifications.count} unread
                            </span>
                        </DropdownMenuLabel>
                        <div className="max-h-96 overflow-y-auto border-y">
                            {notifications.latest.length ? (
                                notifications.latest.map((notification) => (
                                    <div
                                        key={notification.id}
                                        className={`flex items-start gap-3 border-b px-4 py-3 last:border-b-0 ${
                                            notification.read_at
                                                ? 'bg-background'
                                                : 'bg-primary/5'
                                        }`}
                                    >
                                        {notification.read_at ? (
                                            <Check className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                                        ) : (
                                            <Circle className="mt-1 size-3 shrink-0 fill-primary text-primary" />
                                        )}
                                        <Link
                                            href={
                                                notification.action_url ??
                                                '/notifications'
                                            }
                                            className="min-w-0 flex-1"
                                        >
                                            <span className="block truncate text-sm font-medium">
                                                {notification.title}
                                            </span>
                                            {notification.message && (
                                                <span className="mt-1 line-clamp-2 block text-xs text-muted-foreground">
                                                    {notification.message}
                                                </span>
                                            )}
                                            {notification.created_at && (
                                                <span className="mt-1 block text-xs text-muted-foreground">
                                                    {notification.created_at}
                                                </span>
                                            )}
                                        </Link>
                                        {!notification.read_at && (
                                            <button
                                                type="button"
                                                className="shrink-0 text-xs text-primary hover:underline"
                                                onClick={() =>
                                                    router.post(
                                                        `/notifications/${notification.id}/read`,
                                                        {},
                                                        {
                                                            preserveScroll: true,
                                                        },
                                                    )
                                                }
                                            >
                                                Mark read
                                            </button>
                                        )}
                                    </div>
                                ))
                            ) : (
                                <p className="px-4 py-8 text-center text-sm text-muted-foreground">
                                    You have no notifications.
                                </p>
                            )}
                        </div>
                        <Link
                            href="/notifications"
                            className="block px-4 py-3 text-center text-sm font-medium text-primary hover:underline"
                        >
                            View all notifications
                        </Link>
                    </DropdownMenuContent>
                </DropdownMenu>
                <LocaleSwitcher />
                <ThemeToggle />
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <button
                            type="button"
                            aria-label="Open user menu"
                            className="ml-1 rounded-full focus-visible:ring-2 focus-visible:ring-white/60 focus-visible:outline-none"
                        >
                            <Avatar className="size-9 border border-white/30">
                                <AvatarImage
                                    src={auth.user?.avatar}
                                    alt={auth.user?.name}
                                />
                                <AvatarFallback className="bg-white/20 text-white">
                                    {auth.user?.name?.charAt(0).toUpperCase()}
                                </AvatarFallback>
                            </Avatar>
                        </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" className="w-56">
                        <UserMenuContent user={auth.user!} />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </header>
    );
}
