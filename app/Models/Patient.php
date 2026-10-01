<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Patient extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'code', 'first_name', 'last_name', 'gender', 'birth_date',
        'phone', 'email', 'address', 'blood_group', 'allergies',
        'insurance_no', 'emergency_contact', 'emergency_phone',
        'marital_status', 'occupation', 'status', 'last_visit',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'last_visit' => 'date',
            'allergies'  => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = 'pt-' . Str::lower(Str::random(21));
            }
        });
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_id', 'id');
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'patient_id', 'id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'patient_id', 'id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'patient_id', 'id');
    }
}
