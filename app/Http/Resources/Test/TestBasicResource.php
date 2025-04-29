<?php

namespace App\Http\Resources\Test;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestBasicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'section_id' => $this->section_id,
        ];
    }
}
