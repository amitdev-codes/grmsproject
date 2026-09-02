<?php

namespace Modules\Master\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Master\Database\Factories\DistrictFactory;

class District extends Model
{
    protected $fillable = [
        'code',
        'name',
        'name_st',
    ];
}
