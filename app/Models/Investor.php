<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Investor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'nif',
        'phone',
        'email',
        'notes',
    ];

    // Relação muitos-para-muitos com oportunidades
    public function opportunities(): BelongsToMany
    {
        return $this->belongsToMany(Opportunity::class, 'opportunity_investor')
            ->withPivot('investment_amount', 'percentage', 'has_access', 'access_granted_at')
            ->withTimestamps();
    }

    // Total investido por este investidor
    public function getTotalInvestedAttribute(): float
    {
        return $this->opportunities->sum(function ($opportunity) {
            return (float) $opportunity->pivot->investment_amount ?? 0;
        });
    }

    // Número de oportunidades em que está investido
    public function getOpportunitiesCountAttribute(): int
    {
        return $this->opportunities()->count();
    }
}