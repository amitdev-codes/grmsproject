<?php

namespace Modules\Master\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Modules\Master\Database\Factories\GrievanceSlaPolicyFactory;

class GrievanceSlaPolicy extends Model
{
    use HasFactory;

    protected $table = 'grievance_sla_policies';
    protected $fillable = [
        'code',
        'name',
        'priority',
        'acknowledgement_hours',
        'resolution_hours',
        'use_business_hours',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'acknowledgement_hours' => 'integer',
            'resolution_hours' => 'integer',
            'use_business_hours' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function escalationRules(): HasMany
    {
        return $this->hasMany(GrievanceEscalationRule::class, 'grievance_sla_policy_id');
    }
}