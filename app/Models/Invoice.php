<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
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

    // Tipos disponíveis
    public const TYPES = [
        'obra' => 'Obra',
        'imposto' => 'Imposto',
        'outro' => 'Outro',
        'compra' => 'Compra',
    ];

    // Status disponíveis
    public const STATUSES = [
        'pendente' => 'Pendente',
        'pago' => 'Pago',
        'atrasado' => 'Atrasado',
    ];

    // Relação com a oportunidade
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    // Acesso ao tipo formatado
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    // Acesso ao status formatado
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    // Verificar se está atrasado
    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === 'pago') {
            return false;
        }

        return $this->due_date && $this->due_date->isPast();
    }

    // Formatar datas
    public function getFormattedInvoiceDateAttribute(): string
    {
        return $this->invoice_date->format('d/m/Y');
    }

    public function getFormattedDueDateAttribute(): ?string
    {
        return $this->due_date ? $this->due_date->format('d/m/Y') : null;
    }
}