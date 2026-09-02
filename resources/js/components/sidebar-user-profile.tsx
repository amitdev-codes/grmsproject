import { usePage } from '@inertiajs/react';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/hooks/use-initials';
import type { SharedData } from '@/types/shared-data';

export function SidebarUserProfile() {
    const { auth } = usePage<SharedData>().props;
    const getInitials = useInitials();
    const roleLabel = auth.user.role_names;

    return (
        <div className="px-4 py-3 group-data-[collapsible=icon]:hidden">
            <div className="flex items-center gap-3">
                <div className="relative shrink-0">
                    <Avatar className="h-9 w-9 rounded-full ring-2 ring-sidebar ring-offset-0">
                        <AvatarImage src={auth.user.avatar} alt={auth.user.name} />
                        <AvatarFallback className="rounded-full bg-primary text-primary-foreground">
                            {getInitials(auth.user.name)}
                        </AvatarFallback>
                    </Avatar>
                    {/* online status dot, like Blade's avatar-online */}
                    <span className="absolute right-0 bottom-0 block size-2.5 rounded-full border-2 border-sidebar bg-(--success)" />
                </div>
                <div className="min-w-0 flex-1 leading-tight">
                    <p className="truncate text-sm font-bold">{auth.user.name}</p>
                    <>{roleLabel && (
                        <p className="truncate text-xs font-semibold text-muted-foreground/70">
                            {roleLabel}
                        </p>
                    )}</>
                </div>
            </div>
        </div>
    );
}
