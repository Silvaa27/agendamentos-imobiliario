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
        'has_investment_program',
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
        'has_investment_program' => 'boolean',
    ];

    // Status disponíveis
    public const STATUSES = [
        'em_avaliacao' => 'Em Avaliação',
        'em_negociacao' => 'Em Negociação',
        'em_obras' => 'Em Obras',
        'em_venda' => 'Em Venda',
        'concluido' => 'Concluído',
    ];

    // Relação com o utilizador (responsável)
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
        return $this->belongsToMany(User::class, 'opportunity_user')
            ->withTimestamps()
            ->withPivot(['created_at', 'updated_at']);
    }

    public function associatedUsers()
    {
        return $this->belongsToMany(User::class, 'opportunity_user')
            ->withTimestamps();
    }
    // Relação muitos-para-muitos com investidores
    public function investors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'opportunity_investor', 'opportunity_id', 'investor_id')
            ->whereHas('roles', function ($query) {
                $query->where('id', 5); // role ID do investidor
            })
            ->withPivot('investment_amount', 'percentage', 'has_access', 'access_granted_at')
            ->withTimestamps();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        // Se tem permissão para ver todas
        if ($user->can('view_all_opportunities')) {
            return $query;
        }

        // Se tem permissão view_opportunities, vê as suas e associadas
        if ($user->can('view_opportunities')) {
            return $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('associatedUsers', function ($subQuery) use ($user) {
                        $subQuery->where('user_id', $user->id);
                    });
            });
        }

        // Se não tem nenhuma permissão, não vê nada
        return $query->whereRaw('1 = 0');
    }


    // Relação com atualizações de obra
    public function constructionUpdates(): HasMany
    {
        return $this->hasMany(\App\Models\ConstructionUpdate::class, 'opportunity_id');
    }
    public function invoice(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // Acesso ao status formatado
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }


    // Cálculo do preço total (compra + obras + outros custos + impostos)
    public function getTotalCostAttribute(): float
    {
        return (float) $this->purchase_price
            + (float) $this->actual_work_value
            + (float) $this->other_costs
            + (float) $this->tax_costs;
    }

    // Cálculo do lucro potencial (cenário mercado - custo total)
    public function getPotentialProfitAttribute(): float
    {
        return (float) $this->price_market - $this->total_cost;
    }

    // Cálculo da margem (%)
    public function getProfitMarginAttribute(): float
    {
        if ($this->total_cost == 0) {
            return 0;
        }
        return ($this->potential_profit / $this->total_cost) * 100;
    }

    // Registar coleção de media para fotos
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')
            ->useDisk('public');
    }

    // Acesso para a primeira foto (thumbnail)
    public function getFirstPhotoUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('photos');
        return $media ? $media->getUrl() : null;
    }
}