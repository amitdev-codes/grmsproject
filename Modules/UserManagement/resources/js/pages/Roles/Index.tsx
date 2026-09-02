import { DataTable } from '@/components/data-table/data-table';
import type {
    DataTableFilterField,
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';
import { ShieldCheck } from 'lucide-react';
import IndexLayout from '../../../../../../resources/js/components/index-layout';
import { columns } from './columns';
import type { Role } from './columns';
import { RoleViewContent } from './RoleViewContent';

interface RolesIndexProps {
    data: Role[];
    meta: PaginationMeta;
}

const routes: DataTableRoutes = {
    index: 'roles.index',
    create: 'roles.create',
    edit: 'roles.edit',
    destroy: 'roles.destroy',
    bulkDestroy: 'roles.bulk-destroy',
    export: 'roles.export',
    import: 'roles.import',
};

export default function RolesIndex({ data, meta }: RolesIndexProps) {
    const filterFields: DataTableFilterField[] = [
        {
            id: 'status',
            title: 'Status',
            options: [
                { label: 'Active', value: 't' },
                { label: 'Inactive', value: 'f' },
            ],
        },
    ];

    return (
        <IndexLayout
            title="Roles"
            breadcrumbs={[{ label: 'Roles', icon: ShieldCheck }]}
        >
            <div>
                <DataTable<Role, unknown>
                    columns={columns}
                    data={data}
                    meta={meta}
                    routes={routes}
                    filterFields={filterFields}
                    title="Roles"
                    description="Manage all roles in your application."
                    searchPlaceholder="Search by name or code…"
                    resourceLabel="New Role"
                    defaultSort="created_at"
                    defaultOrder="desc"
                    getRowLabel={(row) => row.name}
                    viewContent={(row) => <RoleViewContent role={row} />}
                />
            </div>
        </IndexLayout>
    );
}
