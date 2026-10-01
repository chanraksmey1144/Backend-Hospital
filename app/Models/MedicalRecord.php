<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MedicalRecord extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'code', 'patient_id', 'doctor_id', 'visit_date',
        'chief_complaint', 'diagnosis', 'treatment_plan', 'notes',
        'status', 'vitals', 'follow_up_date',
    ];

    protected function casts(): array
    {
        return [
            'visit_date'     => 'date',
            'follow_up_date' => 'date',
            'vitals'         => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = 'rec-' . Str::lower(Str::random(21));
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
}
