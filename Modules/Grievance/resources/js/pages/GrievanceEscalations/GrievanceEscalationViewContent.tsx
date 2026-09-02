import { DateCell } from '@/components/data-table/date-cell';
import { StatusCell } from '@/components/data-table/status-cell';
import { Badge } from '@/components/ui/badge';
import type { GrievanceEscalation } from './columns';

interface GrievanceEscalationViewContentProps {
    GrievanceEscalation: GrievanceEscalation;
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

const levelLabel = (level: number) => {
    switch (level) {
        case 1:
            return 'Level 1 – Zonal Officer';
        case 2:
            return 'Level 2 – Regional Head';
        case 3:
            return 'Level 3 – Director';
        default:
            return `Level ${level}`;
    }
};

const levelVariant = (level: number): 'default' | 'secondary' | 'outline' =>
    level === 1 ? 'outline' : level === 2 ? 'secondary' : 'default';

export function GrievanceEscalationViewContent({
    GrievanceEscalation,
}: GrievanceEscalationViewContentProps) {
    return (
        <div className="grid grid-cols-2 gap-4 pt-2">
            <Field
                label="Grievance"
                value={GrievanceEscalation.grievance?.reference_number ?? '—'}
            />
            <Field
                label="Escalation Level"
                value={
                    <Badge
                        variant={levelVariant(
                            GrievanceEscalation.escalation_level,
                        )}
                    >
                        {levelLabel(GrievanceEscalation.escalation_level)}
                    </Badge>
                }
            />
            <Field
                label="Escalated To"
                value={GrievanceEscalation.escalated_to_user?.name ?? '—'}
            />
            <Field
                label="SLA Breached"
                value={<DateCell value={GrievanceEscalation.sla_breached_at} />}
            />
            <Field
                label="Escalated At"
                value={<DateCell value={GrievanceEscalation.escalated_at} />}
            />
            <Field label="Reason" value={GrievanceEscalation.reason ?? '—'} />
            <Field
                label="Resolved"
                value={
                    <StatusCell
                        value={GrievanceEscalation.resolved}
                        activeLabel="Resolved"
                        inactiveLabel="Open"
                    />
                }
            />
            <Field
                label="Created"
                value={<DateCell value={GrievanceEscalation.created_at} />}
            />
        </div>
    );
}
