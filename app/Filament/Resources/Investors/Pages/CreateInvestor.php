<?php

namespace App\Filament\Resources\Investors\Pages;

use App\Filament\Resources\Investors\InvestorResource;
use App\Filament\Resources\Investors\Schemas\InvestorForm;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

class CreateInvestor extends CreateRecord
{
    protected static string $resource = InvestorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Chamar beforeSave do InvestorForm
        InvestorForm::beforeSave($data);
        return $data;
    }

    protected function afterCreate(): void
    {
        // Chamar afterCreate do InvestorForm
        InvestorForm::afterCreate($this->record, $this->data);
    }
}