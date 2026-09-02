<?php

namespace Modules\Master\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    protected $table = 'divisions';
    protected $fillable = [
        'code',
        'name',
        'name_st',
        'description',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }
}
