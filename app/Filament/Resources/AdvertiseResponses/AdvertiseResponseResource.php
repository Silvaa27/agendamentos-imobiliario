<?php

namespace App\Filament\Resources\AdvertiseResponses;

use App\Filament\Resources\AdvertiseResponses\Pages\CreateAdvertiseResponse;
use App\Filament\Resources\AdvertiseResponses\Pages\EditAdvertiseResponse;
use App\Filament\Resources\AdvertiseResponses\Pages\ListAdvertiseResponses;
use App\Filament\Resources\AdvertiseResponses\Schemas\AdvertiseResponseForm;
use App\Filament\Resources\AdvertiseResponses\Tables\AdvertiseResponsesTable;
use App\Models\AdvertiseAnswer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdvertiseResponseResource extends Resource
{
    protected static ?string $model = AdvertiseAnswer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AdvertiseResponseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdvertiseResponsesTable::configure($table);
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
            'index' => ListAdvertiseResponses::route('/'),
            'create' => CreateAdvertiseResponse::route('/create'),
            'edit' => EditAdvertiseResponse::route('/{record}/edit'),
        ];
    }
}
