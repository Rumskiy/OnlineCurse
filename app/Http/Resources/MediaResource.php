<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge([
            'id' => $this->id,
            'link' => $this->getUrl() ?? null,
            'model_id' => $this->model_id,
        ],
            $this->hasGeneratedConversion('thumb') ? ['thumb_link' => $this->getUrl('thumb')] : []);

    }
}
