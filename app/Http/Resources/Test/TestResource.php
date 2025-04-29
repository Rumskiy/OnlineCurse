<?php

namespace App\Http\Resources\Test;

use App\Http\Resources\Question\QuestionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'section_id' => $this->section_id,
            'title' => $this->title,
            'total_time_limit' => $this->total_time_limit,
            'time_per_question' => $this->time_per_question,
            'questions' => QuestionResource::collection($this->whenLoaded('questions')),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
