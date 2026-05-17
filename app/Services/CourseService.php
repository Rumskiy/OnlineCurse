<?php

namespace App\Services;

use App\Models\Course;
use App\Repositories\Contracts\CourseRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CourseService
{
    protected $courseRepository;

    public function __construct(CourseRepositoryInterface $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * Створення курсу з обробкою зображення обкладинки
     */
    public function createCourse(array $data, $imageFile = null): Course
    {
        return DB::transaction(function () use ($data, $imageFile) {
            $course = $this->courseRepository->create($data);

            if ($imageFile) {
                $course->addMedia($imageFile)->toMediaCollection('default');
            }

            return $course->load(['category', 'author']);
        });
    }

    /**
     * Оновлення курсу та його медіа
     */
    public function updateCourse(Course $course, array $data, $imageFile = null): Course
    {
        return DB::transaction(function () use ($course, $data, $imageFile) {
            $course = $this->courseRepository->update($course, $data);

            if ($imageFile) {
                $course->clearMediaCollection('default');
                $course->addMedia($imageFile)->toMediaCollection('default');
            }

            return $course->load(['category', 'author']);
        });
    }

    /**
     * Видалення курсу
     */
    public function deleteCourse(Course $course): bool
    {
        return DB::transaction(function () use ($course) {
            $course->clearMediaCollection('default');
            return $this->courseRepository->delete($course);
        });
    }
}
