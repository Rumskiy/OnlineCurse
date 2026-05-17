<?php

namespace App\Repositories\Contracts;

use App\Models\Section;
use Illuminate\Database\Eloquent\Collection;

interface SectionRepositoryInterface
{
    public function getByCourseId(int $courseId): Collection;
    public function findById(int $id): ?Section;
    public function create(array $data): Section;
    public function update(Section $section, array $data): Section;
    public function delete(Section $section): bool;
}
