<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Unavailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'start',
        'end',
        'user_id',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    // 🔥 DONO DA INDISPONIBILIDADE
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 🔥 UTILIZADORES COM ACESSO/PARTILHA
    public function associatedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'unavailability_user');
    }

    // 🔥 SCOPE PARA INDISPONIBILIDADES VISÍVEIS PARA UM UTILIZADOR
    public function scopeVisibleTo($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id) // É o dono
                ->orWhereNull('user_id') // É global
                ->orWhereHas('associatedUsers', function ($q) use ($user) {
                    $q->where('user_id', $user->id); // Está na lista de partilha
                });
        });
    }
}