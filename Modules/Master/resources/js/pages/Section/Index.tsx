import { DataTable } from '@/components/data-table/data-table';
import IndexLayout from '@/components/index-layout';
import type {
    DataTableFilterField,
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';
import { Layers } from 'lucide-react';
import { Columns } from './Columns';
import type { Section } from './Columns';
import { SectionViewContent } from './SectionViewContent';

interface SectionIndexProps {
    data: Section[];
    meta: PaginationMeta;
    divisions: { id: number; name: string }[];
}

const routes: DataTableRoutes = {
    index: 'sections.index',
    create: 'sections.create',
    edit: 'sections.edit',
    destroy: 'sections.destroy',
    bulkDestroy: 'sections.bulk-destroy',
    export: 'sections.export',
    import: 'sections.import',
};

export default function SectionIndex({
    data,
    meta,
    divisions=[],
}: SectionIndexProps) {
    const filterFields: DataTableFilterField[] = [
        {
            id: 'division_id',
            title: 'Division',
            options: divisions.map((d) => ({
                label: d.name,
                value: String(d.id),
            })),
        },
    ];

    return (
        <IndexLayout
            title="Sections"
            breadcrumbs={[{ label: 'Sections', icon: Layers }]}
        >
            <div>
                <DataTable<Section, unknown>
                    columns={Columns}
                    data={data}
                    meta={meta}
                    routes={routes}
                    filterFields={filterFields}
                    title="Sections"
                    description="Manage all sections in your application."
                    searchPlaceholder="Search by code or name…"
                    resourceLabel="New Section"
                    defaultSort="name"
                    defaultOrder="asc"
                    getRowLabel={(row) => row.name}
                    viewContent={(row) => <SectionViewContent section={row} />}
                />
            </div>
        </IndexLayout>
    );
}
