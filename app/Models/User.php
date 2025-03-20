<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Додаємо HasUuids для роботи з UUID

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasUuids;

    protected $keyType = 'string'; // Вказуємо, що id - це рядок
    public $incrementing = false;  // Вимикаємо автоінкремент id

    protected $fillable = [
        'id',
        'lastName',
        'firstName',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
