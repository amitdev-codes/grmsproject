<?php

namespace Modules\Grievance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only audit trail. No `updated_at` column by design —
 * do not add an update/delete admin action for this model.
 */
#[Fillable(['from_status', 'grievance_id', 'to_status', 'is_status', 'actor_id', 'actor_role','reason'])]
class GrievanceStatusHistory extends Model
{
    public $timestamps = false;

    protected $table = 'grievance_status_histories';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function grievance(): BelongsTo
    {
        return $this->belongsTo(Grievance::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
