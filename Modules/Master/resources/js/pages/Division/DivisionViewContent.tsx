import { DateCell } from '@/components/data-table/date-cell';
import type { Division } from './Columns';

interface DivisionViewContentProps {
    division: Division;
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

export function DivisionViewContent({ division }: DivisionViewContentProps) {
    return (
        <div className="grid grid-cols-2 gap-4 pt-2">
            <Field label="Code" value={division.code} />
            <Field label="Name" value={division.name} />
            <Field label="Name ST" value={division.name_st ?? '—'} />
            <Field
                label="Created"
                value={<DateCell value={division.created_at} />}
            />
            <div className="col-span-2 space-y-1">
                <div className="text-sm font-medium text-muted-foreground">
                    Description
                </div>
                <div className="text-sm whitespace-pre-wrap">
                    {division.description ?? '—'}
                </div>
            </div>
        </div>
    );
}
