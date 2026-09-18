<?php

namespace Modules\Grievance\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrievanceChannel extends Model
{
    protected $fillable = ['code', 'name', 'is_active'];
    use HasPublicUlid, SoftDeletes;
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function grievances(): HasMany
    {
        return $this->hasMany(Grievance::class, 'channel_id');
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
