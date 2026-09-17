import { DataTable } from '@/components/data-table/data-table';
import IndexLayout from '@/components/index-layout';
import type {
    DataTableFilterField,
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';
import { Building2 } from 'lucide-react';
import { Columns } from './Columns';
import type { ServiceProvider } from './Columns';
import { ServiceProviderViewContent } from './ServiceProviderViewContent';

interface ServiceProviderIndexProps {
    data: ServiceProvider[];
    meta: PaginationMeta;
}

const routes: DataTableRoutes = {
    index: 'service-providers.index',
    create: 'service-providers.create',
    edit: 'service-providers.edit',
    destroy: 'service-providers.destroy',
    bulkDestroy: 'service-providers.bulk-destroy',
    export: 'service-providers.export',
    import: 'service-providers.import',
};

export default function ServiceProviderIndex({ data, meta }: ServiceProviderIndexProps) {
    const filterFields: DataTableFilterField[] = [
        {
            id: 'provider_type',
            title: 'Type',
            options: [
                { label: 'Contractor', value: 'contractor' },
                { label: 'Consultant', value: 'consultant' },
                { label: 'Other', value: 'other' },
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
            title="Service Providers"
            breadcrumbs={[{ label: 'Service Providers', icon: Building2 }]}
        >
            <div>
                <DataTable<ServiceProvider, unknown>
                    columns={Columns}
                    data={data}
                    meta={meta}
                    routes={routes}
                    filterFields={filterFields}
                    title="Service Providers"
                    description="Manage all service providers in your application."
                    searchPlaceholder="Search by code, name or contact…"
                    resourceLabel="New Service Provider"
                    defaultSort="name"
                    defaultOrder="asc"
                    getRowLabel={(row) => row.name}
                    viewContent={(row) => (
                        <ServiceProviderViewContent provider={row} />
                    )}
                />
            </div>
        </IndexLayout>
    );
}