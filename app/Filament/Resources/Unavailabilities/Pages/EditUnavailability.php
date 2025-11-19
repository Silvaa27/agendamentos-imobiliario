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

        $this->record->load('associatedUsers');
        $associatedUsers = $this->record->associatedUsers->pluck('id')->toArray();

        $data['associatedUsers'] = $associatedUsers;

        // 🔥 LÓGICA CORRIGIDA - ORDEM IMPORTANTE!
        if ($this->record->user_id === null) {
            // user_id = null → GLOBAL (independentemente de ter associatedUsers)
            $data['unavailability_type'] = 'global';
        } elseif (count($associatedUsers) > 0) {
            // user_id NÃO é null E tem associatedUsers → PARTILHADA
            $data['unavailability_type'] = 'shared';
        } else {
            // user_id NÃO é null E NÃO tem associatedUsers → PESSOAL
            $data['unavailability_type'] = 'personal';
        }

        \Log::info('DEBUG - mutateFormDataBeforeFill - TIPO DETETADO:', [
            'record_id' => $this->record->id,
            'user_id' => $this->record->user_id,
            'associatedUsers_count' => count($associatedUsers),
            'associatedUsers' => $associatedUsers,
            'unavailability_type' => $data['unavailability_type'],
            'is_global' => $this->record->user_id === null,
            'is_shared' => $this->record->user_id !== null && count($associatedUsers) > 0,
            'is_personal' => $this->record->user_id !== null && count($associatedUsers) === 0,
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