import { DateCell } from '@/components/data-table/date-cell';
import { StatusCell } from '@/components/data-table/status-cell';
import type { GrievanceCategory } from './columns';

interface GrievanceCategoryViewContentProps {
    grievanceCategory: GrievanceCategory;
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

export function GrievanceCategoryViewContent({
    grievanceCategory,
}: GrievanceCategoryViewContentProps) {
    return (
        <div className="grid grid-cols-2 gap-4 pt-2">
            <Field label="Name (EN)" value={grievanceCategory.name_en ?? '—'} />
            <Field label="Name (ST)" value={grievanceCategory.name_st ?? '—'} />

            <Field label="Code" value={grievanceCategory.code ?? '—'} />
            <Field label="Slug" value={grievanceCategory.slug ?? '—'} />

            <Field
                label="Is Sensitive"
                value={
                    <StatusCell
                        value={grievanceCategory.is_sensitive}
                        activeLabel="Yes"
                        inactiveLabel="No"
                    />
                }
            />
            <Field
                label="Is Active"
                value={<StatusCell value={grievanceCategory.is_active} />}
            />

            <Field
                label="Sort Order"
                value={grievanceCategory.sort_order ?? '—'}
            />
            <Field
                label="Created"
                value={<DateCell value={grievanceCategory.created_at} />}
            />
        </div>
    );
}
