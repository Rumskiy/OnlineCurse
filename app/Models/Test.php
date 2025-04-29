<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Test extends Model
{
    use HasFactory;

    // Зміни fillable - видали 'questions'
    protected $fillable = [
        'section_id',
        'title',
        'total_time_limit',
        'time_per_question',
    ];

    // Видали cast для 'questions'
    protected $casts = [
        'total_time_limit' => 'integer',
        'time_per_question' => 'integer',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    // Зв'язок: Один Тест має багато Питань
    public function questions(): HasMany
    {
        // Вказуємо сортування за полем 'order'
        return $this->hasMany(Question::class)->orderBy('order');
    }
}
