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
                    ->revealable()
                    ->required(fn($operation) => $operation === 'create')
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state)),

                // ⬇️ NOVOS CAMPOS PARA INVESTIDORES
                TextInput::make('nif')
                    ->label('NIF')
                    ->unique(ignoreRecord: true)
                    ->maxLength(9),

                TextInput::make('phone')
                    ->label('Telefone')
                    ->tel()
                    ->maxLength(20),

                // ⬇️ ROLES (Filament Shield)
                Select::make('roles')
                    ->label('Cargo')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->default([5]),
            ]);
    }

}
