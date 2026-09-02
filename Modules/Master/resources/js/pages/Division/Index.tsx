import { DataTable } from '@/components/data-table/data-table';
import IndexLayout from '@/components/index-layout';
import type {
    DataTableFilterField,
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';
import { Building2 } from 'lucide-react';
import { Columns } from './Columns';
import type { Division } from './Columns';
import { DivisionViewContent } from './DivisionViewContent';

interface DivisionIndexProps {
    data: Division[];
    meta: PaginationMeta;
}

const routes: DataTableRoutes = {
    index: 'divisions.index',
    create: 'divisions.create',
    edit: 'divisions.edit',
    destroy: 'divisions.destroy',
    bulkDestroy: 'divisions.bulk-destroy',
    export: 'divisions.export',
    import: 'divisions.import',
};

export default function DivisionIndex({ data, meta }: DivisionIndexProps) {
    const filterFields: DataTableFilterField[] = [];

    return (
        <IndexLayout
            title="Divisions"
            breadcrumbs={[{ label: 'Divisions', icon: Building2 }]}
        >
            <div>
                <DataTable<Division, unknown>
                    columns={Columns}
                    data={data}
                    meta={meta}
                    routes={routes}
                    filterFields={filterFields}
                    title="Divisions"
                    description="Manage all divisions in your application."
                    searchPlaceholder="Search by code or name…"
                    resourceLabel="New Division"
                    defaultSort="name"
                    defaultOrder="asc"
                    getRowLabel={(row) => row.name}
                    viewContent={(row) => (
                        <DivisionViewContent division={row} />
                    )}
                />
            </div>
        </IndexLayout>
    );
}
