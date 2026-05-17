<?php

namespace App\Repositories\Eloquent;

use App\Models\Section;
use App\Repositories\Contracts\SectionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SectionRepository implements SectionRepositoryInterface
{
    public function getByCourseId(int $courseId): Collection
    {
        return Section::where('course_id', $courseId)
            ->orderBy('order')
            ->get();
    }

    public function findById(int $id): ?Section
    {
        return Section::find($id);
    }

    public function create(array $data): Section
    {
        return Section::create($data);
    }

    public function update(Section $section, array $data): Section
    {
        $section->update($data);
        return $section;
    }

    public function delete(Section $section): bool
    {
        return $section->delete();
    }
}
