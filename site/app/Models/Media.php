<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasUuids;

    protected $table = 'media';

    protected $fillable = [
        'slug', 'alt', 'folder', 'original_name', 'path', 'url',
        'mime_type', 'width', 'height', 'size',
    ];

    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'size' => 'integer',
        ];
    }

    public function getPublicUrl(): string
    {
        if ($this->url) {
            return $this->url;
        }

        return Storage::disk('public')->url($this->path);
    }
}
