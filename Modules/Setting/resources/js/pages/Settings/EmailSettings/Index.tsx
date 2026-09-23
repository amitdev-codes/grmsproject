import { DataTable } from '@/components/data-table/data-table';
import IndexLayout from '@/components/index-layout';
import type { DataTableRoutes, PaginationMeta } from '@/types/data-table';
import { Mail } from 'lucide-react';
import { Columns, type EmailSetting } from './Columns';

interface Props {
    data: EmailSetting[];
    meta: PaginationMeta;
}

const routes: DataTableRoutes = {
    index: 'settings.email.index',
    create: 'settings.email.create',
    edit: 'settings.email.edit',
    destroy: 'settings.email.destroy',
};

export default function Index({ data, meta }: Props) {
    return (
        <IndexLayout
            title="Email Settings"
            breadcrumbs={[{ label: 'Email Settings', icon: Mail }]}
        >
            <DataTable
                columns={Columns}
                data={data}
                meta={meta}
                routes={routes}
                title="Email Settings"
                description="Manage SMTP and transactional email providers."
                resourceLabel="Email Setting"
                searchPlaceholder="Search email settings…"
                getRowLabel={(row) => row.name}
            />
        </IndexLayout>
    );
}
