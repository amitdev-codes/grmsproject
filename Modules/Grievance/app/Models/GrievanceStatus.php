<?php

namespace Modules\Grievance\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class GrievanceStatus extends Model
{
    use HasPublicUlid, SoftDeletes;
    protected $fillable = [];

}
