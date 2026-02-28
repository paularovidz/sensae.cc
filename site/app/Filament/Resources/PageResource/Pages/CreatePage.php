<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    public static bool $formActionsAreSticky = true;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($this->data['html_mode']) && isset($this->data['content_raw'])) {
            $data['content'] = $this->data['content_raw'];
        }

        return $data;
    }
}
