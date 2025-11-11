<?php

namespace App\Filament\Resources\AdvertiseResponses\Pages;

use App\Filament\Resources\AdvertiseResponses\AdvertiseResponseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdvertiseResponses extends ListRecords
{
    protected static string $resource = AdvertiseResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
