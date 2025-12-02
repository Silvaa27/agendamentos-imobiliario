<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConstructionMonitoring extends Model
{
    use HasFactory;

    protected $fillable = [
        'investment_opportunity_id',
        'date',
        'report',
        'photos'
    ];

    protected $casts = [
        'photos' => 'array',
        'date' => 'date'
    ];

    public function investmentOpportunity(): BelongsTo
    {
        return $this->belongsTo(InvestmentOpportunity::class);
    }
}