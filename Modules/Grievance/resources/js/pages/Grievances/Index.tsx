import { DataTable } from '@/components/data-table/data-table';
import type {
    DataTableFilterField,
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';
import { GRIEVANCE_STATUS_OPTIONS } from '@/types/grievance-status';
import { FileWarning } from 'lucide-react';
import IndexLayout from '../../../../../../resources/js/components/index-layout';
import { columns } from './columns';
import type {
    Grievance,
    Category,
    District,
    Division,
    Section,
} from './columns';
import { GrievanceViewContent } from './GrievanceViewContent';

interface GrievanceIndexProps {
    data: Grievance[];
    meta: PaginationMeta;
    categories: Category[];
    districts: District[];
    divisions: Division[];
    sections: Section[];
}

const routes: DataTableRoutes = {
    index: 'grievances.index',
    create: 'grievances.create',
    edit: 'grievances.edit',
    destroy: 'grievances.destroy',
    bulkDestroy: 'grievances.bulk-destroy',
    export: 'grievances.export',
};

const PRIORITY_OPTIONS = [
    { label: 'Low', value: 'low' },
    { label: 'Normal', value: 'normal' },
    { label: 'High', value: 'high' },
];

export default function GrievanceIndex({
                                           data,
                                           meta,
                                           categories,
                                           districts,
                                           divisions,
                                           sections,
                                       }: GrievanceIndexProps) {
    const filterFields: DataTableFilterField[] = [
        { id: 'status', title: 'Status', options: GRIEVANCE_STATUS_OPTIONS },
        { id: 'priority', title: 'Priority', options: PRIORITY_OPTIONS },
        {
            id: 'category_id',
            title: 'Category',
            options: categories.map((c) => ({
                label: c.name_en,
                value: String(c.id),
            })),
        },
        {
            id: 'district_id',
            title: 'District',
            options: districts.map((d) => ({
                label: d.name,
                value: String(d.id),
            })),
        },
        {
            id: 'division_id',
            title: 'Division',
            options: divisions.map((d) => ({
                label: d.name,
                value: String(d.id),
            })),
        },
        {
            id: 'section_id',
            title: 'Section',
            options: sections.map((s) => ({
                label: s.name,
                value: String(s.id),
            })),
        },
    ];

    return (
        <IndexLayout
            title="Grievances"
            breadcrumbs={[{ label: 'Grievances', icon: FileWarning }]}
        >
            <div>
                <DataTable<Grievance, unknown>
                    columns={columns}
                    data={data}
                    meta={meta}
                    routes={routes}
                    filterFields={filterFields}
                    title="Grievances"
                    searchPlaceholder="Search by reference number, complainant…"
                    resourceLabel="New Grievance"
                    defaultSort="created_at"
                    defaultOrder="desc"
                    getRowLabel={(row) => row.reference_no}
                    viewContent={(row) => (
                        <GrievanceViewContent grievance={row} />
                    )}
                />
            </div>
        </IndexLayout>
    );
}
