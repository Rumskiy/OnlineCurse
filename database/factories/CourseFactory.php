<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'title_img' => $this->faker->imageUrl(),
            'description' => $this->faker->paragraph,
            'category_id' => \App\Models\Category::factory(),
            'author_id' => \App\Models\User::factory(),
        ];
    }
}

