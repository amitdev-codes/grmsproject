<?php

namespace Modules\Master\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Section extends Model
{
    protected $fillable = [
        'code',
        'division_id',
        'name',
        'name_st',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }
}
