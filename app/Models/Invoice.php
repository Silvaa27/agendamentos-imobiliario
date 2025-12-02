<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'investment_opportunity_id',
        'amount',
        'description',
        'invoice_number',
        'date',
        'file_path',
        'status'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2'
    ];

    public function investmentOpportunity(): BelongsTo
    {
        return $this->belongsTo(InvestmentOpportunity::class);
    }
}