// resources/js/Pages/Resolutions/ResolutionViewContent.tsx
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import type { Resolution } from './columns';

interface ResolutionViewContentProps {
    resolution: Resolution;
}

type ResolutionState = 'proposed' | 'approved' | 'confirmed' | 'rejected';

const resolveState = (r: Resolution): ResolutionState => {
    if (r.rejected_reason) return 'rejected';
    if (r.complainant_confirmed_at) return 'confirmed';
    if (r.approved_at) return 'approved';
    return 'proposed';
};

const stateLabel = (state: ResolutionState) => {
    switch (state) {
        case 'proposed':
            return 'Proposed';
        case 'approved':
            return 'Approved';
        case 'confirmed':
            return 'Confirmed';
        case 'rejected':
            return 'Rejected';
    }
};

const stateVariant = (
    state: ResolutionState,
): 'default' | 'secondary' | 'outline' | 'destructive' => {
    switch (state) {
        case 'proposed':
            return 'outline';
        case 'approved':
            return 'secondary';
        case 'confirmed':
            return 'default';
        case 'rejected':
            return 'destructive';
    }
};

function Field({ label, value }: { label: string; value: React.ReactNode }) {
    return (
        <div className="space-y-1">
            <p className="text-xs font-medium text-muted-foreground">{label}</p>
            <div className="text-sm">{value}</div>
        </div>
    );
}

export function ResolutionViewContent({
    resolution: r,
}: ResolutionViewContentProps) {
    const state = resolveState(r);

    return (
        <div className="space-y-5">
            <div className="flex flex-wrap items-center gap-2">
                <Badge variant={stateVariant(state)}>{stateLabel(state)}</Badge>
            </div>

            <div className="grid grid-cols-2 gap-4">
                <Field
                    label="Grievance"
                    value={
                        r.grievance?.reference_number ?? `#${r.grievance_id}`
                    }
                />
                <Field label="Proposed By" value={r.proposedBy?.name ?? '—'} />
            </div>

            <Separator />

            <Field
                label="Resolution"
                value={
                    <p className="rounded-md border bg-muted/30 p-3 whitespace-pre-wrap">
                        {r.resolution_text}
                    </p>
                }
            />

            {r.rejected_reason && (
                <Field
                    label="Rejected Reason"
                    value={
                        <p className="rounded-md border border-destructive/40 bg-destructive/5 p-3 whitespace-pre-wrap text-destructive">
                            {r.rejected_reason}
                        </p>
                    }
                />
            )}

            <Separator />

            <div className="grid grid-cols-2 gap-4">
                <Field
                    label="Approved By"
                    value={r.approvedByUser?.name ?? '—'}
                />
                <Field
                    label="Approved At"
                    value={
                        r.approved_at
                            ? new Date(r.approved_at).toLocaleString()
                            : '—'
                    }
                />
            </div>

            <div className="grid grid-cols-2 gap-4">
                <Field
                    label="Complainant Confirmed At"
                    value={
                        r.complainant_confirmed_at
                            ? new Date(
                                  r.complainant_confirmed_at,
                              ).toLocaleString()
                            : '—'
                    }
                />
                <Field
                    label="Created"
                    value={new Date(r.created_at).toLocaleString()}
                />
            </div>
        </div>
    );
}
