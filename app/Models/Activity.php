<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = ['type', 'message', 'user_id', 'timestamp'];

    protected function casts(): array
    {
        return ['timestamp' => 'datetime'];
    }
}
