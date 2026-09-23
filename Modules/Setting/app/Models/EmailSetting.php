<?php

namespace Modules\Setting\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSetting extends Model
{
    protected $hidden = ['password'];

    protected $fillable = [
        'name', 'mailer', 'host', 'port', 'encryption', 'username',
        'password', 'from_address', 'from_name', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'password' => 'encrypted',
            'is_active' => 'boolean',
        ];
    }
}
