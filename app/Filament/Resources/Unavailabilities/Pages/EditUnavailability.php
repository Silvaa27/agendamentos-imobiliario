<?php

namespace App\Filament\Resources\Unavailabilities\Pages;

use App\Filament\Resources\Unavailabilities\UnavailabilityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUnavailability extends EditRecord
{
    protected static string $resource = UnavailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = auth()->user();

        // 🔥 CARREGA OS UTILIZADORES ASSOCIADOS
        $this->record->load('associatedUsers');
        $associatedUsers = $this->record->associatedUsers->pluck('id')->toArray();

        $data['associatedUsers'] = $associatedUsers;

        // 🔥 LÓGICA CORRIGIDA PARA DETERMINAR O TIPO
        if ($this->record->user_id === null && count($associatedUsers) > 0) {
            $data['unavailability_type'] = 'shared';
        } elseif ($this->record->user_id === null) {
            $data['unavailability_type'] = 'global';
        } else {
            $data['unavailability_type'] = 'personal';
        }

        \Log::info('DEBUG - mutateFormDataBeforeFill:', [
            'record_id' => $this->record->id,
            'user_id' => $this->record->user_id,
            'associatedUsers' => $associatedUsers,
            'unavailability_type' => $data['unavailability_type']
        ]);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();

        \Log::info('DEBUG - mutateFormDataBeforeSave - Dados recebidos:', $data);

        if (isset($data['unavailability_type'])) {
            switch ($data['unavailability_type']) {
                case 'global':
                    $data['user_id'] = null;
                    $data['associatedUsers'] = [];
                    break;
                case 'shared':
                    $data['user_id'] = null; // 🔥 PARTILHADA TEM user_id = null
                    // associatedUsers mantém-se como está
                    break;
                case 'personal':
                default:
                    $data['user_id'] = $user->id;
                    $data['associatedUsers'] = [];
                    break;
            }

            unset($data['unavailability_type']);
        }

        \Log::info('DEBUG - mutateFormDataBeforeSave - Dados processados:', $data);

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getState();
        $associatedUsers = $data['associatedUsers'] ?? [];

        \Log::info('DEBUG - afterSave - associatedUsers para sincronizar:', $associatedUsers);

        // 🔥 SINCRONIZA OS UTILIZADORES ASSOCIADOS
        $this->record->associatedUsers()->sync($associatedUsers);

        \Log::info('DEBUG - Utilizadores sincronizados na edição com sucesso!');
    }
}