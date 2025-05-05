<?php

namespace App\Http\Resources\Quiz;

use App\Http\Resources\Course\CourseBasicResource;
use App\Http\Resources\Section\SectionBasicResource;
use App\Http\Resources\Test\TestBasicResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizAttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'score' => $this->score,
            'total_questions' => $this->total_questions,
            'percentage' => $this->percentage,
            'completed_at' => $this->completed_at->diffForHumans(), // '2 hours ago'
            'completed_at_iso' => $this->completed_at->toIso8601String(), // Для сортування
            // Включаємо інформацію про тест, секцію та курс
            'test' => new TestBasicResource($this->whenLoaded('test')),
            // 'section' і 'course' будуть завантажені через 'test'
            'section_title' => $this->whenLoaded('test', function () {
                return $this->test->section->title ?? null;
            }),
            'course_title' => $this->whenLoaded('test', function () {
                return $this->test->section->course->title ?? null;
            }),
            'user_id' => $this->user_id ?? null,
        ];
    }
}
