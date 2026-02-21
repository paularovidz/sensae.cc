<?php

namespace App\Filament\Forms\Components;

use App\Models\Media;
use Filament\Forms\Components\Field;

class MediaPicker extends Field
{
    protected string $view = 'filament.forms.components.media-picker';

    public function getPreviewUrl(): ?string
    {
        $slug = $this->getState();
        if (!$slug) {
            return null;
        }

        $media = Media::where('slug', $slug)->first();

        return $media?->getPublicUrl();
    }
}
