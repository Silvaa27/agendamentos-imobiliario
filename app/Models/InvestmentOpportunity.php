<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestmentOpportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'address',
        'latitude',
        'longitude',
        'gallery',
        'worst_case_price',
        'market_price',
        'budgeted_renovation_cost',
        'actual_renovation_cost',
        'purchase_price',
        'other_costs',
        'tax_costs',
        'opportunity_url',
        'status'
    ];

    protected $casts = [
        'gallery' => 'array',
        'worst_case_price' => 'decimal:2',
        'market_price' => 'decimal:2',
        'budgeted_renovation_cost' => 'decimal:2',
        'actual_renovation_cost' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'other_costs' => 'decimal:2',
        'tax_costs' => 'decimal:2',
    ];

    public function investors(): BelongsToMany
    {
        return $this->belongsToMany(
            Investor::class,
            'io_investor', // ← NOME CORRETO DA TABELA PIVOT
            'investment_opportunity_id', // ← foreign pivot key
            'investor_id' // ← related pivot key
        )->withPivot('has_access')->withTimestamps();
    }

    public function constructionMonitorings(): HasMany
    {
        return $this->hasMany(ConstructionMonitoring::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}