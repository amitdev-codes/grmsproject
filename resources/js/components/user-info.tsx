import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/hooks/use-initials';
import { type User } from '@/types';

/**
 * Pulls a display label out of whatever shape `user.role` happens to be —
 * a plain string ("Admin"), a Spatie-style object ({ name: "Admin" }),
 * or an array of role objects/strings (shows the first one).
 * Exported so other components (e.g. sidebar-user-profile.tsx) reuse the
 * exact same parsing logic instead of duplicating it.
 */
export function getRoleLabel(role: unknown): string | null {
    if (!role) return null;
    if (typeof role === 'string') return role;
    if (Array.isArray(role)) return getRoleLabel(role[0]);
    if (typeof role === 'object' && 'name' in role) {
        return (role as { name: string }).name;
    }
    return null;
}

export function UserInfo({
    user,
    showEmail = false,
}: {
    user: User;
    showEmail?: boolean;
}) {
    const getInitials = useInitials();
    const roleLabel = getRoleLabel((user as { role?: unknown }).role);

    return (
        <>
            <Avatar className="h-8 w-8 overflow-hidden rounded-full">
                <AvatarImage src={user.avatar} alt={user.name} />
                <AvatarFallback className="rounded-lg bg-primary text-primary-foreground">
                    {getInitials(user.name)}
                </AvatarFallback>
            </Avatar>
            <div className="grid flex-1 text-left text-sm leading-tight">
                <span className="truncate font-medium">{user.name}</span>
                {roleLabel && (
                    <span className="truncate text-xs font-medium text-primary">
                        {roleLabel}
                    </span>
                )}
                {showEmail && (
                    <span className="truncate text-xs text-muted-foreground">
                        {user.email}
                    </span>
                )}
            </div>
        </>
    );
}
