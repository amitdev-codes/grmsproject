<?php

namespace Modules\Master\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Modules\Master\Database\Factories\ServiceProviderFactory;

class ServiceProvider extends Model
{
    use HasFactory;

    protected $table = 'service_providers';
    protected $fillable = [
        'code',
        'name',
        'provider_type',
        'contact_name',
        'phone',
        'email',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function projectsAsConsultant(): HasMany
    {
        return $this->hasMany(Project::class, 'consultant_id');
    }

    public function projectsAsContractor(): HasMany
    {
        return $this->hasMany(Project::class, 'contractor_id');
    }
}