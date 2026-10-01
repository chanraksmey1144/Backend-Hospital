<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

       // CHAR(24) Primary Key configuration
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'role',
        'department_id',
        'avatar',
        'email_verified_at',
        'last_login_at',
        'is_active',
        'locale',
        'timezone',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
        protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }
    // Automatically generate 24-char unique ID if not provided
    protected static function booted(): void
    {
        static::creating(function ($user) {
            if (empty($user->id)) {
                // 'u-' (2 chars) + 22 random alphanumeric chars = 24 chars total
                $user->id = 'u-' . Str::lower(Str::random(22));
            }
        });
    }
    public function notificationPreference(): HasOne
{
    return $this->hasOne(NotificationPreference::class, 'user_id', 'id');
}
}
