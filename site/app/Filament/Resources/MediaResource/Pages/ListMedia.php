<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Resources\Pages\Page;

class ListMedia extends Page
{
    protected static string $resource = MediaResource::class;

    protected string $view = 'filament.pages.media-library';

    public function getTitle(): string
    {
        return 'Médiathèque';
    }
}
