<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGeneration extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'subject', 'word_count', 'tone', 'context', 'model',
        'prompt_tokens', 'completion_tokens', 'total_tokens',
        'cost_estimate', 'article_id',
    ];

    protected function casts(): array
    {
        return [
            'word_count' => 'integer',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'total_tokens' => 'integer',
            'cost_estimate' => 'decimal:6',
            'created_at' => 'datetime',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }
}
