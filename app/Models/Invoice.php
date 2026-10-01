<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invoice extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'code', 'patient_id', 'appointment_id', 'discount', 'tax',
        'total', 'paid_amount', 'status', 'payment_method', 'issue_date', 'due_date',
    ];

    protected function casts(): array
    {
        return [
            'discount'    => 'decimal:2',
            'tax'         => 'decimal:2',
            'total'       => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'issue_date'  => 'date',
            'due_date'    => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = 'inv-' . Str::lower(Str::random(20));
            }
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id', 'id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id', 'id');
    }
}
