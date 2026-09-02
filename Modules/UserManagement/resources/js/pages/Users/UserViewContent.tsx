import { Badge } from '@/components/ui/badge';
import { DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { UserAvatar } from '@/components/user-avatar';
import type { User } from './columns';

interface UserViewContentProps {
  user: User;
}

function Field({ label, value }: { label: string; value: React.ReactNode }) {
  return (
    <div className="flex flex-col gap-0.5">
      <span className="text-xs font-medium text-muted-foreground">{label}</span>
      <span className="text-sm">{value}</span>
    </div>
  );
}

export function UserViewContent({ user }: UserViewContentProps) {
  return (
    <>
      <DialogHeader>
        <div className="flex items-center gap-3">
          <UserAvatar name={user.username} avatarUrl={user.avatar_url} size={48} />
          <div>
            <DialogTitle>{user.username}</DialogTitle>
            <DialogDescription>{user.email}</DialogDescription>
          </div>
        </div>
      </DialogHeader>

      <div className="grid grid-cols-2 gap-4 pt-2">
        <Field label="Phone" value={user.phone ?? '—'} />
        <Field
          label="Status"
          value={<Badge variant={user.status === 'active' ? 'default' : 'secondary'}>{user.status}</Badge>}
        />
        <Field
          label="Role"
          value={
            <div className="flex flex-wrap gap-1">
              {user.roles.length ? (
                user.roles.map((role) => (
                  <Badge key={role.id} variant="outline" className="capitalize">
                    {role.name}
                  </Badge>
                ))
              ) : (
                '—'
              )}
            </div>
          }
        />
        <Field label="Created" value={new Date(user.created_at).toLocaleString()} />
      </div>
    </>
  );
}
