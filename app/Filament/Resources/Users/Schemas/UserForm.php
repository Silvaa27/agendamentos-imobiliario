<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações da Conta')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        
                        TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->hidden(fn (string $context): bool => $context === 'edit'),
                    ])->columns(2),
                
                Section::make('Permissões')
                    ->schema([
                        CheckboxList::make('roles')
                            ->label('Cargos')
                            ->relationship('roles', 'name')
                            ->searchable()
                            ->columns(2)
                            ->afterStateUpdated(function ($state, $set, $record) {
                                // Se adicionou role de investidor, verificar perfil
                                if (in_array('investidor', $state)) {
                                    if (!$record || !$record->investorProfile) {
                                        Notification::make()
                                            ->title('Atenção: Perfil de Investidor Necessário')
                                            ->body('Este usuário foi atribuído como investidor. Complete o perfil com NIF e telefone no menu "Investidores".')
                                            ->warning()
                                            ->persistent()
                                            ->send();
                                    }
                                }
                            }),
                    ]),
            ]);
    }
}
