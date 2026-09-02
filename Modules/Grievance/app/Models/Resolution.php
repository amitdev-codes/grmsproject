<?php

namespace Modules\Grievance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resolution extends Model
{
    protected $guarded = ['id'];
    protected $fillable = [
        'grievance_id', 'proposed_by', 'approved_by', 'resolution_text',
        'approved_at', 'complainant_confirmed_at', 'rejected_reason',
    ];
    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'complainant_confirmed_at' => 'datetime',
        ];
    }

    public function grievance(): BelongsTo
    {
        return $this->belongsTo(Grievance::class);
    }

    public function proposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
