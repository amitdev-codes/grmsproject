<?php

namespace Modules\UserManagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;


class Role extends Model
{

    protected $fillable = [];

    public function users()
    {
        return $this->morphedByMany(User::class, 'model', 'model_has_roles');
    }
}
