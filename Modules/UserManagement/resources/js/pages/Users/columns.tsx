import { DataTableColumnHeader } from '@/components/data-table/data-table-column-header';
import { Badge } from '@/components/ui/badge';
import { UserAvatar } from '@/components/user-avatar';
import type { ColumnDef } from '@tanstack/react-table';

export interface Role {
    id: number;
    name: string;
}

export interface User {
    id: number;
    name: string;
    username: string;
    email: string;
    phone: string | null;
    bio: string | null;
    status: boolean | number | string; // backend should cast to real boolean, but this stays defensive
    district_id: number | null;
    division_id: number | null;
    section_id: number | null;
    roles: Role[];
    role_names: string;
    avatar_url: string | null;
    created_at: string;
}

const roleVariant = (role: string): 'default' | 'secondary' | 'outline' =>
    role === 'admin' ? 'default' : role === 'editor' ? 'secondary' : 'outline';

// Handles true/false, 1/0, and "1"/"0" (e.g. if the model isn't cast to boolean
// server-side, raw DB values often arrive as strings — and "0" is truthy in JS).
export const isActiveStatus = (status: unknown): boolean =>
    status === true || status === 1 || status === '1';

export const columns: ColumnDef<User>[] = [
    {
        id: 'avatar',
        header: '',
        cell: ({ row }) => (
            <UserAvatar
                name={row.original.username}
                avatarUrl={row.original.avatar_url}
            />
        ),
        enableSorting: false,
        enableHiding: false,
    },
    {
        accessorKey: 'username',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Username" />
        ),
    },
    {
        accessorKey: 'email',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Email" />
        ),
    },
    {
        accessorKey: 'phone',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Phone" />
        ),
        cell: ({ row }) =>
            row.original.phone ?? (
                <span className="text-muted-foreground">—</span>
            ),
    },
    {
        id: 'role',
        header: 'Role',
        cell: ({ row }) => (
            <div className="flex flex-wrap gap-1">
                {row.original.roles.length ? (
                    row.original.roles.map((role) => (
                        <Badge
                            key={role.id}
                            variant={roleVariant(role.name)}
                            className="capitalize"
                        >
                            {role.name}
                        </Badge>
                    ))
                ) : (
                    <span className="text-muted-foreground">—</span>
                )}
            </div>
        ),
        enableSorting: false,
    },
    {
        accessorKey: 'status',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Status" />
        ),
        cell: ({ row }) => {
            const active = isActiveStatus(row.original.status);

            return (
                <Badge variant={active ? 'default' : 'secondary'}>
                    {active ? 'Active' : 'Inactive'}
                </Badge>
            );
        },
    },
    {
        accessorKey: 'created_at',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Created" />
        ),
        cell: ({ row }) =>
            new Date(row.original.created_at).toLocaleDateString(),
    },
];
