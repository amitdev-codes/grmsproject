import { DataTable } from '@/components/data-table/data-table';
import IndexLayout from '@/components/index-layout';
import { useTranslation } from '@/hooks/use-translation';
import type {
    DataTableFilterField,
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';
import { Tags } from 'lucide-react';
import { useGrievanceCategoryColumns } from './columns';
import type { GrievanceCategory } from './columns';
import { GrievanceCategoryViewContent } from './GrievanceCategoryViewContent';

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
    const { t } = useTranslation();
    const columns = useGrievanceCategoryColumns();
    const filterFields: DataTableFilterField[] = [
        {
            id: 'is_sensitive',
            title: t('Sensitivity'),
            options: [
                { label: t('Sensitive'), value: 't' },
                { label: t('Standard'), value: 'f' },
            ],
        },
        {
            id: 'is_active',
            title: t('Status'),
            options: [
                { label: t('Active'), value: 't' },
                { label: t('Inactive'), value: 'f' },
            ],
        },
    ];

    return (
        <IndexLayout
            title={t('Grievance Categories')}
            breadcrumbs={[{ label: t('Grievance Categories'), icon: Tags }]}
        >
            <div>
                <DataTable<GrievanceCategory, unknown>
                    columns={columns}
                    data={data}
                    meta={meta}
                    routes={routes}
                    filterFields={filterFields}
                    title={t('Grievance Categories')}
                    description={t('Manage all Grievance Categories in your application.')}
                    searchPlaceholder={t('Search by name, code or slug…')}
                    resourceLabel={t('New Category')}
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
