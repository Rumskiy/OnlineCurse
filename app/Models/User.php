<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia, FilamentUser, HasName
{
    use HasFactory, Notifiable, HasApiTokens, HasUuids, InteractsWithMedia;

    public function getFilamentName(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin' || $this->role === '2'; // Allowing '2' as it seems to be the current admin role
    }

    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
