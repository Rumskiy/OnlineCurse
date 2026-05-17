<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'school_classes';

    protected $fillable = [
        'school_id',
        'name',
        'academic_year',
        'homeroom_teacher_id',
    ];

    /**
     * Get the school that owns the class.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the homeroom teacher for the class.
     */
    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }

    /**
     * Get the students belonging to this class.
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'school_class_id');
    }

    /**
     * Get the courses assigned to this class.
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'class_course')
            ->withPivot('teacher_id', 'start_date', 'end_date')
            ->withTimestamps();
    }
}
