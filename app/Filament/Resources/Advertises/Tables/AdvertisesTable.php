<?php

namespace App\Filament\Resources\Advertises\Tables;

use App\Models\Advertise;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class AdvertisesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable()
                    ->copyable()
                    ->copyableState(fn (Advertise $record): string => $record->uuid)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('form_link')
                    ->label('Form URL')
                    ->state(function (Advertise $record): string {
                        // Usando a rota nomeada que você definiu
                        return route('advertisement.respond', ['id' => $record->uuid]);
                    })
                    ->formatStateUsing(function (Advertise $record) {
                        $url = route('advertisement.respond', ['id' => $record->uuid]);
                        return new HtmlString("
                            <div class='flex items-center gap-2'>
                                <input type='text' value='{$url}' readonly 
                                    class='fi-input-text text-xs bg-gray-50 border border-gray-300 text-gray-900 rounded-lg px-2 py-1 w-64 cursor-pointer'
                                    onclick='this.select()'>
                                <button type='button' 
                                    onclick='navigator.clipboard.writeText(\"{$url}\")'
                                    class='fi-btn-label text-primary-600 hover:text-primary-700 text-sm font-medium px-2 py-1 rounded border border-gray-300 hover:border-gray-400'>
                                    Copy
                                </button>
                            </div>
                        ");
                    })
                    ->html(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),

                TextColumn::make('url')
                    ->label('Destination URL')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All forms')
                    ->trueLabel('Active forms')
                    ->falseLabel('Inactive forms'),
            ])
            ->actions([
                Action::make('view_form')
                    ->label('View Form')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (Advertise $record): string => route('advertisement.respond', ['id' => $record->uuid]))
                    ->openUrlInNewTab()
                    ->hidden(fn (Advertise $record): bool => !$record->is_active),

                EditAction::make()
                    ->icon('heroicon-o-pencil'),

                Action::make('copy_link')
                    ->label('Copy Link')
                    ->icon('heroicon-o-clipboard')
                    ->action(function (Advertise $record) {
                        $url = route('advertisement.respond', ['id' => $record->uuid]);
                        
                        // No Filament v4, você pode usar notificações
                        session()->flash('success', 'Form link copied to clipboard!');
                        
                        return $url;
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Copy Form Link')
                    ->modalDescription("The form link will be copied to your clipboard.")
                    ->modalSubmitActionLabel('Copy')
                    ->hidden(fn (Advertise $record): bool => !$record->is_active),

                DeleteAction::make()
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->icon('heroicon-o-trash'),
                    
                    BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                        }),
                    
                    BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                        }),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}