import { DataTable } from '@/components/data-table/data-table';
import IndexLayout from '@/components/index-layout';
import type {
    DataTableFilterField,
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';
import { Languages } from 'lucide-react';
import { columns } from './columns';
import type { ApplicationTranslation } from './columns';

interface ApplicationTranslationsIndexProps {
    data: ApplicationTranslation[];
    meta: PaginationMeta;
}

const routes: DataTableRoutes = {
    index: 'settings.translations.index',
    create: 'settings.translations.create',
    edit: 'settings.translations.edit',
    destroy: 'settings.translations.destroy',
};

const filterFields: DataTableFilterField[] = [
    {
        id: 'sesotho_status',
        title: 'Sesotho translation',
        options: [
            { label: 'Translated', value: 'translated' },
            { label: 'Missing', value: 'missing' },
        ],
    },
];

export default function Index({
    data,
    meta,
}: ApplicationTranslationsIndexProps) {
    return (
        <IndexLayout
            title="Language translations"
            breadcrumbs={[{ label: 'Language translations', icon: Languages }]}
        >
            <DataTable<ApplicationTranslation, unknown>
                columns={columns}
                data={data}
                meta={meta}
                routes={routes}
                filterFields={filterFields}
                title="Language translations"
                description="Manage English and Sesotho text used throughout the application."
                searchPlaceholder="Search key or translation text…"
                resourceLabel="New Translation"
                defaultSort="translation_key"
                defaultOrder="asc"
                getRowLabel={(row) => row.translation_key}
            />
        </IndexLayout>
    );
}
