<?php

namespace App\Filament\Resources\SensResource\Pages;

use App\Filament\Resources\SensResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSens extends ListRecords
{
    protected static string $resource = SensResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
