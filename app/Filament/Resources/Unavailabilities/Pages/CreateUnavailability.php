<?php

namespace App\Filament\Resources\Unavailabilities\Pages;

use App\Filament\Resources\Unavailabilities\UnavailabilityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUnavailability extends CreateRecord
{
    protected static string $resource = UnavailabilityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if (isset($data['unavailability_type'])) {
            switch ($data['unavailability_type']) {
                case 'global':
                    $data['user_id'] = null; // Global - sem dono específico
                    $data['associatedUsers'] = [];
                    break;
                case 'shared':
                    // 🔥 NOVA LÓGICA: Para partilhadas, o user_id é null (sem dono específico)
                    // e apenas os utilizadores selecionados têm acesso
                    $data['user_id'] = null;
                    // associatedUsers mantém-se como está
                    break;
                case 'personal':
                default:
                    $data['user_id'] = $user->id; // Pessoal - o criador é o dono
                    $data['associatedUsers'] = [];
                    break;
            }

            unset($data['unavailability_type']);
        }

        return $data;
    }
}