<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ConstructionUpdate extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'opportunity_id',
        'date',
        'title',
        'report',
        'progress_percentage',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'progress_percentage' => 'decimal:2',
    ];

    
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Opportunity::class, 'opportunity_id');
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('construction_photos')
            ->useDisk('public');
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->date->format('d/m/Y');
    }
}