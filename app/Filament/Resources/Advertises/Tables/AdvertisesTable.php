<?php

namespace App\Filament\Resources\Advertises\Tables;

use App\Filament\Resources\Advertises\AdvertiseResource;
use App\Models\Advertise;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Criado por')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('associatedUsers.name')
                    ->label('Partilhado com')
                    ->badge()
                    ->color('success')
                    ->limitList(3),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->icon(fn(bool $state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn(bool $state): string => $state ? 'success' : 'danger'),


                TextColumn::make('uuid')
                    ->label('Link do Formulário')
                    ->formatStateUsing(fn() => 'Abrir Formulário')
                    ->url(fn(Advertise $record): string => route('advertisement.respond', ['id' => $record->uuid]))
                    ->openUrlInNewTab()
                    ->badge()
                    ->color('primary'),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->default()
                    ->label('Status')
                    ->placeholder('All forms')
                    ->trueLabel('Active forms')
                    ->falseLabel('Inactive forms'),
            ])
            ->actions([
                EditAction::make()
                    ->visible(
                        fn(Advertise $record): bool =>
                        auth()->user()->hasRole('super_admin') ||
                        $record->user_id === auth()->id() ||
                        $record->associatedUsers->contains(auth()->id())
                    ),
                Action::make('viewResponses')
                    ->label('Ver Respostas')
                    ->icon('heroicon-o-eye')
                    ->color('secundary')
                    ->url(fn(Advertise $record): string => AdvertiseResource::getUrl('view', ['record' => $record])),

                DeleteAction::make()
                    ->visible(
                        fn(Advertise $record): bool =>
                        auth()->user()->hasRole('super_admin') ||
                        $record->user_id === auth()->id()
                    ),
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