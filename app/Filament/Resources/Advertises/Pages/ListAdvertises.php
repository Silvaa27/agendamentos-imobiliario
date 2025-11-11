<?php

namespace App\Filament\Resources\Advertises\Pages;

use App\Filament\Resources\Advertises\AdvertiseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdvertises extends ListRecords
{
    protected static string $resource = AdvertiseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
