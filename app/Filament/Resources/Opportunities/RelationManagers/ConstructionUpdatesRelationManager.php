<?php

namespace App\Filament\Resources\Opportunities\RelationManagers;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Slider;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\RelationManagers\RelationManager;

class ConstructionUpdatesRelationManager extends RelationManager
{
    protected static string $relationship = 'constructionUpdates';
    protected static ?string $title = 'Atualizações de Obra';
    protected static ?string $modelLabel = 'Acompanhamento de Obra';
    protected static ?string $pluralModelLabel = 'Atualizações de Obra';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informações Básicas')
                    ->description('Detalhes principais da atualização')
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('Responsável')
                            ->options(
                                User::query()
                                    ->pluck('name', 'id')
                            )
                            ->default(auth()->id())
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),

                        DatePicker::make('date')
                            ->label('Data da Atualização')
                            ->required()
                            ->default(now())
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),

                        TextInput::make('title')
                            ->label('Título da Atualização')
                            ->placeholder('Ex: Conclusão da fase de alvenaria')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Slider::make('progress_percentage')
                            ->label('Progresso Geral da Obra')
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(1)
                            ->tooltips(RawJs::make(<<<'JS'
        `${$value}%`
        JS))
                            ->fillTrack()
                            ->columnSpan(2),
                    ]),

                Section::make('Relatório Detalhado')
                    ->description('Descrição completa dos trabalhos realizados')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Textarea::make('report')
                            ->label('Relatório Técnico')
                            ->placeholder('Descreva em detalhe os trabalhos realizados, problemas encontrados e soluções aplicadas...')
                            ->rows(8)
                            ->required()
                            ->columnSpanFull()
                            ->extraInputAttributes(['style' => 'min-height: 200px']),
                    ]),

                SpatieMediaLibraryFileUpload::make('photos')
                    ->label('Galeria de Fotos')
                    ->collection('photos')
                    ->disk('public')
                    ->multiple()
                    ->maxFiles(20)
                    ->appendFiles()
                    ->reorderable()
                    ->panelLayout('grid')
                    ->openable()
                    ->downloadable()
                    ->responsiveImages()
                    ->image()
                    ->imageEditor()
                    ->columnSpanFull()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                ImageColumn::make('first_photo')
                    ->label('Foto')
                    ->getStateUsing(function ($record) {
                        return $record->getFirstMediaUrl('photos');
                    })
                    ->circular()
                    ->defaultImageUrl(url('/images/default-construction.jpg'))
                    ->size(50)
                    ->disk('public'), // ← Especificar o disk

                TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable()
                    ->description(fn($record) => $record->date->diffForHumans())
                    ->toggleable(),

                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    })
                    ->wrap()
                    ->weight('medium'),

                TextColumn::make('user.name')
                    ->label('Responsável')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                IconColumn::make('has_photos')
                    ->label('Fotos')
                    ->boolean()
                    ->trueIcon('heroicon-o-camera')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->getStateUsing(fn($record) => $record->getMedia('photos')->count() > 0) // ← Mesma collection
                    ->alignCenter(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Responsável')
                    ->options(
                        User::whereHas('constructionUpdates')
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->searchable()
                    ->preload(),

                Filter::make('date')
                    ->label('Período')
                    ->form([
                        DatePicker::make('date_from')
                            ->label('De'),
                        DatePicker::make('date_until')
                            ->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),

                TernaryFilter::make('has_photos')
                    ->label('Tem Fotos')
                    ->placeholder('Todos')
                    ->trueLabel('Com fotos')
                    ->falseLabel('Sem fotos'),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->headerActions([
                CreateAction::make()
                    ->label('Nova Atualização')
                    ->icon('heroicon-o-plus-circle')
                    ->modalHeading('Criar Nova Atualização de Obra')
                    ->modalSubmitActionLabel('Criar Atualização')
                    ->successNotificationTitle('Atualização de obra criada com sucesso!'),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Ver Detalhes')
                        ->modalHeading(fn($record) => "Atualização: {$record->title}"),

                    EditAction::make()
                        ->label('Editar')
                        ->modalHeading('Editar Atualização de Obra'),

                    DeleteAction::make()
                        ->label('Eliminar')
                        ->modalHeading('Eliminar Atualização')
                        ->modalDescription('Tem certeza que deseja eliminar esta atualização? Esta ação não pode ser desfeita.')
                        ->successNotificationTitle('Atualização eliminada com sucesso'),
                ])->icon('heroicon-o-ellipsis-vertical')
                    ->button()
                    ->color('gray'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Eliminar Selecionados')
                        ->modalHeading('Eliminar Atualizações Selecionadas')
                        ->modalDescription('Tem certeza que deseja eliminar as atualizações selecionadas? Esta ação não pode ser desfeita.')
                        ->successNotificationTitle('Atualizações eliminadas com sucesso'),
                ]),
            ])
            ->emptyStateHeading('Nenhuma atualização de obra registrada')
            ->emptyStateDescription('Crie a primeira atualização para acompanhar o progresso da obra.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Criar Primeira Atualização')
                    ->button(),
            ])
            ->deferLoading()
            ->persistFiltersInSession()
            ->poll('30s');
    }
}