import { DateCell } from '@/components/data-table/date-cell';
import { StatusCell } from '@/components/data-table/status-cell';;
import type { GrievanceChannel } from './columns';

interface GrievanceChannelViewContentProps {
    grievanceChannel: GrievanceChannel;
}

function Field({
                   label,
                   value,
               }: {
    label: string;
    value: React.ReactNode;
}) {
    return (
        <div className="space-y-1">
            <div className="text-sm font-medium text-muted-foreground">
                {label}
            </div>

            <div className="text-sm">
                {value}
            </div>
        </div>
    );
}

export function GrievanceChannelViewContent({
    grievanceChannel,
}: GrievanceChannelViewContentProps) {

    return (
        <>
            <div className="grid grid-cols-2 gap-4 pt-2">
                <Field label="Code" value={grievanceChannel.code ?? '—'} />
                <Field label="Name" value={grievanceChannel.name ?? '—'} />
                <Field
                    label="Is Active"
                    value={<StatusCell value={grievanceChannel.is_active} />}
                />
                <Field
                    label="Created"
                    value={<DateCell value={grievanceChannel.created_at} />}
                />
            </div>
        </>
    );
}
