import { DateCell } from '@/components/data-table/date-cell';
import type { District } from './Columns';

interface DistrictViewContentProps {
    district: District;
}

function Field({ label, value }: { label: string; value: React.ReactNode }) {
    return (
        <div className="space-y-1">
            <div className="text-sm font-medium text-muted-foreground">
                {label}
            </div>
            <div className="text-sm">{value}</div>
        </div>
    );
}

export function DistrictViewContent({ district }: DistrictViewContentProps) {
    return (
        <div className="grid grid-cols-2 gap-4 pt-2">
            <Field label="Code" value={district.code} />
            <Field label="Name" value={district.name} />
            <Field label="Name ST" value={district.name_st ?? '—'} />
            <Field
                label="Created"
                value={<DateCell value={district.created_at} />}
            />
        </div>
    );
}
