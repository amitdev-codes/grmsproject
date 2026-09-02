<?php

namespace Modules\Grievance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Master\Models\District;
use Modules\Master\Models\Division;
use Modules\Master\Models\Section;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Grievance extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    public const string STATUS_SUBMITTED = 'submitted';

    public const string MEDIA_COLLECTION = 'attachments';

    protected $fillable = [
        'reference_no', 'grievance_category_id', 'channel_id', 'district_id',
        'division_id', 'section_id', 'assigned_officer_id',
        'complainant_id', 'complainant_name', 'complainant_phone', 'complainant_email',
        'is_anonymous', 'description', 'location_description', 'metadata',
        'raw_payload', 'ussd_session_id', 'status', 'priority',
        'registered_by', 'acknowledged_at', 'sla_due_at', 'satisfaction_rating',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'metadata' => 'array',
            'raw_payload' => 'array',
            'acknowledged_at' => 'datetime',
            'sla_due_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION)
            ->acceptsMimeTypes([
                'image/jpeg', 'image/png', 'image/webp',
                'application/pdf',
                'video/mp4', 'video/quicktime',
                'audio/mpeg', 'audio/wav', 'audio/mp4', 'audio/x-m4a',
            ]);
    }
    public function messages(): HasMany
    {
        return $this->hasMany(GrievanceMessage::class);
    }
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)->height(300)
            ->performOnCollections(self::MEDIA_COLLECTION)
            ->nonQueued();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(GrievanceCategory::class, 'grievance_category_id');
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(GrievanceChannel::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
    public function assignedOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class,'assigned_officer_id');
    }

    public function complainant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'complainant_id');
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(GrievanceStatusHistory::class);
    }
}
