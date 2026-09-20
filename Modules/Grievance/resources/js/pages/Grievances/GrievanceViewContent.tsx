import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { route } from 'ziggy-js';
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
                    {grievance.reference_no}
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
                            : grievance.complainant_name
                    }
                />
                <Field label="Category" value={grievance.category?.name_en} />
                <Field label="District" value={grievance.district?.name} />
                <Field label="Division" value={grievance.division?.name} />
                <Field label="Section" value={grievance.section?.name} />
                <Field
                    label="Assigned Officer"
                    value="Assigned through Section Manager workflow"
                />
                <Field label="Submitted Via" value={grievance.channel?.name} />
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
                {['assigned_officer', 'in_progress', 'escalated'].includes(
                    grievance.status,
                ) && (
                    <Button
                        asChild
                        variant="outline"
                        size="sm"
                        className="mt-3"
                    >
                        <a
                            href={route('resolutions.create', {
                                grievance_id: grievance.id,
                            })}
                        >
                            Prepare resolution note, memo or report
                        </a>
                    </Button>
                )}
            </div>

            <Separator />
            <div>
                <p className="mb-2 text-xs font-medium tracking-wide text-muted-foreground uppercase">
                    Documents
                </p>
                <div className="space-y-1 text-sm">
                    {(grievance.attachments ?? []).length === 0 && (
                        <p className="text-muted-foreground">
                            No documents attached.
                        </p>
                    )}
                    {(grievance.attachments ?? []).map((attachment) => (
                        <a
                            key={attachment.id}
                            href={attachment.url}
                            target="_blank"
                            rel="noreferrer"
                            className="block text-primary underline"
                        >
                            {attachment.file_name}
                        </a>
                    ))}
                </div>
            </div>

            <Separator />
            <div>
                <p className="mb-2 text-xs font-medium tracking-wide text-muted-foreground uppercase">
                    Status Timeline
                </p>
                <div className="space-y-3">
                    {(grievance.status_histories ?? []).length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            No status history available.
                        </p>
                    )}
                    {(grievance.status_histories ?? []).map((history) => (
                        <div
                            key={history.id}
                            className="border-l-2 pl-3 text-sm"
                        >
                            <p className="font-medium">
                                {history.to_status.replaceAll('_', ' ')}
                            </p>
                            <p className="text-xs text-muted-foreground">
                                {history.actor_role ?? 'System'} ·{' '}
                                {new Date(history.created_at).toLocaleString()}
                            </p>
                            {history.reason && (
                                <p className="mt-1 whitespace-pre-wrap text-muted-foreground">
                                    {history.reason}
                                </p>
                            )}
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}
