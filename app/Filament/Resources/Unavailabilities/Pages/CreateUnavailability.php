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

    protected function afterCreate(): void
    {
        $data = $this->form->getState();
        $associatedUsers = $data['associatedUsers'] ?? [];

        $this->record->associatedUsers()->sync($associatedUsers);
    }
}