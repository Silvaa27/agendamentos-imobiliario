<?php

namespace App\Filament\Resources\Investors\Pages;

use App\Filament\Resources\Investors\InvestorResource;
use App\Filament\Resources\Investors\Schemas\InvestorForm;
use Filament\Resources\Pages\EditRecord;

class EditInvestor extends EditRecord
{
    protected static string $resource = InvestorResource::class;

    protected function beforeSave(): void
    {
        // Chamar beforeSave do InvestorForm
        $data = $this->form->getState();
        InvestorForm::beforeSave($data, $this->record);
    }

    protected function afterSave(): void
    {
        // Chamar afterSave do InvestorForm
        InvestorForm::afterSave($this->record, $this->data);
    }
}