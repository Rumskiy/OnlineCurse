<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'address',
        'logo',
    ];

    /**
     * Get the classes for the school.
     */
    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    /**
     * Get the users (students, teachers, admins) in the school.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
