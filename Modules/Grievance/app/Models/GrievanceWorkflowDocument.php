<?php

namespace Modules\Grievance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrievanceWorkflowDocument extends Model
{
    protected $fillable = [
        'grievance_workflow_action_id',
        'uploaded_by',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function action(): BelongsTo
    {
        return $this->belongsTo(GrievanceWorkflowAction::class, 'grievance_workflow_action_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
