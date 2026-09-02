import { Badge } from '@/components/ui/badge';
import {
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';
import type { Role } from './columns';

interface RoleViewContentProps {
    role: Role;
}

function Field({ label, value }: { label: string; value: React.ReactNode }) {
    return (
        <div className="flex flex-col gap-0.5">
            <span className="text-xs font-medium text-muted-foreground">
                {label}
            </span>
            <span className="text-sm">{value}</span>
        </div>
    );
}

export function RoleViewContent({ role }: RoleViewContentProps) {
    return (
        <>
            <DialogHeader>
                <div className="flex items-center gap-3">
                    <div>
                        <DialogTitle>{role.name}</DialogTitle>
                        <DialogDescription>{role.name_st}</DialogDescription>
                    </div>
                </div>
            </DialogHeader>
            <div className="grid grid-cols-2 gap-4 pt-2">
                <Field
                    label="Status"
                    value={
                        <Badge variant={role.status ? 'default' : 'secondary'}>
                            {role.status ? 'Active' : 'Inactive'}
                        </Badge>
                    }
                />
                <Field label="Users" value={role.users_count ?? 0} />
                <Field
                    label="Permissions"
                    value={
                        <div className="flex flex-wrap gap-1">
                            {role.permissions?.length
                                ? role.permissions.map((permission) => (
                                      <Badge
                                          key={permission.id}
                                          variant="outline"
                                          className="capitalize"
                                      >
                                          {permission.name}
                                      </Badge>
                                  ))
                                : '—'}
                        </div>
                    }
                />
                <Field label="Code" value={role.code} />
                <Field
                    label="Created"
                    value={new Date(role.created_at).toLocaleString()}
                />
            </div>
        </>
    );
}
