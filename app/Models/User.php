<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia, FilamentUser, HasName, HasTenants
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

    public function getTenants(Panel $panel): \Illuminate\Support\Collection
    {
        return collect([$this->school])->filter();
    }

    public function canAccessTenant(\Illuminate\Database\Eloquent\Model $tenant): bool
    {
        return $this->school_id === $tenant->id;
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
        'school_id',
        'school_class_id',
    ];

    /**
     * Get the school the user belongs to.
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the class the student belongs to.
     */
    public function schoolClass(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    /**
     * Get the grades received by this student.
     */
    public function grades(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Gradebook::class, 'student_id');
    }

    /**
     * Get the grades issued by this teacher.
     */
    public function gradedItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Gradebook::class, 'graded_by');
    }

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
