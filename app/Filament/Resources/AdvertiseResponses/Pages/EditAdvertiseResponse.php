<?php

namespace App\Filament\Resources\AdvertiseResponses\Pages;

use App\Filament\Resources\AdvertiseResponses\AdvertiseResponseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAdvertiseResponse extends EditRecord
{
    protected static string $resource = AdvertiseResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
