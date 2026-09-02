import { DataTable } from '@/components/data-table/data-table';
import type {
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';
import { Users } from 'lucide-react';
import { useState } from 'react';
import PermissionForm from '@modules/UserManagement/pages/Permissions/PermissionForm';
import IndexLayout from '../../../../../../resources/js/components/index-layout';
import { columns } from './columns';
import type { Permission } from './columns';
import { PermissionViewContent } from './PermissionViewContent';

interface PermissionsIndexProps {
    data: Permission[];
    meta: PaginationMeta;
    roles: string[];
}

const routes: DataTableRoutes = {
    index: 'permissions.index',
    create: 'permissions.create',
    edit: 'permissions.edit',
    destroy: 'permissions.destroy',
    bulkDestroy: 'permissions.bulk-destroy',
    export: 'permissions.export',
    import: 'permissions.import',
    // routes.view intentionally omitted — "View" opens a modal instead (see viewContent below)
};

export default function PermissionsIndex({ data, meta }: PermissionsIndexProps) {
    const [open, setOpen] = useState(false);
    const [editing, setEditing] = useState<Permission | null>(null);

    const handleCreate = () => {
        setEditing(null);
        setOpen(true);
    };

    const handleEdit = (permission: Permission) => {
        setEditing(permission);
        setOpen(true);
    };

    return (
        <IndexLayout
            title="Permissions"
            breadcrumbs={[{ label: 'Permissions', icon: Users }]}
        >
            <div>
                <DataTable<Permission, unknown>
                    columns={columns}
                    data={data}
                    meta={meta}
                    routes={routes}

                    title="Permissions"
                    description="Manage all permissions in your application."
                    searchPlaceholder="Search by name…"
                    resourceLabel="New Permission"
                    defaultSort="created_at"
                    defaultOrder="desc"
                    getRowLabel={(row) => row.name}
                    viewContent={(row) => (
                        <PermissionViewContent permission={row} />
                    )}
                    onCreate={handleCreate}
                    onEdit={handleEdit}
                />
                <PermissionForm
                    open={open}
                    onOpenChange={setOpen}
                    permission={editing}
                />
            </div>
        </IndexLayout>
    );
}
