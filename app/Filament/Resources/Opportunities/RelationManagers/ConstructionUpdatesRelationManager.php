<?php

namespace App\Filament\Resources\Opportunities\RelationManagers;

use App\Filament\Resources\Opportunities\OpportunityResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ConstructionUpdatesRelationManager extends RelationManager
{
    protected static string $relationship = 'constructionUpdates';

    protected static ?string $title = 'Construction Updates';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->label('Data')
                    ->required()
                    ->default(now()),

                TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255),

                Textarea::make('report')
                    ->label('Relatório/Descrição')
                    ->rows(5)
                    ->columnSpanFull(),

                TextInput::make('progress_percentage')
                    ->label('Progresso (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%'),

                SpatieMediaLibraryFileUpload::make('construction_photos')
                    ->label('Galeria de Fotos')
                    ->collection('photos')
                    ->multiple()
                    ->maxFiles(20)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Título')
                    ->limit(50),

                TextColumn::make('progress_percentage')
                    ->label('Progresso')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Responsável'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nova Atualização'), // Personaliza o botão
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}