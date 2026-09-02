import { DataTable } from '@/components/data-table/data-table';
import IndexLayout from '@/components/index-layout';
import type {
    DataTableFilterField,
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';
import { Tags } from 'lucide-react';
import { columns } from './columns';
import type { GrievanceCategory } from './columns';
import  {GrievanceCategoryViewContent}  from './GrievanceCategoryViewContent';

interface GrievanceCategoryIndexProps {
    data: GrievanceCategory[];
    meta: PaginationMeta;
}

const routes: DataTableRoutes = {
    index: 'grievance-categories.index',
    create: 'grievance-categories.create',
    edit: 'grievance-categories.edit',
    destroy: 'grievance-categories.destroy',
    bulkDestroy: 'grievance-categories.bulk-destroy',
    export: 'grievance-categories.export',
    import: 'grievance-categories.import',
};

export default function GrievanceCategoryIndex({
    data,
    meta,
}: GrievanceCategoryIndexProps) {
    const filterFields: DataTableFilterField[] = [
        {
            id: 'is_sensitive',
            title: 'Sensitivity',
            options: [
                { label: 'Sensitive', value: 't' },
                { label: 'Standard', value: 'f' },
            ],
        },
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
            title="Grievance Categories"
            breadcrumbs={[{ label: 'Grievance Categories', icon: Tags }]}
        >
            <div>
                <DataTable<GrievanceCategory, unknown>
                    columns={columns}
                    data={data}
                    meta={meta}
                    routes={routes}
                    filterFields={filterFields}
                    title="Grievance Categories"
                    description="Manage all Grievance Categories in your application."
                    searchPlaceholder="Search by name, code or slug…"
                    resourceLabel="New Category"
                    defaultSort="sort_order"
                    defaultOrder="asc"
                    getRowLabel={(row) => row.name_en || row.code}
                    viewContent={(row) => (
                        <GrievanceCategoryViewContent grievanceCategory={row} />
                    )}
                />
            </div>
        </IndexLayout>
    );
}
