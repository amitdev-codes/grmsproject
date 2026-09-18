<?php

namespace Modules\Grievance\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'name_en', 'name_st', 'slug', 'icon', 'is_sensitive', 'is_active'])]
class GrievanceCategory extends Model
{
    use HasPublicUlid, SoftDeletes;
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_sensitive' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function grievances(): HasMany
    {
        return $this->hasMany(Grievance::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
