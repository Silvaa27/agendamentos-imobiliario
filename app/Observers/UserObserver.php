<?php

namespace App\Observers;

use App\Filament\Resources\Investors\InvestorResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Se usuário tem role de investidor mas não tem perfil, notificar
        if ($user->hasRole('investidor') && !$user->investorProfile) {
            $this->sendIncompleteProfileNotification($user);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Se role mudou para investidor
        if ($user->wasChanged('role') && $user->hasRole('investidor')) {
            if (!$user->investorProfile) {
                $this->sendIncompleteProfileNotification($user);
            }
        }

        // Se tem role de investidor mas não tem perfil completo
        if ($user->hasRole('investidor') && $user->investorProfile) {
            if (empty($user->investorProfile->nif) || empty($user->investorProfile->phone)) {
                $this->sendMissingDataNotification($user);
            }
        }
    }

    /**
     * Handle the User "saved" event.
     */
    public function saved(User $user): void
    {
        // Verificação após salvar
        if ($user->hasRole('investidor') && !$user->investorProfile) {
            $this->sendIncompleteProfileNotification($user);
        }
    }

    private function sendIncompleteProfileNotification(User $user): void
    {
        Notification::make()
            ->title('Perfil de Investidor Incompleto')
            ->body("O usuário {$user->name} foi atribuído como investidor, mas não tem perfil completo. Adicione NIF e telefone.")
            ->warning()
            ->persistent()
            ->actions([
                Action::make('completeProfile')
                    ->label('Completar Perfil')
                    ->url(InvestorResource::getUrl('edit', ['record' => $user->id]))
                    ->button(),
            ])
            ->sendToDatabase($user); // Enviar para o usuário admin
    }

    private function sendMissingDataNotification(User $user): void
    {
        $missingFields = [];
        if (empty($user->investorProfile->nif))
            $missingFields[] = 'NIF';
        if (empty($user->investorProfile->phone))
            $missingFields[] = 'Telefone';

        Notification::make()
            ->title('Dados do Investidor Faltantes')
            ->body("O investidor {$user->name} está sem: " . implode(', ', $missingFields))
            ->warning()
            ->sendToDatabase($user); // Enviar para o usuário admin
    }
}