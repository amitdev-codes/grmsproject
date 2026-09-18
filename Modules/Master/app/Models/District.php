<?php

namespace Modules\Master\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Grievance\Models\Grievance;

// use Modules\Master\Database\Factories\DistrictFactory;

class District extends Model
{
    use HasPublicUlid, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'name_st',
    ];

    public function grievances(): HasMany
    {
        return $this->hasMany(Grievance::class, 'district_id');
    }
}
