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

    // Relação com a oportunidade
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    // Relação com o utilizador (responsável)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Registar coleção de media para fotos da obra
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('construction_photos')
            ->useDisk('public')
            ->singleFile(); // altera para false para múltiplas fotos
    }

    // Formatação da data
    public function getFormattedDateAttribute(): string
    {
        return $this->date->format('d/m/Y');
    }
}