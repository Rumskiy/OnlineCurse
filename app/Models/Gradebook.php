<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gradebook extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id',
        'section_id',
        'test_id',
        'grade',
        'teacher_comment',
        'graded_by',
    ];

    /**
     * Get the student who received the grade.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the course where the grade was given.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the section (lesson) associated with the grade, if any.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the test associated with the grade, if any.
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    /**
     * Get the teacher who graded the entry.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}
