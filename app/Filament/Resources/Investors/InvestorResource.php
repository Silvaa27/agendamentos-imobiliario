<?php

namespace App\Filament\Resources\Investors;

use App\Filament\Resources\Investors\Pages\CreateInvestor;
use App\Filament\Resources\Investors\Pages\EditInvestor;
use App\Filament\Resources\Investors\Pages\ListInvestors;
use App\Filament\Resources\Investors\Schemas\InvestorForm;
use App\Filament\Resources\Investors\Tables\InvestorsTable;
use App\Models\Investor;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvestorResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return InvestorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvestorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvestors::route('/'),
            'create' => CreateInvestor::route('/create'),
            'edit' => EditInvestor::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // Filtrar apenas usuários com role de investidor
        $query->role('investidor');

        // Se pode ver tudo, retorna todos investidores
        if ($user->can('view_all_opportunities')) {
            return $query;
        }

        // Se não, só vê seu próprio perfil
        return $query->where('id', $user->id);
    }

}
