import { DateCell } from '@/components/data-table/date-cell';
import { Badge } from '@/components/ui/badge';
import type { Section } from './Columns';

interface SectionViewContentProps {
    section: Section;
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

const divisionVariant = (
    division: string,
): 'default' | 'secondary' | 'outline' =>
    division === 'Northern Region'
        ? 'default'
        : division === 'Central Region'
          ? 'secondary'
          : 'outline';

export function SectionViewContent({ section }: SectionViewContentProps) {
    return (
        <div className="grid grid-cols-2 gap-4 pt-2">
            <Field label="Code" value={section.code} />
            <Field label="Name" value={section.name} />
            <Field label="Name ST" value={section.name_st ?? '—'} />
            <Field
                label="Division"
                value={
                    section.division ? (
                        <Badge variant={divisionVariant(section.division.name)}>
                            {section.division.name}
                        </Badge>
                    ) : (
                        '—'
                    )
                }
            />
            <Field
                label="Created"
                value={<DateCell value={section.created_at} />}
            />
        </div>
    );
}
