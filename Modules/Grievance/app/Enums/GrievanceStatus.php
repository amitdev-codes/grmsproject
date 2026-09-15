<?php

namespace Modules\Grievance\Enums;

enum GrievanceStatus: string
{
    case Submitted = 'submitted';
    case Acknowledged = 'acknowledged';
    case AllocatedDivision = 'allocated_division';
    case AllocatedSection = 'allocated_section';
    case ReallocationRequired = 'reallocation_required';
    case AssignedOfficer = 'assigned_officer';
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
            self::AllocatedDivision => 'Allocated to division',
            self::AllocatedSection => 'Allocated to section',
            self::ReallocationRequired => 'Reallocation required',
            self::AssignedOfficer => 'Assigned to investigating officer',
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
            self::Submitted => [self::Acknowledged, self::AllocatedDivision, self::Rejected],
            self::Acknowledged => [self::AllocatedDivision, self::Rejected],
            self::AllocatedDivision => [self::AllocatedSection, self::ReallocationRequired, self::Rejected],
            self::AllocatedSection => [self::AssignedOfficer, self::ReallocationRequired, self::Rejected],
            self::ReallocationRequired => [self::AllocatedDivision, self::Rejected],
            self::AssignedOfficer => [self::InProgress, self::Rejected],
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
