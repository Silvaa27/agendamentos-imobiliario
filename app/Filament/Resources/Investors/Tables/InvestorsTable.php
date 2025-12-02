<?php

namespace App\Filament\Resources\Investors\Tables;

use App\Models\User;
use App\Models\Investor;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class InvestorsTable
{
    public static function configure(Table $table): Table
    {
        $user = auth()->user();
        $canViewAll = $user->can('view_all_opportunities');
        
        return $table
            ->query(
                User::query()
                    ->role('investidor')
                    ->with('investorProfile') // Carregar relacionamento
                    ->withCount('investorProfile') // Contar se tem perfil
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                    
                TextColumn::make('investorProfile.nif')
                    ->label('NIF')
                    ->searchable()
                    ->placeholder('Não informado')
                    ->visible($canViewAll),
                    
                TextColumn::make('investorProfile.phone')
                    ->label('Telefone')
                    ->placeholder('Não informado'),
                    
                TextColumn::make('roles.name')
                    ->label('Cargo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'investidor' ? 'Investidor' : $state)
                    ->colors([
                        'primary' => 'investidor',
                    ])
                    ->visible($canViewAll),
                    
                // CORREÇÃO AQUI: Usar contagem através do relacionamento
                TextColumn::make('investorProfile.investmentOpportunities_count')
                    ->label('Oportunidades')
                    ->getStateUsing(function ($record) {
                        if ($record->investorProfile && $record->investorProfile->relationLoaded('investmentOpportunities')) {
                            return $record->investorProfile->investmentOpportunities->count();
                        }
                        return 0;
                    })
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray')
                    ->visible($canViewAll),
                    
                // Ou usar este método alternativo:
                TextColumn::make('has_investor_profile')
                    ->label('Perfil')
                    ->getStateUsing(fn ($record) => $record->investor_profile_count > 0 ? '✅' : '❌')
                    ->badge()
                    ->color(fn ($state) => $state === '✅' ? 'success' : 'danger')
                    ->visible($canViewAll),
            ])
            ->filters([])
            ->actions([
                EditAction::make()
                    ->visible(
                        fn($record) =>
                        $canViewAll ||
                        ($record->id === $user->id)
                    ),
                    
                ViewAction::make()
                    ->visible(
                        fn($record) =>
                        $canViewAll ||
                        ($record->id === $user->id)
                    ),
                    
                Action::make('resetPassword')
                    ->label('Redefinir Senha')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->visible($canViewAll)
                    ->action(function ($record) {
                        $record->update([
                            'password' => Hash::make('password123')
                        ]);

                        Notification::make()
                            ->title('Senha redefinida')
                            ->body('Senha redefinida para: password123')
                            ->success()
                            ->send();
                    }),
                    
                DeleteAction::make()
                    ->before(function ($record) {
                        if ($record->investorProfile) {
                            $record->investorProfile->delete();
                        }
                    })
                    ->visible($canViewAll),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->before(function ($records) {
                        foreach ($records as $record) {
                            if ($record->investorProfile) {
                                $record->investorProfile->delete();
                            }
                        }
                    })
                    ->visible($canViewAll),
            ]);
    }
}