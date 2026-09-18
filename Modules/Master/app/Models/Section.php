<?php

namespace Modules\Master\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use HasPublicUlid, SoftDeletes;

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
