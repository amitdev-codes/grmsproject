<?php

namespace Modules\Grievance\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenceSequence extends Model
{
    protected $fillable = ['scope', 'year', 'last_number'];
}
