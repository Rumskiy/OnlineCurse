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
        // 1. Створення тестової школи
        $school = \App\Models\School::firstOrCreate(
            ['name' => 'Середня школа №12'],
            [
                'address' => 'вул. Шевченка, 12, Київ',
            ]
        );

        // 2. Створення адміністратора
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'firstName' => 'Admin',
                'lastName' => 'Admin',
                'password' => Hash::make('121212'),
                'role' => '2',
                'status' => 'active',
                'school_id' => $school->id,
            ]
        );

        if ($admin && !$admin->school_id) {
            $admin->update(['school_id' => $school->id]);
        }

        // 3. Створення вчителя
        $teacher = User::firstOrCreate(
            ['email' => 'teacher@teacher.com'],
            [
                'firstName' => 'Ірина',
                'lastName' => 'Мельник',
                'password' => Hash::make('121212'),
                'role' => '2',
                'status' => 'active',
                'school_id' => $school->id,
            ]
        );

        if ($teacher && !$teacher->school_id) {
            $teacher->update(['school_id' => $school->id]);
        }

        // 4. Створення тестових класів для школи
        \App\Models\SchoolClass::firstOrCreate(
            ['school_id' => $school->id, 'name' => '10-А'],
            [
                'academic_year' => '2025/2026',
                'homeroom_teacher_id' => $teacher->id,
            ]
        );

        \App\Models\SchoolClass::firstOrCreate(
            ['school_id' => $school->id, 'name' => '11-Б'],
            [
                'academic_year' => '2025/2026',
                'homeroom_teacher_id' => $teacher->id,
            ]
        );

        // 5. Створення тестових курсів
        $this->call([
            CourseSeeder::class,
        ]);
    }
}

