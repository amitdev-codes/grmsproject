import { DataTable } from '@/components/data-table/data-table';
import type {
    DataTableFilterField,
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';
import { Users } from 'lucide-react';
import IndexLayout from '../../../../../../resources/js/components/index-layout';
import { columns } from './columns';
import type { User } from './columns';
import { UserViewContent } from './UserViewContent';

interface UsersIndexProps {
    data: User[];
    meta: PaginationMeta;
    /** All Spatie role names that exist in the DB — used to build the role filter dropdown */
    roles: string[];
}

const routes: DataTableRoutes = {
    index: 'users.index',
    create: 'users.create',
    edit: 'users.edit',
    destroy: 'users.destroy',
    bulkDestroy: 'users.bulk-destroy',
    export: 'users.export',
    import: 'users.import',
    // routes.view intentionally omitted — "View" opens a modal instead (see viewContent below)
};

export default function UsersIndex({ data, meta, roles }: UsersIndexProps) {
    const filterFields: DataTableFilterField[] = [
        { id: 'email', title: 'Email', type: 'text' },
        {
            id: 'role',
            title: 'Role',
            // Pulled straight from Spatie's roles table (passed in from UserController::index) —
            // no hardcoded options here.
            options: roles.map((role) => ({
                label: role.charAt(0).toUpperCase() + role.slice(1),
                value: role,
            })),
        },
        {
            id: 'status',
            title: 'Status',
            // status is a boolean column; '1'/'0' match what applyFilter() casts on the backend
            options: [
                { label: 'Active', value: 't' },
                { label: 'Inactive', value: 'f' },
            ],
        },
    ];

    return (
        <IndexLayout
            title="Users"
            breadcrumbs={[{ label: 'Users', icon: Users }]}
        >
            <div>
                <DataTable<User, unknown>
                    columns={columns}
                    data={data}
                    meta={meta}
                    routes={routes}
                    filterFields={filterFields}
                    title="Users"
                    searchPlaceholder="Search by username or email…"
                    resourceLabel="New User"
                    defaultSort="created_at"
                    defaultOrder="desc"
                    getRowLabel={(row) => row.username}
                    viewContent={(row) => <UserViewContent user={row} />}
                />
            </div>
        </IndexLayout>
    );
}
