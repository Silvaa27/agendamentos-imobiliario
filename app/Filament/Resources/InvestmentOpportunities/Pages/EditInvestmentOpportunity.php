<?php

namespace App\Filament\Resources\InvestmentOpportunities\Pages;

use App\Filament\Resources\InvestmentOpportunities\InvestmentOpportunityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInvestmentOpportunity extends EditRecord
{
    protected static string $resource = InvestmentOpportunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
