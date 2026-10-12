<?php

namespace Modules\Grievance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrievanceWorkflowStep extends Model
{
    protected $fillable = [
        'workflow_key',
        'step_number',
        'name',
        'description',
        'action_type',
        'role_name',
        'approver_user_id',
        'approval_action',
        'approval_target_step',
        'rejection_action',
        'rejection_target_step',
        'is_final_approval',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'step_number' => 'integer',
            'approver_user_id' => 'integer',
            'approval_target_step' => 'integer',
            'rejection_target_step' => 'integer',
            'is_final_approval' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function approverUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }
}
