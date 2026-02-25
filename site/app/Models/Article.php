<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasUuids;

    protected $fillable = [
        'title', 'slug', 'content', 'image', 'excerpt',
        'author', 'categories', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'categories' => 'array',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
