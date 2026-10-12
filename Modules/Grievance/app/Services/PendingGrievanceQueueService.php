<?php

namespace Modules\Grievance\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Models\GrievanceWorkflowStep;

class PendingGrievanceQueueService
{
    private const ASSIGNED_OFFICER_ACTIONS = ['prepare_resolution', 'investigation', 'acknowledge'];

    private const ASSIGNED_ROLE_STEPS = ['review' => 'Helpdesk Officer'];

    public function queryFor(User $user): Builder
    {
        return $this->constrain(Grievance::query(), $user);
    }

    public function constrain(Builder $query, User $user): Builder
    {
        $steps = $this->actionableWorkflowSteps($user);
        $standardStepIds = $steps
            ->filter(fn (GrievanceWorkflowStep $step): bool => ! in_array(
                $step->action_type,
                [...self::ASSIGNED_OFFICER_ACTIONS, 'allocate_section', 'assign_officer'],
                true,
            ) && (self::ASSIGNED_ROLE_STEPS[$step->action_type] ?? null) !== $step->role_name)
            ->pluck('id')
            ->all();
        $assignedOfficerStepIds = $steps
            ->filter(fn (GrievanceWorkflowStep $step): bool => in_array($step->action_type, self::ASSIGNED_OFFICER_ACTIONS, true)
                || (self::ASSIGNED_ROLE_STEPS[$step->action_type] ?? null) === $step->role_name)
            ->pluck('id')
            ->all();
        $divisionStepIds = $steps->where('action_type', 'allocate_section')->pluck('id')->all();
        $sectionStepIds = $steps->where('action_type', 'assign_officer')->pluck('id')->all();
        $roleNames = $user->getRoleNames()->all();

        $hasWorkflowQueue = $standardStepIds !== []
            || $assignedOfficerStepIds !== []
            || ($divisionStepIds !== [] && $user->division_id)
            || ($sectionStepIds !== [] && $user->section_id);

        if (! $hasWorkflowQueue && ! $this->hasLegacyQueue($user, $roleNames)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $query) use ($user, $roleNames, $standardStepIds, $assignedOfficerStepIds, $divisionStepIds, $sectionStepIds): void {
            if ($standardStepIds !== []) {
                $query->whereIn('current_workflow_step_id', $standardStepIds);
            }

            if ($assignedOfficerStepIds !== []) {
                $query->orWhere(fn (Builder $queue) => $queue
                    ->where('assigned_officer_id', $user->id)
                    ->whereIn('current_workflow_step_id', $assignedOfficerStepIds));
            }

            if ($divisionStepIds !== [] && $user->division_id) {
                $query->orWhere(fn (Builder $queue) => $queue
                    ->where('division_id', $user->division_id)
                    ->whereIn('current_workflow_step_id', $divisionStepIds));
            }

            if ($sectionStepIds !== [] && $user->section_id) {
                $query->orWhere(fn (Builder $queue) => $queue
                    ->where('section_id', $user->section_id)
                    ->whereIn('current_workflow_step_id', $sectionStepIds));
            }

            $this->orWhereLegacyQueue($query, $user, $roleNames);
        });
    }

    public function visibleFor(User $user): bool
    {
        $steps = $this->actionableWorkflowSteps($user);
        $hasWorkflowQueue = $steps->contains(fn (GrievanceWorkflowStep $step): bool => (! in_array(
            $step->action_type,
            [...self::ASSIGNED_OFFICER_ACTIONS, 'allocate_section', 'assign_officer'],
            true,
        ) && (self::ASSIGNED_ROLE_STEPS[$step->action_type] ?? null) !== $step->role_name)
            || in_array($step->action_type, self::ASSIGNED_OFFICER_ACTIONS, true)
            || (self::ASSIGNED_ROLE_STEPS[$step->action_type] ?? null) === $step->role_name)
            || ($user->division_id && $steps->contains('action_type', 'allocate_section'))
            || ($user->section_id && $steps->contains('action_type', 'assign_officer'));

        if ($hasWorkflowQueue) {
            return true;
        }

        $roleNames = $user->getRoleNames()->all();

        return $this->hasLegacyQueue($user, $roleNames);
    }

    /**
     * @return Collection<int, GrievanceWorkflowStep>
     */
    private function actionableWorkflowSteps(User $user): Collection
    {
        $roleNames = $user->getRoleNames()->all();

        return GrievanceWorkflowStep::query()
            ->where('workflow_key', 'default')
            ->where('is_active', true)
            ->where(function (Builder $query) use ($user, $roleNames): void {
                $query->where('approver_user_id', $user->id);

                if ($roleNames !== []) {
                    $query->orWhere(fn (Builder $roleQuery) => $roleQuery
                        ->whereNull('approver_user_id')
                        ->whereIn('role_name', $roleNames));
                }
            })
            ->get()
            ->filter(fn (GrievanceWorkflowStep $step): bool => $user->can($this->permissionFor($step)))
            ->values();
    }

    private function permissionFor(GrievanceWorkflowStep $step): string
    {
        return match ($step->action_type) {
            'allocate_division', 'allocate_section', 'screen_allocate' => 'grievances.allocate',
            'assign_officer' => 'grievances.assign',
            'prepare_resolution', 'investigation', 'final_approval', 'acknowledge', 'decision_approval' => 'grievances.resolve',
            'closure' => 'grievances.close',
            'escalation' => 'grievances.escalate',
            default => 'grievances.view',
        };
    }

    private function orWhereLegacyQueue(Builder $query, User $user, array $roleNames): void
    {
        if (in_array('Director', $roleNames, true)) {
            $query->orWhereIn('status', ['submitted', 'reallocation_required', 'pending_approval']);
        }

        if (in_array('Division Director', $roleNames, true) && $user->division_id) {
            $query->orWhere(fn (Builder $queue) => $queue
                ->where('status', 'allocated_division')
                ->where('division_id', $user->division_id));
        }

        if (in_array('Section Manager', $roleNames, true) && $user->section_id) {
            $query->orWhere(fn (Builder $queue) => $queue
                ->where('status', 'allocated_section')
                ->where('section_id', $user->section_id));
        }

        if (array_intersect(['Helpdesk Officer', 'Content Editor'], $roleNames) !== []) {
            $query->orWhere(fn (Builder $queue) => $queue
                ->whereIn('status', ['assigned_officer', 'assigned', 'in_progress', 'escalated'])
                ->where('assigned_officer_id', $user->id));
        }
    }

    private function hasLegacyQueue(User $user, array $roleNames): bool
    {
        return in_array('Director', $roleNames, true)
            || (in_array('Division Director', $roleNames, true) && (bool) $user->division_id)
            || (in_array('Section Manager', $roleNames, true) && (bool) $user->section_id)
            || array_intersect(['Helpdesk Officer', 'Content Editor'], $roleNames) !== [];
    }
}
