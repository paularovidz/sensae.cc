<?php

namespace App\View\Components;

use App\Models\Media;
use Illuminate\View\Component;
use Illuminate\View\View;

class Image extends Component
{
    public ?Media $media;

    public function __construct(
        public string $slug,
        public ?string $alt = null,
    ) {
        $this->media = Media::where('slug', $slug)->first();
    }

    public function render(): View|string
    {
        if (!$this->media) {
            return '';
        }

        return view('components.image');
    }
}
