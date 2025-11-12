<?php

namespace App\Filament\Resources\BusinessHours\Pages;

use App\Filament\Resources\BusinessHours\BusinessHourResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBusinessHour extends CreateRecord
{
    protected static string $resource = BusinessHourResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 🔥 CONVERTE STRING VAZIA PARA NULL (HORÁRIO DEFAULT)
        if (isset($data['user_id']) && $data['user_id'] === '') {
            $data['user_id'] = null;
        }

        \Log::info('DEBUG - Dados antes de criar BusinessHour:', $data);

        return $data;
    }
}