<?php

namespace App\Http\Resources\Section;

use App\Http\Resources\MediaResource;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'contentSection' => $this->contentSection,
            'course_id' => $this->course_id,
            'section_video' => $this->getMedia('default')->isNotEmpty() ? MediaResource::collection($this->getMedia('default')) : null,
        ];
    }

    public function test()
    {
        return $this->hasOne(Test::class);
    }

}
