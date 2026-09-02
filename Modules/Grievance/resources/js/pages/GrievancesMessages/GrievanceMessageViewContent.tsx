// resources/js/Pages/GrievanceMessages/GrievanceMessageViewContent.tsx
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import type { GrievanceMessage } from './columns';

interface GrievanceMessageViewContentProps {
    grievanceMessage: GrievanceMessage;
}

const senderTypeLabel = (type: GrievanceMessage['sender_type']) => {
    switch (type) {
        case 'officer':
            return 'Officer';
        case 'complainant':
            return 'Complainant';
        case 'system':
            return 'System';
        default:
            return type;
    }
};

const senderTypeVariant = (
    type: GrievanceMessage['sender_type'],
): 'default' | 'secondary' | 'outline' =>
    type === 'officer'
        ? 'default'
        : type === 'complainant'
          ? 'secondary'
          : 'outline';

function Field({ label, value }: { label: string; value: React.ReactNode }) {
    return (
        <div className="space-y-1">
            <p className="text-xs font-medium text-muted-foreground">{label}</p>
            <div className="text-sm">{value}</div>
        </div>
    );
}

export function GrievanceMessageViewContent({
    grievanceMessage,
}: GrievanceMessageViewContentProps) {
    const m = grievanceMessage;

    return (
        <div className="space-y-5">
            <div className="flex flex-wrap items-center gap-2">
                <Badge variant={senderTypeVariant(m.sender_type)}>
                    {senderTypeLabel(m.sender_type)}
                </Badge>
                {m.is_internal ? (
                    <Badge
                        variant="outline"
                        className="border-amber-400 text-amber-700 dark:text-amber-400"
                    >
                        Internal — not visible to complainant
                    </Badge>
                ) : (
                    <Badge variant="secondary">Visible to complainant</Badge>
                )}
            </div>

            <div className="grid grid-cols-2 gap-4">
                <Field
                    label="Grievance"
                    value={
                        m.grievance?.reference_number ?? `#${m.grievance_id}`
                    }
                />
                <Field label="From" value={m.user?.name ?? '—'} />
            </div>

            <Separator />

            <Field
                label="Message"
                value={
                    <p className="rounded-md border bg-muted/30 p-3 whitespace-pre-wrap">
                        {m.message}
                    </p>
                }
            />

            <Separator />

            <div className="grid grid-cols-2 gap-4">
                <Field
                    label="Sent"
                    value={new Date(m.created_at).toLocaleString()}
                />
                <Field
                    label="Last Updated"
                    value={new Date(m.updated_at).toLocaleString()}
                />
            </div>
        </div>
    );
}
