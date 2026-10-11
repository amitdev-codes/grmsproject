<?php

namespace Modules\Grievance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoiceGrievance extends Model
{
    protected $fillable = [
        'reference_no',
        'audio_path',
        'audio_mime',
        'duration_seconds',
        'language_selected',
        'language_detected',
        'transcript_original',
        'transcript_english',
        'contact_phone',
        'status',
        'assigned_officer_id',
        'grievance_id',
        'failure_reason',
    ];

    public function assignedOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_officer_id');
    }

    public function grievance(): BelongsTo
    {
        return $this->belongsTo(Grievance::class);
    }
}
