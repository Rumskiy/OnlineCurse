<?php
namespace App\Http\Resources\Option;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'is_correct' => $this->is_correct,
            // 'order' => $this->order, // Розкоментуй, якщо порядок потрібен на фронтенді
        ];
    }
}
