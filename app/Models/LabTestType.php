<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LabTestType extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'name', 'category', 'price', 'unit', 'normal_range'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = 'test-' . Str::lower(Str::random(19));
            }
        });
    }
}
