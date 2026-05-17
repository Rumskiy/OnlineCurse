<?php

namespace App\Repositories\Eloquent;

use App\Models\Course;
use App\Repositories\Contracts\CourseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CourseRepository implements CourseRepositoryInterface
{
    public function all(): Collection
    {
        return Course::with(['category', 'author'])->get();
    }

    public function findById(int $id): ?Course
    {
        return Course::with(['category', 'author'])->find($id);
    }

    public function findByAuthorId(string $authorId): Collection
    {
        return Course::where('author_id', $authorId)
            ->with('category')
            ->get();
    }

    public function findByCategoryId(int $categoryId): Collection
    {
        return Course::where('category_id', $categoryId)
            ->with(['category', 'author'])
            ->get();
    }

    public function create(array $data): Course
    {
        return Course::create($data);
    }

    public function update(Course $course, array $data): Course
    {
        $course->update($data);
        return $course;
    }

    public function delete(Course $course): bool
    {
        return $course->delete();
    }
}
