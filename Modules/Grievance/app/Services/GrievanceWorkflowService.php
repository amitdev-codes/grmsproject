<?php

namespace Modules\Grievance\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Grievance\Interface\GrievanceRepositoryInterface;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Models\GrievanceWorkflowAction;
use Modules\Grievance\Models\GrievanceWorkflowStep;
use Modules\Grievance\Notifications\GrievanceAssigned;
use Modules\Grievance\Notifications\GrievanceWorkflowActionRequired;
use Modules\Master\Models\Section;

class GrievanceWorkflowService
{
    public function __construct(
        private readonly GrievanceRepositoryInterface $grievances,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  UploadedFile[]  $documents
     */
    public function act(Grievance $grievance, User $actor, array $data, array $documents = []): GrievanceWorkflowAction
    {
        $storedPaths = [];

        try {
            return DB::transaction(function () use ($grievance, $actor, $data, $documents, &$storedPaths): GrievanceWorkflowAction {
                $lockedGrievance = Grievance::query()
                    ->lockForUpdate()
                    ->with('currentWorkflowStep')
                    ->findOrFail($grievance->id);
                $step = $lockedGrievance->currentWorkflowStep;

                if (! $step || ! $step->is_active || $step->workflow_key !== 'default') {
                    throw ValidationException::withMessages(['workflow' => 'This grievance is not at an active workflow step.']);
                }

                $this->authorizeActor($step, $actor, $lockedGrievance);
                $fromStatus = $lockedGrievance->status;
                $fromStep = $step;
                $status = $fromStatus;
                $targetStep = null;
                $actionData = [];
                $autoAssignedHelpdeskId = null;
                $decision = $data['decision'];
                $reason = $data['reason'] ?? null;

                if ($decision === 'acknowledge') {
                    if (! in_array($step->action_type, ['prepare_resolution', 'acknowledge'], true)
                        || $fromStatus !== 'assigned_officer') {
                        throw ValidationException::withMessages(['decision' => 'This grievance cannot be acknowledged at its current workflow step.']);
                    }

                    $status = 'in_progress';
                    $targetStep = $step->action_type === 'acknowledge'
                        ? $this->nextStep($step)
                        : $step;
                    if ($targetStep !== $step) {
                        $actionData['current_workflow_step_id'] = $targetStep->id;
                    }
                } elseif ($decision === 'reject') {
                    if ($step->rejection_action === 'reject') {
                        $status = 'rejected';
                        $actionData = [
                            'current_workflow_step_id' => null,
                            'closed_by' => $actor->id,
                            'closed_reason' => $reason,
                            'closed_at' => now(),
                        ];
                    } else {
                        $targetStep = GrievanceWorkflowStep::query()
                            ->where('workflow_key', 'default')
                            ->where('step_number', $step->rejection_target_step)
                            ->where('is_active', true)
                            ->first();

                        if (! $targetStep) {
                            throw ValidationException::withMessages(['workflow' => 'The configured return level is not active.']);
                        }

                        $status = $this->statusForStep($targetStep);
                        $actionData = [
                            'current_workflow_step_id' => $targetStep->id,
                            ...$this->resetForStep($targetStep),
                        ];
                    }
                } else {
                    [$targetStep, $status, $actionData] = $this->approve($lockedGrievance, $step, $actor, $data);
                }

                if ($targetStep?->action_type === 'review' && $targetStep->role_name === 'Helpdesk Officer') {
                    $helpdeskOfficer = app(HelpdeskOfficerAssignmentService::class)->assignFor(
                        $lockedGrievance,
                        $targetStep->approver_user_id,
                    );

                    if (! $helpdeskOfficer) {
                        throw ValidationException::withMessages([
                            'workflow' => 'No active Helpdesk Officer is available. Contact an administrator to configure Helpdesk Officer accounts.',
                        ]);
                    }

                    $actionData['assigned_officer_id'] = $helpdeskOfficer->id;
                    $autoAssignedHelpdeskId = $helpdeskOfficer->id;
                }

                if ($decision === 'reject') {
                    DB::table('grievance_assignments')->insert([
                        'grievance_id' => $lockedGrievance->id,
                        'action' => 'rejected',
                        'from_division_id' => $lockedGrievance->division_id,
                        'from_section_id' => $lockedGrievance->section_id,
                        'from_officer_id' => $lockedGrievance->assigned_officer_id,
                        'acted_by' => $actor->id,
                        'reason' => $reason,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } elseif ($decision !== 'acknowledge') {
                    $this->recordAssignment($lockedGrievance, $step, $actor, $data);
                }

                if ($autoAssignedHelpdeskId !== null) {
                    DB::table('grievance_assignments')->insert([
                        'grievance_id' => $lockedGrievance->id,
                        'action' => $lockedGrievance->assigned_officer_id ? 'reassigned' : 'assigned',
                        'from_division_id' => $lockedGrievance->division_id,
                        'to_division_id' => $lockedGrievance->division_id,
                        'from_section_id' => $lockedGrievance->section_id,
                        'to_section_id' => $lockedGrievance->section_id,
                        'from_officer_id' => $lockedGrievance->assigned_officer_id,
                        'to_officer_id' => $autoAssignedHelpdeskId,
                        'acted_by' => $actor->id,
                        'reason' => 'Automatically assigned to an active Helpdesk Officer based on geographic scope and workload.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $action = $lockedGrievance->workflowActions()->create([
                    'from_step_id' => $fromStep->id,
                    'to_step_id' => $targetStep?->id,
                    'actor_id' => $actor->id,
                    'decision' => $decision,
                    'reason' => $reason,
                    'data' => $data['workflow_data'] ?? null,
                ]);

                foreach ($documents as $document) {
                    $path = $document->storeAs(
                        "grievance-workflow-documents/{$lockedGrievance->id}",
                        Str::uuid().'.'.$document->getClientOriginalExtension(),
                        'local',
                    );

                    if (! $path) {
                        throw new \RuntimeException('Unable to store a workflow document.');
                    }

                    $storedPaths[] = $path;
                    $action->documents()->create([
                        'uploaded_by' => $actor->id,
                        'disk' => 'local',
                        'path' => $path,
                        'original_name' => $document->getClientOriginalName(),
                        'mime_type' => $document->getMimeType() ?: 'application/octet-stream',
                        'size' => $document->getSize(),
                    ]);
                }

                $actionData['status'] = $status;
                $lockedGrievance->update($actionData);

                if ($status !== $fromStatus) {
                    $this->grievances->recordStatus(
                        $lockedGrievance,
                        $fromStatus,
                        $status,
                        $actor->id,
                        $actor->getRoleNames()->first(),
                        $reason,
                    );
                }

                $this->notifyNextApprover($lockedGrievance, $targetStep);

                return $action->load(['fromStep', 'toStep', 'actor', 'documents']);
            });
        } catch (\Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }
    }

    public function startInvestigation(Grievance $grievance, User $actor, ?string $remarks = null): Grievance
    {
        return DB::transaction(function () use ($grievance, $actor, $remarks): Grievance {
            $lockedGrievance = Grievance::query()
                ->lockForUpdate()
                ->with('currentWorkflowStep')
                ->findOrFail($grievance->id);
            $step = $lockedGrievance->currentWorkflowStep;

            if (! $step || $step->action_type !== 'prepare_resolution' || $lockedGrievance->status !== 'assigned_officer') {
                throw ValidationException::withMessages(['workflow' => 'The investigation can only start when assigned to the investigation step.']);
            }

            $this->authorizeActor($step, $actor, $lockedGrievance);
            $lockedGrievance->update(['status' => 'in_progress']);
            $this->grievances->recordStatus(
                $lockedGrievance,
                'assigned_officer',
                'in_progress',
                $actor->id,
                $actor->getRoleNames()->first(),
                $remarks,
            );
            $lockedGrievance->workflowActions()->create([
                'from_step_id' => $step->id,
                'to_step_id' => $step->id,
                'actor_id' => $actor->id,
                'decision' => 'start_investigation',
                'reason' => $remarks,
            ]);

            return $lockedGrievance->refresh();
        });
    }

    private function approve(Grievance $grievance, GrievanceWorkflowStep $step, User $actor, array $data): array
    {
        $actionData = [];
        $rejectedClosure = false;

        switch ($step->action_type) {
            case 'allocate_division':
                $divisionId = (int) $data['division_id'];
                $actionData['division_id'] = $divisionId;
                if ($grievance->division_id !== $divisionId) {
                    $actionData['section_id'] = null;
                    $actionData['assigned_officer_id'] = null;
                }
                break;
            case 'allocate_section':
                $section = Section::query()->findOrFail((int) $data['section_id']);
                if ($grievance->division_id && $section->division_id !== $grievance->division_id) {
                    throw ValidationException::withMessages(['section_id' => 'Select a section within the grievance division.']);
                }
                $actionData['section_id'] = $section->id;
                $actionData['assigned_officer_id'] = null;
                break;
            case 'assign_officer':
            case 'screen_allocate':
                if ($step->action_type === 'screen_allocate') {
                    $section = Section::query()->findOrFail((int) $data['section_id']);
                    if ($section->division_id !== (int) $data['division_id']) {
                        throw ValidationException::withMessages(['section_id' => 'Select a section within the allocated division.']);
                    }
                    if ($actor->division_id && $actor->division_id !== (int) $data['division_id']) {
                        throw ValidationException::withMessages(['division_id' => 'Select a division within your assigned remit.']);
                    }

                    $actionData['division_id'] = (int) $data['division_id'];
                    $actionData['section_id'] = $section->id;

                    $workflowData = $data['workflow_data'] ?? [];
                    if (isset($workflowData['severity'])) {
                        $actionData['priority'] = $workflowData['severity'] === 'medium'
                            ? 'normal'
                            : $workflowData['severity'];
                    }
                    if (isset($workflowData['repeat_case'])) {
                        $actionData['is_previously_lodged'] = (bool) $workflowData['repeat_case'];
                    }
                }

                $officer = User::query()->whereKey((int) $data['officer_id'])->firstOrFail();
                if (! $officer->status
                    || ($step->action_type === 'screen_allocate'
                        ? ! $officer->hasRole('Investigator')
                        : ! $officer->hasAnyRole(['Helpdesk Officer', 'Investigator']))
                    || ($step->action_type !== 'screen_allocate'
                        && $grievance->section_id
                        && $officer->section_id !== null
                        && $officer->section_id !== $grievance->section_id)
                    || ($step->action_type === 'screen_allocate'
                        && $officer->division_id !== null
                        && $officer->division_id !== (int) $data['division_id'])
                    || ($step->action_type === 'screen_allocate'
                        && $officer->section_id !== null
                        && $officer->section_id !== (int) $data['section_id'])) {
                    throw ValidationException::withMessages(['officer_id' => 'Select an active Investigator assigned to this division and section or not yet assigned to a section.']);
                }
                $actionData['assigned_officer_id'] = $officer->id;
                break;
            case 'prepare_resolution':
            case 'investigation':
                if (! in_array($grievance->status, ['in_progress', 'escalated'], true)) {
                    throw ValidationException::withMessages(['workflow' => 'Start the investigation before submitting a resolution draft.']);
                }
                $this->saveResolutionDraft(
                    $grievance,
                    $actor,
                    $data['resolution_html'],
                    $data['document_type'] ?? 'report',
                );
                break;
            case 'decision_approval':
                $resolution = $grievance->resolutions()
                    ->whereNull('approved_at')
                    ->latest('id')
                    ->first();

                if (! $resolution) {
                    throw ValidationException::withMessages(['workflow' => 'A resolution draft must be submitted before the decision is approved.']);
                }

                $resolution->update([
                    'approved_by' => $actor->id,
                    'approved_at' => now(),
                ]);
                break;
            case 'closure':
                $workflowData = $data['workflow_data'] ?? [];
                $actionData['satisfaction_rating'] = $workflowData['satisfaction_rating'] ?? null;
                $actionData['closed_reason'] = $data['reason'] ?? null;
                $rejectedClosure = ! (bool) ($workflowData['acceptance'] ?? true);
                break;
            case 'escalation':
                break;
            case 'monitoring':
                $actionData += [
                    'current_workflow_step_id' => null,
                    'closed_by' => $actor->id,
                    'closed_reason' => $data['reason'] ?? null,
                    'closed_at' => now(),
                ];

                return [null, 'closed', $actionData];
            case 'informal_resolution':
                if ($step->approval_target_step) {
                    $actionData['closed_by'] = $actor->id;
                    $actionData['closed_reason'] = $data['reason'] ?? null;
                }
                break;
            case 'final_approval':
                if (! $step->is_final_approval || $step->approval_action !== 'resolve') {
                    throw ValidationException::withMessages(['workflow' => 'The configured level is not a final approval step.']);
                }

                $resolution = $grievance->resolutions()
                    ->whereNull('approved_at')
                    ->latest('id')
                    ->first();

                if (! $resolution) {
                    throw ValidationException::withMessages(['workflow' => 'A resolution draft must be submitted before final approval.']);
                }

                $resolution->update([
                    'approved_by' => $actor->id,
                    'approved_at' => now(),
                ]);
                $actionData += [
                    'current_workflow_step_id' => null,
                    'closed_by' => $actor->id,
                    'closed_at' => now(),
                ];

                return [null, 'resolved', $actionData];
            case 'review':
                break;
            default:
                throw ValidationException::withMessages(['workflow' => 'The configured workflow action is not supported.']);
        }

        if ($step->approval_action === 'resolve' && $step->action_type !== 'final_approval') {
            throw ValidationException::withMessages(['workflow' => 'Only a final approval step may resolve a grievance.']);
        }

        $nextStep = $this->nextStep($step);

        $targetStep = $rejectedClosure
            ? $this->targetStep($step, (int) $step->rejection_target_step)
            : ($step->approval_target_step
                ? $this->targetStep($step, $step->approval_target_step)
                : $nextStep);
        $actionData['current_workflow_step_id'] = $targetStep->id;

        $status = match ($step->action_type) {
            'informal_resolution' => 'resolved',
            'closure' => $rejectedClosure ? 'in_progress' : 'resolved',
            'escalation' => 'escalated',
            default => $this->statusForStep($targetStep),
        };

        return [$targetStep, $status, $actionData];
    }

    private function nextStep(GrievanceWorkflowStep $step): GrievanceWorkflowStep
    {
        $nextStep = GrievanceWorkflowStep::query()
            ->where('workflow_key', 'default')
            ->where('is_active', true)
            ->where('step_number', '>', $step->step_number)
            ->orderBy('step_number')
            ->first();

        if (! $nextStep) {
            throw ValidationException::withMessages(['workflow' => 'There is no active next workflow level.']);
        }

        return $nextStep;
    }

    private function targetStep(GrievanceWorkflowStep $step, int $stepNumber): GrievanceWorkflowStep
    {
        $targetStep = GrievanceWorkflowStep::query()
            ->where('workflow_key', 'default')
            ->where('step_number', $stepNumber)
            ->where('is_active', true)
            ->first();

        if (! $targetStep) {
            throw ValidationException::withMessages(['workflow' => 'The configured route target is not active.']);
        }

        return $targetStep;
    }

    private function saveResolutionDraft(Grievance $grievance, User $actor, string $html, string $documentType): void
    {
        $safeHtml = app(ResolutionDraftSanitizer::class)->sanitize($html);

        if (trim(strip_tags($safeHtml)) === '') {
            throw ValidationException::withMessages(['resolution_html' => 'Enter resolution details before submitting the draft.']);
        }

        $draft = $grievance->resolutions()->whereNull('approved_at')->latest('id')->first();
        $attributes = [
            'proposed_by' => $actor->id,
            'document_type' => $documentType,
            'resolution_text' => $safeHtml,
            'approved_by' => null,
            'approved_at' => null,
        ];

        if ($draft) {
            $draft->update($attributes);
        } else {
            $grievance->resolutions()->create($attributes);
        }
    }

    private function resetForStep(GrievanceWorkflowStep $step): array
    {
        return match ($step->action_type) {
            'allocate_division' => [
                'division_id' => null,
                'section_id' => null,
                'assigned_officer_id' => null,
            ],
            'allocate_section' => [
                'section_id' => null,
                'assigned_officer_id' => null,
            ],
            'assign_officer' => ['assigned_officer_id' => null],
            'screen_allocate' => [
                'division_id' => null,
                'section_id' => null,
                'assigned_officer_id' => null,
            ],
            default => [],
        };
    }

    private function recordAssignment(
        Grievance $grievance,
        GrievanceWorkflowStep $step,
        User $actor,
        array $data,
    ): void {
        $action = match ($step->action_type) {
            'allocate_division', 'allocate_section', 'screen_allocate' => 'allocated',
            'assign_officer' => 'assigned',
            default => null,
        };

        if (! $action) {
            return;
        }

        DB::table('grievance_assignments')->insert([
            'grievance_id' => $grievance->id,
            'action' => $action,
            'from_division_id' => $grievance->division_id,
            'to_division_id' => $data['division_id'] ?? $grievance->division_id,
            'from_section_id' => $grievance->section_id,
            'to_section_id' => $data['section_id'] ?? $grievance->section_id,
            'from_officer_id' => $grievance->assigned_officer_id,
            'to_officer_id' => $data['officer_id'] ?? $grievance->assigned_officer_id,
            'acted_by' => $actor->id,
            'reason' => $data['reason'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function authorizeActor(GrievanceWorkflowStep $step, User $actor, Grievance $grievance): void
    {
        $isApprover = $step->approver_user_id
            ? $step->approver_user_id === $actor->id
            : ($step->role_name && $actor->hasRole($step->role_name));

        $permission = match ($step->action_type) {
            'allocate_division', 'allocate_section', 'screen_allocate' => 'grievances.allocate',
            'assign_officer' => 'grievances.assign',
            'prepare_resolution', 'investigation', 'final_approval', 'acknowledge', 'decision_approval' => 'grievances.resolve',
            'closure' => 'grievances.close',
            'escalation' => 'grievances.escalate',
            default => 'grievances.view',
        };

        if (! $isApprover
            || ! $actor->can($permission)
            || ($step->action_type === 'allocate_section'
                && $actor->division_id !== $grievance->division_id)
            || ($step->action_type === 'assign_officer'
                && $actor->section_id !== $grievance->section_id)
            || (($step->action_type === 'review' && $step->role_name === 'Helpdesk Officer'
                || in_array($step->action_type, ['prepare_resolution', 'investigation', 'acknowledge'], true))
                && $grievance->assigned_officer_id !== $actor->id)) {
            abort(403);
        }
    }

    private function notifyNextApprover(Grievance $grievance, ?GrievanceWorkflowStep $step): void
    {
        if (! $step) {
            return;
        }

        if ($step->action_type === 'review' && $step->role_name === 'Helpdesk Officer') {
            $approvers = User::query()->whereKey($grievance->assigned_officer_id)->get();
        } elseif (in_array($step->action_type, ['prepare_resolution', 'investigation', 'acknowledge'], true)) {
            $officers = User::query()->whereKey($grievance->assigned_officer_id)->get();

            Notification::send($officers, new GrievanceAssigned($grievance));

            return;
        } elseif ($step->approver_user_id) {
            $approvers = User::query()->whereKey($step->approver_user_id)->get();
        } else {
            $approverQuery = User::query()->whereHas('roles', fn ($query) => $query->where('name', $step->role_name));

            if ($step->action_type === 'allocate_section') {
                $approverQuery->where('division_id', $grievance->division_id);
            } elseif ($step->action_type === 'assign_officer') {
                $approverQuery->where('section_id', $grievance->section_id);
            }

            $approvers = $approverQuery->get();
        }

        Notification::send($approvers, new GrievanceWorkflowActionRequired($grievance, $step));
    }

    private function statusForStep(GrievanceWorkflowStep $step): string
    {
        return match ($step->action_type) {
            'allocate_division', 'review', 'informal_resolution', 'screen_allocate' => 'submitted',
            'allocate_section' => 'allocated_division',
            'assign_officer' => 'allocated_section',
            'acknowledge' => 'assigned_officer',
            'prepare_resolution' => 'assigned_officer',
            'investigation' => 'in_progress',
            'final_approval', 'decision_approval' => 'pending_approval',
            'closure' => 'resolved',
            'escalation' => 'escalated',
            'monitoring' => 'resolved',
            default => 'submitted',
        };
    }
}
