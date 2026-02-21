<?php

namespace App\Filament\Resources\SensResource\Pages;

use App\Filament\Resources\SensResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSens extends CreateRecord
{
    protected static string $resource = SensResource::class;

    public static bool $formActionsAreSticky = true;
}
