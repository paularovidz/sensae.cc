<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasUuids;

    protected $fillable = [
        'question', 'answer', 'category', 'order', 'is_published',
    ];

    /**
     * Strip <p> tags wrapped inside <li> (TipTap artifact).
     */
    public function getAnswerAttribute(?string $value): ?string
    {
        if (!$value) return $value;

        return preg_replace('#<li>\s*<p>(.*?)</p>\s*</li>#s', '<li>$1</li>', $value);
    }

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
