<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\Section;
use App\Models\Test;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Створення адміністратора
        User::factory()->create([
            'email' => 'admin@admin.com',
            'firstName' => 'Admin',
            'lastName' => 'Admin',
            'password' => Hash::make('121212'),
            'role' => '2'
        ]);

        // Створення курсів, секцій та тестів
//        Course::factory()->count(1)->create()->each(function ($course) {
//            $course->sections()->saveMany(Section::factory()->count(1)->make())->each(function ($section) {
//                $section->test()->save(Test::factory()->make());
//            });
//        });
    }
}

