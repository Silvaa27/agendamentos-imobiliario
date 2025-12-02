<?php

namespace App\Filament\Resources\Opportunities\RelationManagers;

use App\Filament\Resources\Opportunities\OpportunityResource;
use App\Models\Invoice;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoice';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informação da Fatura')
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label('Número da Fatura'),

                        TextInput::make('supplier')
                            ->label('Fornecedor'),

                        Select::make('type')
                            ->label('Tipo')
                            ->options(Invoice::TYPES)
                            ->default('outro')
                            ->required(),
                    ])->columns(3),

                Section::make('Valores e Datas')
                    ->schema([
                        TextInput::make('amount')
                            ->label('Valor (€)')
                            ->numeric()
                            ->prefix('€')
                            ->required(),

                        TextInput::make('description')
                            ->label('Descrição')
                            ->required()
                            ->maxLength(255),

                        DatePicker::make('invoice_date')
                            ->label('Data da Fatura')
                            ->required()
                            ->default(now()),

                        DatePicker::make('due_date')
                            ->label('Data de Vencimento'),

                        DatePicker::make('payment_date')
                            ->label('Data de Pagamento'),
                    ])->columns(2),

                Section::make('Estado e Anexos')
                    ->schema([
                        Select::make('status')
                            ->label('Estado')
                            ->options(Invoice::STATUSES)
                            ->default('pendente')
                            ->required(),

                        FileUpload::make('file_path')
                            ->label('Anexar Fatura (PDF ou Imagem)')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/jpeg',
                                'image/png',
                                'image/webp'
                            ])
                            ->directory('invoices') // Guarda em storage/app/public/invoices/
                            ->disk('public')
                            ->visibility('public')
                            ->preserveFilenames() // Mantém o nome original do ficheiro
                            ->maxSize(10240) // 10MB máximo
                            ->downloadable() // ⬅️ Permite download direto
                            ->openable() // ⬅️ Permite abrir no navegador
                            ->previewable(true) // ⬅️ Mostra preview para imagens
                            ->columnSpan(1),

                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_number')
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Number')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('supplier')
                    ->label('Supplier')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(40),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'obra' => 'info',
                        'imposto' => 'warning',
                        'compra' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state): string => match ($state) {
                        'obra' => 'Work',
                        'imposto' => 'Tax',
                        'compra' => 'Purchase',
                        default => 'Other',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pendente' => 'warning',
                        'pago' => 'success',
                        'atrasado' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state): string => match ($state) {
                        'pendente' => 'Pending',
                        'pago' => 'Paid',
                        'atrasado' => 'Overdue',
                        default => $state,
                    }),

                TextColumn::make('invoice_date')
                    ->label('Invoice Date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('d/m/Y')
                    ->sortable(),

                IconColumn::make('is_overdue')
                    ->label('Overdue')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('gray')
                    ->getStateUsing(function ($record): bool {
                        if ($record->status === 'pago') {
                            return false;
                        }
                        return $record->due_date && $record->due_date->isPast();
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('New Invoice')
                    ->modalHeading('Create New Invoice'),
            ])
            ->actions([
                EditAction::make()
                    ->modalHeading('Edit Invoice'),

                DeleteAction::make(),

                \Filament\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(
                        fn($record): string => $record->file_path
                        ? asset('storage/' . $record->file_path)
                        : '#'
                    )
                    ->openUrlInNewTab()
                    ->visible(fn($record): bool => !empty($record->file_path)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
