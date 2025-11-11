<?php

namespace App\Filament\Resources\Advertises;

use App\Filament\Resources\Advertises\Pages\CreateAdvertise;
use App\Filament\Resources\Advertises\Pages\EditAdvertise;
use App\Filament\Resources\Advertises\Pages\ListAdvertises;
use App\Filament\Resources\Advertises\Schemas\AdvertiseForm;
use App\Filament\Resources\Advertises\Tables\AdvertisesTable;
use App\Models\Advertise;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdvertiseResource extends Resource
{
    protected static ?string $model = Advertise::class;

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
        ];
    }
}
