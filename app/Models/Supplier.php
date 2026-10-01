<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Supplier extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'name', 'contact_person', 'phone', 'email', 'address',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = 'sup-' . Str::lower(Str::random(20));
            }
        });
    }

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class, 'supplier_id', 'id');
    }
}
