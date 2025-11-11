<?php

namespace App\Filament\Resources\AdvertiseResponses\Pages;

use App\Filament\Resources\AdvertiseResponses\AdvertiseResponseResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAdvertiseResponse extends ViewRecord
{
    protected static string $resource = AdvertiseResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
