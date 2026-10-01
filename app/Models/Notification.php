<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'type', 'title', 'message', 'is_read', 'user_id',
    ];

    protected function casts(): array
    {
        return ['is_read' => 'boolean'];
    }
}
