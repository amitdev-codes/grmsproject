import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import type { Grievance } from './columns';

function Field({ label, value }: { label: string; value: React.ReactNode }) {
    return (
        <div>
            <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                {label}
            </p>
            <p className="mt-0.5 text-sm">{value ?? '—'}</p>
        </div>
    );
}

export function GrievanceViewContent({ grievance }: { grievance: Grievance }) {
    return (
        <div className="space-y-5">
            <div className="flex items-center justify-between">
                <span className="font-mono text-sm text-muted-foreground">
                    {grievance.reference_number}
                </span>
                <div className="flex gap-2">
                    <Badge className="capitalize">{grievance.priority}</Badge>
                    <Badge variant="secondary">
                        {grievance.status.replace('_', ' ')}
                    </Badge>
                </div>
            </div>

            <Separator />

            <div className="grid grid-cols-2 gap-4">
                <Field
                    label="Complainant"
                    value={
                        grievance.is_anonymous
                            ? 'Anonymous'
                            : grievance.contact_name
                    }
                />
                <Field label="Category" value={grievance.category?.name_en} />
                <Field label="District" value={grievance.district?.name_en} />
                <Field label="Division" value={grievance.division?.name} />
                <Field label="Section" value={grievance.section?.name} />
                <Field
                    label="Assigned Officer"
                    value={grievance.assigned_officer?.name ?? 'Unassigned'}
                />
                <Field label="Submitted Via" value={grievance.submitted_via} />
                <Field
                    label="SLA Due"
                    value={
                        grievance.sla_due_at
                            ? new Date(
                                  grievance.sla_due_at,
                              ).toLocaleDateString()
                            : null
                    }
                />
            </div>

            {!grievance.is_anonymous &&
                (grievance.contact_email || grievance.contact_phone) && (
                    <>
                        <Separator />
                        <div className="grid grid-cols-2 gap-4">
                            <Field
                                label="Email"
                                value={grievance.contact_email}
                            />
                            <Field
                                label="Phone"
                                value={grievance.contact_phone}
                            />
                        </div>
                    </>
                )}

            <Separator />

            <div>
                <p className="mb-1 text-xs font-medium tracking-wide text-muted-foreground uppercase">
                    Description
                </p>
                <p className="text-sm leading-relaxed whitespace-pre-wrap">
                    {grievance.description}
                </p>
            </div>
        </div>
    );
}
