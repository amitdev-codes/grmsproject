// resources/js/Pages/GrievanceStatusHistories/GrievanceStatusHistoryViewContent.tsx
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { statusLabel } from '@/types/grievance-status';
import type { GrievanceStatusHistory } from './columns';

interface GrievanceStatusHistoryViewContentProps {
    history: GrievanceStatusHistory;
}

function Field({ label, value }: { label: string; value: React.ReactNode }) {
    return (
        <div className="space-y-1">
            <p className="text-xs font-medium text-muted-foreground">{label}</p>
            <div className="text-sm">{value}</div>
        </div>
    );
}

export function GrievanceStatusHistoryViewContent({
    history: h,
}: GrievanceStatusHistoryViewContentProps) {
    return (
        <div className="space-y-5">
            <div className="flex flex-wrap items-center gap-2 text-sm">
                {h.from_status ? (
                    <>
                        <Badge variant="outline">
                            {statusLabel(h.from_status)}
                        </Badge>
                        <span className="text-muted-foreground">→</span>
                    </>
                ) : (
                    <span className="text-xs text-muted-foreground">
                        {'(initial status)'}
                    </span>
                )}
                <Badge>{statusLabel(h.to_status)}</Badge>
            </div>

            <div className="grid grid-cols-2 gap-4">
                <Field
                    label="Grievance"
                    value={
                        h.grievance?.reference_number ?? `#${h.grievance_id}`
                    }
                />
                <Field
                    label="Changed By"
                    value={h.changedBy?.name ?? 'System'}
                />
            </div>

            <Separator />

            <Field
                label="Note"
                value={
                    h.note ? (
                        <p className="rounded-md border bg-muted/30 p-3 whitespace-pre-wrap">
                            {h.note}
                        </p>
                    ) : (
                        <span className="text-muted-foreground">—</span>
                    )
                }
            />

            <Separator />

            <Field
                label="Recorded At"
                value={new Date(h.created_at).toLocaleString()}
            />
        </div>
    );
}
