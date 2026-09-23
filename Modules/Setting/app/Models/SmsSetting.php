<?php

namespace Modules\Setting\Models;

use Illuminate\Database\Eloquent\Model;

class SmsSetting extends Model
{
    protected $hidden = ['api_key', 'api_secret'];

    protected $fillable = [
        'name', 'provider', 'base_url', 'api_key', 'api_secret',
        'sender_id', 'default_country_code', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'api_secret' => 'encrypted',
            'is_active' => 'boolean',
        ];
    }
}
