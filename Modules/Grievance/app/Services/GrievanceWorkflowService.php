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
                $decision = $data['decision'];
                $reason = $data['reason'] ?? null;

                if ($decision === 'acknowledge') {
                    if ($step->action_type !== 'prepare_resolution' || $fromStatus !== 'assigned_officer') {
                        throw ValidationException::withMessages(['decision' => 'This grievance cannot be acknowledged at its current workflow step.']);
                    }

                    $status = 'in_progress';
                    $targetStep = $step;
                    $actionData = [];
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

                $action = $lockedGrievance->workflowActions()->create([
                    'from_step_id' => $fromStep->id,
                    'to_step_id' => $targetStep?->id,
                    'actor_id' => $actor->id,
                    'decision' => $decision,
                    'reason' => $reason,
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

                if ($decision !== 'acknowledge') {
                    $this->notifyNextApprover($lockedGrievance, $targetStep);
                }

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
                $officer = User::query()->whereKey((int) $data['officer_id'])->firstOrFail();
                if (! $officer->status
                    || ! $officer->hasRole('Helpdesk Officer')
                    || ($grievance->section_id
                        && $officer->section_id !== null
                        && $officer->section_id !== $grievance->section_id)) {
                    throw ValidationException::withMessages(['officer_id' => 'Select an active Helpdesk Officer assigned to this section or not yet assigned to a section.']);
                }
                $actionData['assigned_officer_id'] = $officer->id;
                break;
            case 'prepare_resolution':
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

        $nextStep = GrievanceWorkflowStep::query()
            ->where('workflow_key', 'default')
            ->where('is_active', true)
            ->where('step_number', '>', $step->step_number)
            ->orderBy('step_number')
            ->first();

        if (! $nextStep) {
            throw ValidationException::withMessages(['workflow' => 'There is no active next workflow level.']);
        }

        $actionData['current_workflow_step_id'] = $nextStep->id;

        return [$nextStep, $this->statusForStep($nextStep), $actionData];
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
            'allocate_division', 'allocate_section' => 'allocated',
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
            'allocate_division', 'allocate_section' => 'grievances.allocate',
            'assign_officer' => 'grievances.assign',
            'prepare_resolution', 'final_approval' => 'grievances.resolve',
            default => 'grievances.view',
        };

        if (! $isApprover
            || ! $actor->can($permission)
            || ($step->action_type === 'allocate_section'
                && $actor->division_id !== $grievance->division_id)
            || ($step->action_type === 'assign_officer'
                && $actor->section_id !== $grievance->section_id)
            || ($step->action_type === 'prepare_resolution' && $grievance->assigned_officer_id !== $actor->id)) {
            abort(403);
        }
    }

    private function notifyNextApprover(Grievance $grievance, ?GrievanceWorkflowStep $step): void
    {
        if (! $step) {
            return;
        }

        if ($step->action_type === 'prepare_resolution') {
            $officers = User::query()->whereKey($grievance->assigned_officer_id)->get();

            Notification::send($officers, new GrievanceAssigned($grievance));

            return;
        }

        if ($step->approver_user_id) {
            $approvers = User::query()->whereKey($step->approver_user_id)->get();
        } else {
            $approverQuery = User::role($step->role_name);

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
            'allocate_division', 'review' => 'submitted',
            'allocate_section' => 'allocated_division',
            'assign_officer' => 'allocated_section',
            'prepare_resolution' => 'assigned_officer',
            'final_approval' => 'pending_approval',
            default => 'submitted',
        };
    }
}
