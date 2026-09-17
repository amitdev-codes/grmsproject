<?php

namespace Modules\Master\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Modules\Master\Database\Factories\DistrictFactory;

class District extends Model
{
    protected $fillable = [
        'code',
        'name',
        'name_st',
    ];

    public function grievances(): HasMany
    {
        return $this->hasMany(\Modules\Grievance\Models\Grievance::class, 'district_id');
    }
}