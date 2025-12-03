<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class OpportunityInvestor extends Pivot
{
    protected $table = 'opportunity_investor'; // ← IMPORTANTE: singular

    protected $casts = [
        'has_access' => 'boolean',
        'access_granted_at' => 'datetime',
        'investment_amount' => 'decimal:2',
        'percentage' => 'decimal:2',
    ];
}
