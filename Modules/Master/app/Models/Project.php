<?php

namespace Modules\Master\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Modules\Master\Database\Factories\ProjectFactory;

class Project extends Model
{

    use HasPublicUlid, SoftDeletes;
    protected $table = 'projects';
    protected $fillable = [
        'code',
        'title',
        'description',
        'project_type_id',
        'district_id',
        'division_id',
        'consultant_id',
        'contractor_id',
        'contract_amount',
        'currency',
        'starts_on',
        'expected_completion_on',
        'completed_on',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'contract_amount' => 'decimal:2',
            'starts_on' => 'date',
            'expected_completion_on' => 'date',
            'completed_on' => 'date',
            'metadata' => 'array',
        ];
    }

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class, 'project_type_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class, 'consultant_id');
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class, 'contractor_id');
    }

    public function grievances(): HasMany
    {
        return $this->hasMany(\Modules\Grievance\Models\Grievance::class, 'project_id');
    }
}
