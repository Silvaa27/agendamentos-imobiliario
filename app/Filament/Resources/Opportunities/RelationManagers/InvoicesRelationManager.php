<?php

namespace App\Filament\Resources\Opportunities\RelationManagers;

use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    protected static ?string $title = 'Faturas e Custos';

    protected static ?string $modelLabel = 'Fatura';
    protected static ?string $pluralModelLabel = 'Faturas';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Identificação da Fatura')
                    ->description('Informações básicas da fatura')
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label('Número da Fatura')
                            ->placeholder('Ex: FAC-2024-00123')
                            ->required()
                            ->maxLength(50)
                            ->columnSpan(2),

                        TextInput::make('supplier')
                            ->label('Fornecedor/Emitente')
                            ->placeholder('Nome do fornecedor')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Select::make('type')
                            ->label('Tipo de Despesa')
                            ->options(Invoice::TYPES)
                            ->default('outro')
                            ->required()
                            ->columnSpan(1)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $suggestions = [
                                    'obra' => 'Materiais/Mão de obra para obras',
                                    'imposto' => 'Impostos e taxas municipais',
                                    'compra' => 'Aquisição do imóvel',
                                    'outro' => 'Outras despesas relacionadas',
                                ];
                                if (isset($suggestions[$state])) {
                                    $set('description', $suggestions[$state]);
                                }
                            }),
                    ]),

                Section::make('Valores Financeiros')
                    ->description('Informações monetárias')
                    ->icon('heroicon-o-currency-euro')
                    ->columns(2)
                    ->schema([
                        TextInput::make('amount')
                            ->label('Valor Total (€)')
                            ->numeric()
                            ->prefix('€')
                            ->required()
                            ->minValue(0)
                            ->step(0.01)
                            ->columnSpan(1),

                        Select::make('status')
                            ->label('Estado do Pagamento')
                            ->options(Invoice::STATUSES)
                            ->default('pendente')
                            ->required()
                            ->columnSpan(1)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === 'pago') {
                                    $set('payment_date', now()->format('Y-m-d'));
                                }
                            }),
                    ]),

                Section::make('Datas Importantes')
                    ->description('Prazos e vencimentos')
                    ->icon('heroicon-o-calendar')
                    ->columns(4)
                    ->schema([
                        DatePicker::make('invoice_date')
                            ->label('Data da Fatura')
                            ->required()
                            ->default(now())
                            ->displayFormat('d/m/Y')
                            ->columnSpan(2),

                        DatePicker::make('due_date')
                            ->label('Data de Vencimento')
                            ->minDate(fn(callable $get) => $get('invoice_date'))
                            ->columnSpan(2),

                        DatePicker::make('payment_date')
                            ->label('Data de Pagamento')
                            ->minDate(fn(callable $get) => $get('invoice_date'))
                            ->visible(fn(callable $get) => $get('status') === 'pago')
                            ->columnSpan(2)
                            ->helperText('Preenchido automaticamente'),
                    ]),

                Section::make('Descrição e Documentação')
                    ->description('Detalhes e anexos')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        Textarea::make('description')
                            ->label('Descrição Resumida')
                            ->placeholder('Breve descrição da fatura...')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Descrição Detalhada')
                            ->placeholder('Descreva o que foi adquirido/pago com esta fatura...')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                SpatieMediaLibraryFileUpload::make('invoice_document')
                    ->label('Ficheiro da Fatura')
                    ->collection('invoices')
                    ->disk('public')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                    ])
                    ->multiple(false)
                    ->maxFiles(1)
                    ->maxSize(10240)
                    ->appendFiles()
                    ->openable()
                    ->downloadable()
                    ->helperText('Formatos: PDF, JPG, PNG, WebP. Máximo 10MB.')
                    ->getUploadedFileNameForStorageUsing(
                        fn($file): string => (string) str($file->getClientOriginalName())
                            ->slug()
                            ->prepend('fatura-')
                            ->append('-' . uniqid())
                    )
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_number')
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Nº Fatura')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Número copiado!')
                    ->weight('medium')
                    ->description(fn($record) => $record->supplier)
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('amount')
                    ->label('Valor')
                    ->money('EUR', locale: 'pt')
                    ->sortable()
                    ->alignEnd()
                    ->summarize([
                        Sum::make(),
                    ])
                    ->color(fn($record) => $record->status === 'pago' ? 'success' : 'warning')
                    ->formatStateUsing(fn($state) => '€ ' . number_format($state, 2, ',', ' '))
                    ->toggleable(isToggledHiddenByDefault: false),

                BadgeColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn($state): string => match ($state) {
                        'obra' => 'Obra',
                        'imposto' => 'Imposto',
                        'compra' => 'Compra',
                        default => 'Outro',
                    })
                    ->colors([
                        'info' => 'obra',
                        'warning' => 'imposto',
                        'success' => 'compra',
                        'gray' => 'outro',
                    ])
                    ->icons([
                        'heroicon-o-wrench' => 'obra',
                        'heroicon-o-banknotes' => 'imposto',
                        'heroicon-o-home' => 'compra',
                        'heroicon-o-document-text' => 'outro',
                    ])
                    ->toggleable(isToggledHiddenByDefault: false),

                BadgeColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn($state): string => match ($state) {
                        'pendente' => 'Pendente',
                        'pago' => 'Pago',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pendente',
                        'success' => 'pago',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pendente',
                        'heroicon-o-check-circle' => 'pago',
                    ])
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('invoice_date')
                    ->label('Data Fatura')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('due_date')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn($record) => $record->is_overdue ? 'danger' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: false),

                IconColumn::make('has_attachment')
                    ->label('Anexo')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-document')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->getStateUsing(fn($record) => !empty($record->file_path))
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_overdue')
                    ->label('Status Pag.')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->getStateUsing(function ($record): bool {
                        if ($record->status === 'pago')
                            return false;
                        return $record->due_date && $record->due_date->isPast();
                    })
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->defaultSort('invoice_date', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo de Fatura')
                    ->options(Invoice::TYPES)
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(Invoice::STATUSES)
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('has_attachment')
                    ->label('Tem Anexo')
                    ->placeholder('Todos')
                    ->trueLabel('Com anexo')
                    ->falseLabel('Sem anexo')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('file_path'),
                        false: fn(Builder $query) => $query->whereNull('file_path'),
                    ),

                TernaryFilter::make('is_overdue')
                    ->label('Atrasadas')
                    ->placeholder('Todas')
                    ->trueLabel('Atrasadas')
                    ->falseLabel('Em dia')
                    ->queries(
                        true: fn(Builder $query) => $query
                            ->where('status', '!=', 'pago')
                            ->whereNotNull('due_date')
                            ->where('due_date', '<', now()),
                        false: fn(Builder $query) => $query
                            ->where(function ($query) {
                                $query->where('status', 'pago')
                                    ->orWhereNull('due_date')
                                    ->orWhere('due_date', '>=', now());
                            }),
                    ),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->headerActions([
                CreateAction::make()
                    ->label('Nova Fatura')
                    ->icon('heroicon-o-plus-circle')
                    ->modalHeading('Registrar Nova Fatura')
                    ->modalSubmitActionLabel('Criar Fatura')
                    ->successNotificationTitle('Fatura criada com sucesso!'),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Ver Detalhes')
                        ->modalHeading(fn($record) => "Fatura: {$record->invoice_number}"),

                    EditAction::make()
                        ->label('Editar')
                        ->modalHeading('Editar Fatura'),

                    Action::make('download')
                        ->label('Baixar')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->url(
                            fn($record): string =>
                            $record->file_path
                            ? asset('storage/' . $record->file_path)
                            : '#'
                        )
                        ->openUrlInNewTab()
                        ->visible(fn($record): bool => !empty($record->file_path)),
                    Action::make('viewGallery')
                        ->label('Ver Fotos')
                        ->icon('heroicon-o-photo')
                        ->color('primary')
                        ->modalHeading(fn($record) => "Fatura: {$record->invoice_number}")
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false)
                        ->modalContent(function ($record) {
                            $mediaItems = $record->getMedia('invoices');

                            if ($mediaItems->isEmpty()) {
                                return view('filament.components.empty-gallery');
                            }

                            return view('filament.components.gallery-modal', [
                                'mediaItems' => $mediaItems,
                                'record' => $record,
                            ]);
                        }),

                    Action::make('mark_as_paid')
                        ->label('Marcar como Pago')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($record) {
                            $record->update([
                                'status' => 'pago',
                                'payment_date' => now(),
                            ]);
                        })
                        ->visible(fn($record): bool => $record->status !== 'pago'),

                    DeleteAction::make()
                        ->label('Eliminar')
                        ->modalHeading('Eliminar Fatura')
                        ->modalDescription('Tem certeza que deseja eliminar esta fatura? Esta ação não pode ser desfeita.')
                        ->successNotificationTitle('Fatura eliminada com sucesso'),
                ])->icon('heroicon-o-ellipsis-vertical')
                    ->button()
                    ->color('gray'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Eliminar Selecionadas')
                        ->modalHeading('Eliminar Faturas Selecionadas')
                        ->modalDescription('Tem certeza que deseja eliminar as faturas selecionadas? Esta ação não pode ser desfeita.')
                        ->successNotificationTitle('Faturas eliminadas com sucesso'),

                    BulkAction::make('mark_as_paid_bulk')
                        ->label('Marcar como Pagas')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'status' => 'pago',
                                    'payment_date' => now(),
                                ]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateHeading('Nenhuma fatura registrada')
            ->emptyStateDescription('Registre a primeira fatura para acompanhar os custos desta oportunidade.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Registrar Primeira Fatura')
                    ->button(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderBy('invoice_date', 'desc');
    }
}