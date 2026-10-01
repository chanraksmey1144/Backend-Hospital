<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Medicine extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'code', 'name', 'generic_name', 'category_id', 'manufacturer',
        'batch_no', 'quantity', 'unit', 'purchase_price', 'selling_price',
        'expiry_date', 'min_stock', 'supplier_id', 'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity'       => 'integer',
            'min_stock'      => 'integer',
            'purchase_price' => 'decimal:2',
            'selling_price'  => 'decimal:2',
            'expiry_date'    => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = 'med-' . Str::lower(Str::random(20));
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }
}
