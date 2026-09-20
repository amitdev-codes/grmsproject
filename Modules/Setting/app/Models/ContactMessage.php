<?php

namespace Modules\Setting\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'is_anonymous',
        'name',
        'email',
        'mobile',
        'subject',
        'message',
        'status',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
    ];
}
