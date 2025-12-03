<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Investor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'nif',
        'phone',
        'notes',
    ];

    // Relação com User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ACCESSORS que FUNCIONAM no Filament
    public function getNameAttribute(): ?string
    {
        return $this->user?->name;
    }

    public function getEmailAttribute(): ?string
    {
        return $this->user?->email;
    }

    // MUTATORS para salvar via Filament
    public function setNameAttribute($value): void
    {
        if (!$this->user && $this->exists) {
            $this->load('user');
        }
        if ($this->user) {
            $this->user->name = $value;
            $this->user->save();
        }
    }

    public function setEmailAttribute($value): void
    {
        if (!$this->user && $this->exists) {
            $this->load('user');
        }
        if ($this->user) {
            $this->user->email = $value;
            $this->user->save();
        }
    }

    // Relação com oportunidades
    public function opportunities(): BelongsToMany
    {
        return $this->belongsToMany(Opportunity::class, 'opportunity_investor')
            ->withPivot('investment_amount', 'percentage', 'has_access', 'access_granted_at')
            ->withTimestamps();
    }
}