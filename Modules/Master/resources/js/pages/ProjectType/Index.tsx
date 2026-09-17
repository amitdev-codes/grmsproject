import { DataTable } from '@/components/data-table/data-table';
import IndexLayout from '@/components/index-layout';
import type {
    DataTableFilterField,
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';
import { FolderKanban } from 'lucide-react';
import { Columns } from './Columns';
import type { ProjectType } from './Columns';
import { ProjectTypeViewContent } from './ProjectTypeViewContent';

interface ProjectTypeIndexProps {
    data: ProjectType[];
    meta: PaginationMeta;
}

const routes: DataTableRoutes = {
    index: 'project-types.index',
    create: 'project-types.create',
    edit: 'project-types.edit',
    destroy: 'project-types.destroy',
    bulkDestroy: 'project-types.bulk-destroy',
    export: 'project-types.export',
    import: 'project-types.import',
};

export default function ProjectTypeIndex({ data, meta }: ProjectTypeIndexProps) {
    const filterFields: DataTableFilterField[] = [
        {
            id: 'is_active',
            title: 'Status',
            options: [
                { label: 'Active', value: 't' },
                { label: 'Inactive', value: 'f' },
            ],
        },
    ];

    return (
        <IndexLayout
            title="Project Types"
            breadcrumbs={[{ label: 'Project Types', icon: FolderKanban }]}
        >
            <div>
                <DataTable<ProjectType, unknown>
                    columns={Columns}
                    data={data}
                    meta={meta}
                    routes={routes}
                    filterFields={filterFields}
                    title="Project Types"
                    description="Manage all project types in your application."
                    searchPlaceholder="Search by code or name…"
                    resourceLabel="New Project Type"
                    defaultSort="sort_order"
                    defaultOrder="asc"
                    getRowLabel={(row) => row.name}
                    viewContent={(row) => (
                        <ProjectTypeViewContent projectType={row} />
                    )}
                />
            </div>
        </IndexLayout>
    );
}