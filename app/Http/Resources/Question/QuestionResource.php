<?php
namespace App\Http\Resources\Question;

use App\Http\Resources\MatchPair\MatchPairResource;
use App\Http\Resources\MediaResource;
use App\Http\Resources\Option\OptionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'test_id' => $this->test_id,
            'type' => $this->type,
            'text' => $this->text,
            'order' => $this->order,
            'points' => $this->points,
            'image' => $this->getMedia('default')->isNotEmpty() ? MediaResource::collection($this->getMedia('default')) : null, // Використовуємо аксесор з моделі
            'options' => OptionResource::collection($this->whenLoaded('options')),
            'match_pairs' => MatchPairResource::collection($this->whenLoaded('matchPairs')), // Використовуємо 'matchPairs' як назву відношення
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
