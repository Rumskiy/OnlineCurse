<?php

namespace App\Http\Resources\Course;

use App\Http\Resources\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'title_img' => $this->getMedia('default')->isNotEmpty() ? MediaResource::collection($this->getMedia('default')) : null,
        ];
    }
}
