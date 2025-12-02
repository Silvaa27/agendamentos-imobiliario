<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Investors\InvestorResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('roles.name')
                    ->label('Cargos')
                    ->badge()
                    ->colors([
                        'primary' => 'investidor',
                        'success' => 'admin',
                        'warning' => 'super_admin',
                    ]),

                TextColumn::make('investor_complete')
                    ->label('Perfil Investidor')
                    ->getStateUsing(function ($record) {
                        if ($record->hasRole('investidor')) {
                            return $record->investorProfile &&
                                !empty($record->investorProfile->nif) &&
                                !empty($record->investorProfile->phone)
                                ? '✅ Completo'
                                : '⚠️ Incompleto';
                        }
                        return 'N/A';
                    })
                    ->badge()
                    ->color(
                        fn($state) =>
                        str_contains($state, '✅') ? 'success' :
                        (str_contains($state, '⚠️') ? 'warning' : 'gray')
                    ),
            ])
            ->filters([
                // Filtros
            ])
            ->actions([
                EditAction::make(),
                Action::make('completeInvestorProfile')
                    ->label('Completar Perfil')
                    ->icon('heroicon-o-document-plus')
                    ->color('warning')
                    ->url(
                        fn($record) =>
                        $record->hasRole('investidor')
                        ? InvestorResource::getUrl('edit', ['record' => $record->id])
                        : null
                    )
                    ->visible(
                        fn($record) =>
                        $record->hasRole('investidor') &&
                        (!$record->investorProfile ||
                            empty($record->investorProfile->nif) ||
                            empty($record->investorProfile->phone))
                    ),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
