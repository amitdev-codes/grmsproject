import { DataTable } from '@/components/data-table/data-table';
import IndexLayout from '@/components/index-layout';
import type {
    DataTableFilterField,
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';
import { FolderKanban } from 'lucide-react';
import { Columns } from './Columns';
import type { Project } from './Columns';
import { ProjectViewContent } from './ProjectViewContent';

interface ProjectIndexProps {
    data: Project[];
    meta: PaginationMeta;
}

const routes: DataTableRoutes = {
    index: 'projects.index',
    create: 'projects.create',
    edit: 'projects.edit',
    destroy: 'projects.destroy',
    bulkDestroy: 'projects.bulk-destroy',
    export: 'projects.export',
    import: 'projects.import',
};

export default function ProjectIndex({ data, meta }: ProjectIndexProps) {
    const filterFields: DataTableFilterField[] = [
        {
            id: 'status',
            title: 'Status',
            options: [
                { label: 'Planned', value: 'planned' },
                { label: 'Current', value: 'current' },
                { label: 'Completed', value: 'completed' },
                { label: 'Archived', value: 'archived' },
            ],
        },
    ];

    return (
        <IndexLayout
            title="Projects"
            breadcrumbs={[{ label: 'Projects', icon: FolderKanban }]}
        >
            <div>
                <DataTable<Project, unknown>
                    columns={Columns}
                    data={data}
                    meta={meta}
                    routes={routes}
                    filterFields={filterFields}
                    title="Projects"
                    description="Manage all projects in your application."
                    searchPlaceholder="Search by code or title…"
                    resourceLabel="New Project"
                    defaultSort="created_at"
                    defaultOrder="desc"
                    getRowLabel={(row) => row.title}
                    viewContent={(row) => (
                        <ProjectViewContent project={row} />
                    )}
                />
            </div>
        </IndexLayout>
    );
}