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

        \Log::info('DEBUG CREATE - Dados recebidos:', $data);

        if (isset($data['unavailability_type'])) {
            switch ($data['unavailability_type']) {
                case 'global':
                    $data['user_id'] = null;
                    $data['associatedUsers'] = [];
                    break;
                case 'shared':
                    // 🔥 PARTILHADA: user_id do criador + associatedUsers na pivot table
                    $data['user_id'] = $user->id;
                    // associatedUsers mantém-se - será sincronizado na pivot table
                    break;
                case 'personal':
                default:
                    $data['user_id'] = $user->id;
                    $data['associatedUsers'] = [];
                    break;
            }

            unset($data['unavailability_type']);
        }

        \Log::info('DEBUG CREATE - Dados processados:', $data);

        return $data;
    }

    protected function afterCreate(): void
    {
        $data = $this->form->getState();
        $associatedUsers = $data['associatedUsers'] ?? [];

        \Log::info('DEBUG CREATE - afterCreate - associatedUsers para sincronizar:', $associatedUsers);

        // 🔥 SINCRONIZA OS UTILIZADORES ASSOCIADOS APÓS A CRIAÇÃO
        $this->record->associatedUsers()->sync($associatedUsers);

        \Log::info('DEBUG CREATE - Utilizadores sincronizados na criação com sucesso!');
    }
}