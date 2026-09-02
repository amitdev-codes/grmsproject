// resources/js/types/grievance-status.ts
//
// Mirrors Modules\Grievance\Enums\GrievanceStatus. Keep this in sync by hand
// when the PHP enum changes — there's no automatic codegen here, so a status
// added on the backend won't show up in the UI until this file is updated
// too. (If this drifts often, worth generating it from the enum via a
// small artisan command instead of hand-syncing.)

export type GrievanceStatusValue =
    | 'submitted'
    | 'acknowledged'
    | 'assigned'
    | 'in_progress'
    | 'escalated'
    | 'resolved'
    | 'closed'
    | 'rejected'
    | 'reopened';

export type BadgeVariant = 'default' | 'secondary' | 'outline' | 'destructive';

interface StatusMeta {
    value: GrievanceStatusValue;
    label: string;
    variant: BadgeVariant;
    /** Matches GrievanceStatus::isTerminal() on the backend — no further transitions possible. */
    terminal: boolean;
}

// Order matches the PHP enum's case order.
export const GRIEVANCE_STATUSES: StatusMeta[] = [
    {
        value: 'submitted',
        label: 'Submitted',
        variant: 'default',
        terminal: false,
    },
    {
        value: 'acknowledged',
        label: 'Acknowledged',
        variant: 'default',
        terminal: false,
    },
    {
        value: 'assigned',
        label: 'Assigned',
        variant: 'default',
        terminal: false,
    },
    {
        value: 'in_progress',
        label: 'In Progress',
        variant: 'default',
        terminal: false,
    },
    {
        value: 'escalated',
        label: 'Escalated',
        variant: 'destructive',
        terminal: false,
    },
    {
        value: 'resolved',
        label: 'Resolved',
        variant: 'secondary',
        terminal: false,
    },
    { value: 'closed', label: 'Closed', variant: 'secondary', terminal: false },
    {
        value: 'rejected',
        label: 'Rejected',
        variant: 'destructive',
        terminal: true,
    },
    {
        value: 'reopened',
        label: 'Reopened',
        variant: 'outline',
        terminal: false,
    },
];

const STATUS_MAP = new Map(GRIEVANCE_STATUSES.map((s) => [s.value, s]));

export function statusLabel(value: string): string {
    return STATUS_MAP.get(value as GrievanceStatusValue)?.label ?? value;
}

export function statusVariant(value: string): BadgeVariant {
    return STATUS_MAP.get(value as GrievanceStatusValue)?.variant ?? 'default';
}

/** For Select2Field / filter options — { value, label } shape. */
export const GRIEVANCE_STATUS_OPTIONS = GRIEVANCE_STATUSES.map((s) => ({
    value: s.value,
    label: s.label,
}));
