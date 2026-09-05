<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ToolPage extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'category',
        'calculator_key',
        'title',
        'meta_title',
        'meta_description',
        'intro_content',
        'faq_content',
        'is_published',
        'is_indexable',
    ];

    protected function casts(): array
    {
        return [
            'faq_content' => 'array',
            'is_published' => 'boolean',
            'is_indexable' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeIndexable(Builder $query): Builder
    {
        return $query->where('is_indexable', true);
    }
}
