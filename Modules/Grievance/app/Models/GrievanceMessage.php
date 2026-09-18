<?php

namespace Modules\Grievance\Models;

use App\Models\Concerns\HasPublicUlid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrievanceMessage extends Model
{
    use HasPublicUlid, SoftDeletes;
    protected $fillable = ['grievance_id', 'sender', 'user_id', 'body'];
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_internal' => 'boolean'];
    }

    public function grievance(): BelongsTo
    {
        return $this->belongsTo(Grievance::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

