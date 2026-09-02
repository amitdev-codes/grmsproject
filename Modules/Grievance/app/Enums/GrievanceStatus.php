<?php

namespace Modules\Grievance\Enums;

enum GrievanceStatus: string
{
    case Submitted = 'submitted';
    case Acknowledged = 'acknowledged';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Escalated = 'escalated';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Rejected = 'rejected';
    case Reopened = 'reopened';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::Acknowledged => 'Acknowledged',
            self::Assigned => 'Assigned',
            self::InProgress => 'In progress',
            self::Escalated => 'Escalated',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
            self::Rejected => 'Rejected',
            self::Reopened => 'Reopened',
        };
    }

    /**
     * The state machine. This is the ONLY place transition legality is
     * defined — GrievanceWorkflowService consults this before writing
     * anything, so an invalid transition can never reach the database.
     *
     * @return self[]
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Submitted => [self::Acknowledged, self::Rejected],
            self::Acknowledged => [self::Assigned, self::Rejected],
            self::Assigned => [self::InProgress, self::Rejected],
            self::InProgress => [self::Escalated, self::Resolved, self::Rejected],
            self::Escalated => [self::InProgress, self::Resolved],
            self::Resolved => [self::Closed, self::Reopened],
            self::Closed => [self::Reopened],
            self::Rejected => [],
            self::Reopened => [self::Assigned, self::InProgress],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function isTerminal(): bool
    {
        return $this === self::Rejected;
    }
}
