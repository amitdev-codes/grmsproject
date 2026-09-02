import { DataTableColumnHeader } from '@/components/data-table/data-table-column-header';
import { Badge } from '@/components/ui/badge';
import type { ColumnDef } from '@tanstack/react-table';
export interface Role {
    id: number;
    code: string;
    name: string;
    name_st: string;
    status: boolean;
    permissions: { id: number; name: string }[];
    users_count: number;
    created_at: string;
}
export const columns: ColumnDef<Role>[] = [
    {
        accessorKey: 'code',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Code" />
        ),
    },
    {
        accessorKey: 'name',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Name" />
        ),
    },
    {
        accessorKey: 'name_st',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Name (ST)" />
        ),
    },
    {
        accessorKey: 'permissions',
        header: 'Permissions',
        cell: ({ row }) => {
            const perms = row.original.permissions ?? [];
            const shown = perms.slice(0, 3);

            return (
                <div className="flex flex-wrap gap-1">
                    {shown.map((p) => (
                        <Badge
                            key={p.id}
                            variant="secondary"
                            className="font-normal"
                        >
                            {p.name}
                        </Badge>
                    ))}
                    {perms.length > shown.length && (
                        <Badge variant="outline" className="font-normal">
                            +{perms.length - shown.length}
                        </Badge>
                    )}
                </div>
            );
        },
        enableSorting: false,
    },
    {
        accessorKey: 'users_count',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Users" />
        ),
        cell: ({ row }) => row.original.users_count ?? 0,
    },
    {
        accessorKey: 'status',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Status" />
        ),
        cell: ({ row }) => (
            <Badge variant={row.original.status ? 'default' : 'secondary'}>
                {row.original.status ? 'Active' : 'Inactive'}
            </Badge>
        ),
    },
];
