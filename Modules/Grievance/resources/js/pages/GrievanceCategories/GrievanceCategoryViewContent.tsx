import { DateCell } from '@/components/data-table/date-cell';
import { StatusCell } from '@/components/data-table/status-cell';
import { useTranslation } from '@/hooks/use-translation';
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
    const { t } = useTranslation();

    return (
        <div className="grid grid-cols-2 gap-4 pt-2">
            <Field label={t('Name (EN)')} value={grievanceCategory.name_en ?? '—'} />
            <Field label={t('Name (ST)')} value={grievanceCategory.name_st ?? '—'} />

            <Field label={t('Code')} value={grievanceCategory.code ?? '—'} />
            <Field label={t('Slug')} value={grievanceCategory.slug ?? '—'} />

            <Field
                label={t('Is Sensitive')}
                value={
                    <StatusCell
                        value={grievanceCategory.is_sensitive}
                        activeLabel={t('Yes')}
                        inactiveLabel={t('No')}
                    />
                }
            />
            <Field
                label={t('Is Active')}
                value={<StatusCell value={grievanceCategory.is_active} />}
            />

            <Field
                label={t('Sort Order')}
                value={grievanceCategory.sort_order ?? '—'}
            />
            <Field
                label={t('Created')}
                value={<DateCell value={grievanceCategory.created_at} />}
            />
        </div>
    );
}
