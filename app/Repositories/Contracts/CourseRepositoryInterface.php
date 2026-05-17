<?php

namespace App\Repositories\Contracts;

use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

interface CourseRepositoryInterface
{
    public function all(): Collection;
    public function findById(int $id): ?Course;
    public function findByAuthorId(string $authorId): Collection;
    public function findByCategoryId(int $categoryId): Collection;
    public function create(array $data): Course;
    public function update(Course $course, array $data): Course;
    public function delete(Course $course): bool;
}
