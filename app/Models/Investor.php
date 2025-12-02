<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Investor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'nif',
        'phone',
        'email'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function investmentOpportunities(): BelongsToMany
    {
        return $this->belongsToMany(
            InvestmentOpportunity::class,
            'io_investor',
            'investor_id',
            'investment_opportunity_id'
        )->withPivot('has_access')->withTimestamps();
    }
}