<?php

namespace Modules\Grievance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrievanceWorkflowAction extends Model
{
    protected $fillable = [
        'grievance_id',
        'from_step_id',
        'to_step_id',
        'actor_id',
        'decision',
        'reason',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function grievance(): BelongsTo
    {
        return $this->belongsTo(Grievance::class);
    }

    public function fromStep(): BelongsTo
    {
        return $this->belongsTo(GrievanceWorkflowStep::class, 'from_step_id');
    }

    public function toStep(): BelongsTo
    {
        return $this->belongsTo(GrievanceWorkflowStep::class, 'to_step_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(GrievanceWorkflowDocument::class);
    }
}
