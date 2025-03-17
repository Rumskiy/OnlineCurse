<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'options',
        'correct_answer',
        'section_id'
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}

