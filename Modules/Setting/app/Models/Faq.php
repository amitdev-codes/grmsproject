<?php

namespace Modules\Setting\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'faqs';

    protected $fillable = [
        'question_en',
        'question_st',
        'answer_en',
        'answer_st',
        'sort_order',
        'is_published',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_published' => 'boolean',
    ];

    public function scopePublished($query): void
    {
        $query->where('is_published', true);
    }
}
