<?php

namespace Modules\Master\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

// use Modules\Master\Database\Factories\GrievanceEscalationRuleFactory;

class GrievanceEscalationRule extends Model
{
    use HasPublicUlid, SoftDeletes;

    protected $table = 'grievance_escalation_rules';
    protected $fillable = [
        'grievance_sla_policy_id',
        'escalation_level',
        'breach_after_hours',
        'extension_hours',
        'target_role',
        'requires_manual_review',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'escalation_level' => 'integer',
            'breach_after_hours' => 'integer',
            'extension_hours' => 'integer',
            'requires_manual_review' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function slaPolicy(): BelongsTo
    {
        return $this->belongsTo(GrievanceSlaPolicy::class, 'grievance_sla_policy_id');
    }
}
