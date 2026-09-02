<?php

namespace Modules\Grievance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrievanceEscalation extends Model
{
    protected $fillable = [
        'grievance_id',
        'escalation_level',
        'escalated_to',
        'sla_breached_at',
        'escalated_at',
        'reason',
        'resolved',
    ];

    protected function casts(): array
    {
        return [
            'sla_breached_at' => 'datetime',
            'escalated_at' => 'datetime',
            'resolved' => 'boolean',
        ];
    }

    public function grievance(): BelongsTo
    {
        return $this->belongsTo(Grievance::class);
    }

    public function escalatedOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_to');
    }
}
