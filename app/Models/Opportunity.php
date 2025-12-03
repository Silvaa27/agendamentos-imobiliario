<?php

namespace App\Models;

use Filament\Forms\Components\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Opportunity extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'title',
        'description',
        'address',
        'city',
        'postal_code',
        'latitude',
        'longitude',
        'property_info',
        'price_worst_case',
        'price_market',
        'budgeted_work_value',
        'actual_work_value',
        'purchase_price',
        'other_costs',
        'tax_costs',
        'opportunity_link',
        'status',
        'user_id',
    ];

    protected $casts = [
        'price_worst_case' => 'decimal:2',
        'price_market' => 'decimal:2',
        'budgeted_work_value' => 'decimal:2',
        'actual_work_value' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'other_costs' => 'decimal:2',
        'tax_costs' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public const STATUSES = [
        'em_avaliacao' => 'Em Avaliação',
        'em_negociacao' => 'Em Negociação',
        'em_obras' => 'Em Obras',
        'em_venda' => 'Em Venda',
        'concluido' => 'Concluído',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'opportunity_id');
    }

    public function investorsOnly()
    {
        return $this->belongsToMany(User::class, 'opportunity_user')
            ->whereHas('roles', fn($query) => $query->where('name', 'investidor'));
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'opportunity_user');
    }


    public function associatedUsers()
    {
        return $this->belongsToMany(User::class, 'opportunity_user');
    }

    // Isto deve dar problemas se o ID mudar mas not sure how to resolver melhor
    public function investors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'opportunity_investor', 'opportunity_id', 'investor_id')
            ->whereHas('roles', function ($query) {
                $query->where('id', 5);
            })
            ->withPivot('investment_amount', 'percentage', 'has_access', 'access_granted_at')
            ->withTimestamps();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->can('view_all_opportunities')) {
            return $query;
        }

        if ($user->can('view_opportunities')) {
            return $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('associatedUsers', function ($subQuery) use ($user) {
                        $subQuery->where('user_id', $user->id);
                    });
            });
        }

        return $query->whereRaw('1 = 0');
    }

    public function constructionUpdates(): HasMany
    {
        return $this->hasMany(\App\Models\ConstructionUpdate::class, 'opportunity_id');
    }
    public function invoice(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getTotalCostAttribute(): float
    {
        return (float) $this->purchase_price
            + (float) $this->actual_work_value
            + (float) $this->other_costs
            + (float) $this->tax_costs;
    }

    public function getPotentialProfitAttribute(): float
    {
        return (float) $this->price_market - $this->total_cost;
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->total_cost == 0) {
            return 0;
        }
        return ($this->potential_profit / $this->total_cost) * 100;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')
            ->useDisk('public');
    }

    public function getFirstPhotoUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('photos');
        return $media ? $media->getUrl() : null;
    }
}