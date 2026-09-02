import { DataTable } from '@/components/data-table/data-table';
import IndexLayout from '@/components/index-layout';
import type {
    DataTableFilterField,
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';
import { MapPin } from 'lucide-react';
import { Columns } from './Columns';
import type { District } from './Columns';
import { DistrictViewContent } from './DistrictViewContent';

interface DistrictIndexProps {
    data: District[];
    meta: PaginationMeta;
}

const routes: DataTableRoutes = {
    index: 'districts.index',
    create: 'districts.create',
    edit: 'districts.edit',
    destroy: 'districts.destroy',
    bulkDestroy: 'districts.bulk-destroy',
    export: 'districts.export',
    import: 'districts.import',
};

export default function DistrictIndex({ data, meta }: DistrictIndexProps) {
    const filterFields: DataTableFilterField[] = [];

    return (
        <IndexLayout
            title="Districts"
            breadcrumbs={[{ label: 'Districts', icon: MapPin }]}
        >
            <div>
                <DataTable<District, unknown>
                    columns={Columns}
                    data={data}
                    meta={meta}
                    routes={routes}
                    filterFields={filterFields}
                    title="Districts"
                    description="Manage all districts in your application."
                    searchPlaceholder="Search by code or name…"
                    resourceLabel="New District"
                    defaultSort="name"
                    defaultOrder="asc"
                    getRowLabel={(row) => row.name}
                    viewContent={(row) => (
                        <DistrictViewContent district={row} />
                    )}
                />
            </div>
        </IndexLayout>
    );
}
