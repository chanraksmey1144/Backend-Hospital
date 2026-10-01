<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

        protected $fillable = [
        'user_id',
        'email',
        'push',
        'appointments',
        'laboratory',
        'billing',
        'inventory',
        'system',
    ];
    protected function casts(): array
    {
        return [
            'email'        => 'boolean',
            'push'         => 'boolean',
            'appointments' => 'boolean',
            'laboratory'   => 'boolean',
            'billing'      => 'boolean',
            'inventory'    => 'boolean',
            'system'       => 'boolean',
        ];
    }
        public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
