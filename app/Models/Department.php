<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Department extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'code', 'name', 'description', 'head_doctor_id', 'room_number', 'status',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = 'dep-' . Str::lower(Str::random(21));
            }
        });
    }

    public function headDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'head_doctor_id', 'id');
    }

    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class, 'department_id', 'id');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class, 'department_id', 'id');
    }
}
