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
            InvestorsRelationManager::class,
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

        // ⬇️ VERIFICA SE HÁ UTILIZADOR AUTENTICADO
        if (!$user) {
            return $query->whereRaw('1 = 0'); // Não mostra nada se não está autenticado
        }

        // Super admin vê tudo
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        // Se tem view_all_opportunities, vê tudo
        if ($user->can('view_all_opportunities')) {
            return $query;
        }

        // Se tem view_opportunities, vê só as suas/associadas OU partilhadas com acesso
        if ($user->can('view_opportunities')) {
            return $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id) // É dono da oportunidade
                    ->orWhereHas('associatedUsers', function ($subQuery) use ($user) {
                        $subQuery->where('user_id', $user->id); // Está associado
                    })
                    ->orWhereHas('investors', function ($subQuery) use ($user) {
                        $subQuery->where('investor_id', $user->id)
                            ->where('has_access', 1); // ← TEM ACESSO (has_access = 1)
                    });
            });
        }

        // Se não tem nenhuma permissão, não vê nada
        return $query->whereRaw('1 = 0');
    }
}
