<?php

namespace App\Filament\Resources\Advertises;

use App\Filament\Resources\Advertises\Pages\CreateAdvertise;
use App\Filament\Resources\Advertises\Pages\EditAdvertise;
use App\Filament\Resources\Advertises\Pages\ListAdvertises;
use App\Filament\Resources\Advertises\Pages\ViewAdvertise;
use App\Filament\Resources\Advertises\Schemas\AdvertiseForm;
use App\Filament\Resources\Advertises\Tables\AdvertisesTable;
use App\Models\Advertise;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdvertiseResource extends Resource
{
    protected static ?string $model = Advertise::class;
    protected static ?string $permissionPrefix = 'Advertise';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $modelLabel = 'Anúncio';

    public static function form(Schema $schema): Schema
    {
        return AdvertiseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdvertisesTable::configure($table);
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
            'index' => ListAdvertises::route('/'),
            'create' => CreateAdvertise::route('/create'),
            'edit' => EditAdvertise::route('/{record}/edit'),
            'view' => ViewAdvertise::route('/{record}/view'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (!$user->can('view_all_advertise')) {
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