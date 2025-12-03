<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações Pessoais')
                    ->description('Dados básicos do utilizador')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome Completo')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Digite o nome completo')
                            ->autocomplete('name'),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(table: 'users', column: 'email', ignoreRecord: true)
                            ->placeholder('exemplo@email.com')
                            ->maxLength(255)
                            ->autocomplete('email'),
                    ]),

                Section::make('Segurança')
                    ->description('Configurações de acesso')
                    ->icon('heroicon-o-lock-closed')
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->revealable()
                            ->required(fn ($operation) => $operation === 'create')
                            ->rules([
                                Password::min(8)
                                    ->letters()
                                    ->mixedCase()
                                    ->numbers()
                                    ->symbols()
                            ])
                            ->confirmed()
                            ->validationMessages([
                                'password.confirmed' => 'As senhas não coincidem',
                                'password.min' => 'A senha deve ter pelo menos 8 caracteres',
                            ])
                            ->helperText('Mínimo 8 caracteres com letras maiúsculas, minúsculas, números e símbolos')
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state)),

                        TextInput::make('password_confirmation')
                            ->label('Confirmar Senha')
                            ->password()
                            ->revealable()
                            ->required(fn ($operation) => $operation === 'create')
                            ->dehydrated(false),
                    ]),

                Section::make('Contacto')
                    ->description('Informações de contacto')
                    ->icon('heroicon-o-phone')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nif')
                            ->label('NIF')
                            ->numeric()
                            ->length(9)
                            ->unique(table: 'users', column: 'nif', ignoreRecord: true)
                            ->placeholder('123456789')
                            ->helperText('9 dígitos numéricos')
                            ->rule('digits:9'),

                        TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                            ->maxLength(20)
                            ->placeholder('+351 912 345 678')
                            ->autocomplete('tel'),
                    ]),

                Section::make('Permissões')
                    ->description('Definição de funções e acessos')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Select::make('roles')
                            ->label('Função')
                            ->relationship(
                                name: 'roles',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->orderBy('name')
                            )
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->native(false)
                            ->default([5])
                            ->helperText('Selecione uma ou mais funções')
                            ->validationMessages([
                                'required' => 'Selecione pelo menos uma função',
                            ])
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }
}