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
        \Log::info('DEBUG - Dados da BD antes de preencher:', $data);

        // 🔥 CONVERTE NULL PARA STRING VAZIA (DEFAULT) E USER_ID PARA STRING
        if (array_key_exists('user_id', $data)) {
            if ($data['user_id'] === null) {
                $data['user_id'] = ''; // NULL → '' (Default)
            } else {
                $data['user_id'] = (string) $data['user_id']; // ID → string
            }
        }

        \Log::info('DEBUG - Dados convertidos para formulário:', $data);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        \Log::info('DEBUG - Dados do formulário antes de guardar:', $data);

        // 🔥 CONVERTE STRING VAZIA PARA NULL E STRINGS PARA INT
        if (isset($data['user_id'])) {
            if ($data['user_id'] === '') {
                $data['user_id'] = null; // '' → NULL (Default)
            } else {
                $data['user_id'] = (int) $data['user_id']; // string → int
            }
        }

        \Log::info('DEBUG - Dados convertidos para BD:', $data);

        return $data;
    }
}