<?php

namespace App\Filament\Resources\Opportunities;

use App\Filament\Resources\Opportunities\Pages\CreateOpportunity;
use App\Filament\Resources\Opportunities\Pages\EditOpportunity;
use App\Filament\Resources\Opportunities\Pages\ListOpportunities;
use App\Filament\Resources\Opportunities\RelationManagers\ConstructionUpdatesRelationManager;
use App\Filament\Resources\Opportunities\RelationManagers\InvoicesRelationManager;
use App\Filament\Resources\Opportunities\Schemas\OpportunityForm;
use App\Filament\Resources\Opportunities\Tables\OpportunitiesTable;
use App\Filament\Resources\OpportunityResource\RelationManagers\InvestorsRelationManager;
use App\Models\Opportunity;
use App\Models\OpportunityInvestor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OpportunityResource extends Resource
{
    protected static ?string $model = Opportunity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return OpportunityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OpportunitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ConstructionUpdatesRelationManager::class,
            InvoicesRelationManager::class,
        ];
    }
    public static function getPages(): array
    {
        return [
            'index' => ListOpportunities::route('/'),
            'create' => CreateOpportunity::route('/create'),
            'edit' => EditOpportunity::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (!$user->can('view_all_opportunities')) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('associatedUsers', function ($subQuery) use ($user) {
                        $subQuery->where('user_id', $user->id);
                    });
            });
        }

        return $query;
    }
}
