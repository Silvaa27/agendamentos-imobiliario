<?php

namespace App\Filament\Resources\Investors\Schemas;

use App\Models\User;
use App\Models\Investor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Filament\Notifications\Notification;

class InvestorForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = auth()->user();
        $canViewAll = $user->can('view_all_opportunities');
        $isInvestor = !$canViewAll;

        return $schema
            ->statePath('data')
            ->components([
                Section::make('Informações da Conta')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome Completo')
                            ->required()
                            ->maxLength(255)
                            ->disabled($isInvestor),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabled($isInvestor),

                        TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->hidden(fn(string $context): bool => $context === 'edit')
                            ->visible($canViewAll),
                    ])->columns(2),

                Section::make('Perfil do Investidor')
                    ->id('investor_profile')
                    ->schema([
                        // Campos para o perfil do investidor
                        TextInput::make('nif')
                            ->label('NIF')
                            ->required()
                            ->maxLength(9)
                            ->disabled($isInvestor)
                            ->validationAttribute('nif'),

                        TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->required()
                            ->disabled($isInvestor),
                    ])->columns(2),
            ]);
    }

    // Método para ser chamado antes de salvar
    public static function beforeSave(array &$data, $record = null): void
    {
        // Se for criação, garantir role de investidor
        if (!$record) {
            $data['should_create_investor'] = true;
        }
    }

    // Método para ser chamado após criar
    public static function afterCreate(User $record, array $data): void
    {
        // 1. Atribuir role de investidor
        $investorRole = Role::where('name', 'investidor')->first();
        if (!$investorRole) {
            $investorRole = Role::create(['name' => 'investidor', 'guard_name' => 'web']);
        }

        $record->assignRole($investorRole);

        // 2. Criar perfil de investor
        Investor::create([
            'user_id' => $record->id,
            'name' => $record->name,
            'email' => $record->email,
            'nif' => $data['nif'] ?? null,
            'phone' => $data['phone'] ?? null,
        ]);

        Notification::make()
            ->title('Investidor criado com sucesso')
            ->body('Perfil de investidor criado automaticamente.')
            ->success()
            ->send();
    }

    // Método para ser chamado após salvar (edição)
    public static function afterSave(User $record, array $data): void
    {
        // Verificar ou atualizar perfil de investor
        $investor = Investor::where('user_id', $record->id)->first();

        if ($investor) {
            // Atualizar perfil existente
            $investor->update([
                'name' => $record->name,
                'email' => $record->email,
                'nif' => $data['nif'] ?? $investor->nif,
                'phone' => $data['phone'] ?? $investor->phone,
            ]);
        } else {
            // Se não tem perfil mas é investidor, criar
            if ($record->hasRole('investidor')) {
                Investor::create([
                    'user_id' => $record->id,
                    'name' => $record->name,
                    'email' => $record->email,
                    'nif' => $data['nif'] ?? null,
                    'phone' => $data['phone'] ?? null,
                ]);
            }
        }
    }
}