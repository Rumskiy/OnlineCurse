<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'test_id',
        'score',
        'total_questions',
        'percentage',
        'answers_details', // Важливо!
        'completed_at',
    ];

    protected $casts = [
        'answers_details' => 'array', // Для зручної роботи з JSON
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        // Переконайтесь, що модель User використовує HasUuids, якщо user_id це UUID
        return $this->belongsTo(User::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }
}
