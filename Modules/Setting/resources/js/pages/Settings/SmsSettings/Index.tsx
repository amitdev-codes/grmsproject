import { DataTable } from '@/components/data-table/data-table';
import IndexLayout from '@/components/index-layout';
import type { DataTableRoutes, PaginationMeta } from '@/types/data-table';
import { MessageSquareText } from 'lucide-react';
import { Columns, type SmsSetting } from './Columns';

interface Props {
    data: SmsSetting[];
    meta: PaginationMeta;
}
const routes: DataTableRoutes = {
    index: 'settings.sms.index',
    create: 'settings.sms.create',
    edit: 'settings.sms.edit',
    destroy: 'settings.sms.destroy',
};

export default function Index({ data, meta }: Props) {
    return (
        <IndexLayout
            title="SMS Settings"
            breadcrumbs={[{ label: 'SMS Settings', icon: MessageSquareText }]}
        >
            <DataTable
                columns={Columns}
                data={data}
                meta={meta}
                routes={routes}
                title="SMS Settings"
                description="Manage the SMS gateway used for citizen notifications."
                resourceLabel="SMS Setting"
                searchPlaceholder="Search SMS settings…"
                getRowLabel={(row) => row.name}
            />
        </IndexLayout>
    );
}
