<?php

namespace App\Filament\Resources\Advertises\Pages;

use App\Filament\Resources\Advertises\AdvertiseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAdvertise extends EditRecord
{
    protected static string $resource = AdvertiseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
