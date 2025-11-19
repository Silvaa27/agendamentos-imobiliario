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

        if ($this->record->user_id === null) {
            $data['unavailability_type'] = 'global';
        } elseif (count($associatedUsers) > 0) {
            $data['unavailability_type'] = 'shared';
        } elseif ($this->record->user_id !== $user->id) {
            $data['unavailability_type'] = 'other_user';
            $data['other_user_id'] = $this->record->user_id;
        } else {
            $data['unavailability_type'] = 'personal';
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();

        if (isset($data['unavailability_type'])) {
            switch ($data['unavailability_type']) {
                case 'global':
                    $data['user_id'] = null;
                    $data['associatedUsers'] = [];
                    break;

                case 'shared':
                    $data['user_id'] = $user->id;
                    break;

                case 'other_user':
                    $data['user_id'] = $data['other_user_id'] ?? $user->id;
                    $data['associatedUsers'] = [];
                    break;

                case 'personal':
                default:
                    $data['user_id'] = $user->id;
                    $data['associatedUsers'] = [];
                    break;
            }

            unset($data['unavailability_type'], $data['other_user_id']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getState();
        $associatedUsers = $data['associatedUsers'] ?? [];

        $this->record->associatedUsers()->sync($associatedUsers);
    }
}