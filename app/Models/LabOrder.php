<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LabOrder extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'code', 'patient_id', 'doctor_id', 'test_type_id',
        'status', 'result', 'result_notes', 'notes', 'ordered_date', 'completed_date',
    ];

    protected function casts(): array
    {
        return [
            'ordered_date'   => 'datetime',
            'completed_date' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = 'lab-' . Str::lower(Str::random(20));
            }
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }

    public function testType(): BelongsTo
    {
        return $this->belongsTo(LabTestType::class, 'test_type_id', 'id');
    }
}
