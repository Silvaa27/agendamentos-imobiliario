<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'nif',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relacionamento com Investor

    public function isInvestor(): bool
    {
        return $this->hasRole('investidor');
    }

    // Relação com Investor
    public function investor(): HasOne
    {
        return $this->hasOne(Investor::class);
    }

    // Relação com oportunidades (como responsável)
    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

        public function investors(): HasMany
    {
        return $this->hasMany(Investor::class);
    }

    // Relação com atualizações de obra
    public function constructionUpdates(): HasMany
    {
        return $this->hasMany(ConstructionUpdate::class);
    }
}