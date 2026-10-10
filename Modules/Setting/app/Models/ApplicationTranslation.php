<?php

namespace Modules\Setting\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationTranslation extends Model
{
    protected $fillable = ['translation_key', 'english_text', 'sesotho_text'];
}
