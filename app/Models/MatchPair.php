<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchPair extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'left_text',
        'right_text',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    // Зв'язок: Пара належить Питання
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
