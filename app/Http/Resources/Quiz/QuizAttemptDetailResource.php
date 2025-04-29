<?php

namespace App\Http\Resources\Quiz;

use App\Http\Resources\Test\TestResource; // Повний ресурс тесту з питаннями
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizAttemptDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'score' => $this->score,
            'total_questions' => $this->total_questions,
            'percentage' => $this->percentage,
            'answers_details' => $this->answers_details,
            'completed_at' => $this->completed_at?->format('d.m.Y H:i'),
            'completed_at_iso' => $this->completed_at?->toIso8601String(),
            'test' => new TestResource($this->whenLoaded('test')),
        ];
    }
}
