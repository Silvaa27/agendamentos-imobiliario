<?php

namespace App\Filament\Resources\BusinessHours\Pages;

use App\Filament\Resources\BusinessHours\BusinessHourResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBusinessHour extends EditRecord
{
    protected static string $resource = BusinessHourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (array_key_exists('user_id', $data)) {
            if ($data['user_id'] === null) {
                $data['user_id'] = '';
            } else {
                $data['user_id'] = (string) $data['user_id'];
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {

        if (isset($data['user_id'])) {
            if ($data['user_id'] === '') {
                $data['user_id'] = null; 
            } else {
                $data['user_id'] = (int) $data['user_id'];
            }
        }

        return $data;
    }
}