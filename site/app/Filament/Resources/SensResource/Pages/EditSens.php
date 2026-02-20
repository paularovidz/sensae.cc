<?php

namespace App\Filament\Resources\SensResource\Pages;

use App\Filament\Resources\SensResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSens extends EditRecord
{
    protected static string $resource = SensResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
