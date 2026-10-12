<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Grievance\Models\Grievance;

class StoreResolutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $grievance = Grievance::query()
            ->with('currentWorkflowStep')
            ->find($this->integer('grievance_id'));
        $step = $grievance?->currentWorkflowStep;

        if (! $user || ! $grievance || ! $step || ! $step->is_active
            || ! in_array($step->action_type, ['prepare_resolution', 'investigation'], true)
            || ! in_array($grievance->status, ['in_progress', 'escalated'], true)
            || $grievance->assigned_officer_id !== $user->id
            || ! $user->can('grievances.view')
            || ! $user->can('grievances.resolve')) {
            return false;
        }

        return $step->approver_user_id
            ? $step->approver_user_id === $user->id
            : ($step->role_name && $user->hasRole($step->role_name));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'grievance_id' => ['required', 'integer', 'exists:grievances,id'],
            'document_type' => ['required', 'in:note,memo,report'],
            'resolution_text' => ['required', 'string', 'max:50000'],
        ];
    }
}
