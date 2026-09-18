<?php

namespace Modules\Master\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

// use Modules\Master\Database\Factories\ProjectTypeFactory;

class ProjectType extends Model
{
    use HasPublicUlid, SoftDeletes;

    protected $table = 'project_types';
    protected $fillable = [
        'code',
        'name',
        'name_st',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'project_type_id');
    }
}
