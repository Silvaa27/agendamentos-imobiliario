<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Invoice extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'opportunity_id',
        'invoice_number',
        'supplier',
        'amount',
        'description',
        'type',
        'invoice_date',
        'due_date',
        'payment_date',
        'status',
        'file_path',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
    ];

    public const TYPES = [
        'obra' => 'Obra',
        'imposto' => 'Imposto',
        'outro' => 'Outro',
        'compra' => 'Compra',
    ];

    public const STATUSES = [
        'pendente' => 'Pendente',
        'pago' => 'Pago',
        'atrasado' => 'Atrasado',
    ];

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === 'pago') {
            return false;
        }

        return $this->due_date && $this->due_date->isPast();
    }

    public function getFormattedInvoiceDateAttribute(): string
    {
        return $this->invoice_date->format('d/m/Y');
    }

    public function getFormattedDueDateAttribute(): ?string
    {
        return $this->due_date ? $this->due_date->format('d/m/Y') : null;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('invoices')
            ->useDisk('public')
            ->acceptsMimeTypes([
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/webp',
            ])
            ->singleFile();

        if (config('media-library.generate_thumbnails_for_pdfs', false)) {
            $this->addMediaConversion('thumb')
                ->width(300)
                ->height(300)
                ->performOnCollections('invoices');
        }
    }

    public function getHasDocumentAttribute(): bool
    {
        return $this->hasMedia('invoices');
    }
}