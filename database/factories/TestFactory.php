<?php

namespace Database\Factories;

use App\Models\Test;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestFactory extends Factory
{
    protected $model = Test::class;

    public function definition()
    {
        return [
            'question' => $this->faker->sentence,
            'options' => json_encode([$this->faker->word, $this->faker->word, $this->faker->word]),
            'correct_answer' => 0,
            'section_id' => \App\Models\Section::factory(),
        ];
    }
}

