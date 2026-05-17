<?php

namespace App\Repositories\Eloquent;

use App\Models\Test;
use App\Repositories\Contracts\TestRepositoryInterface;

class TestRepository implements TestRepositoryInterface
{
    public function findWithQuestions(int $id): ?Test
    {
        return Test::with(['questions.media', 'questions.options', 'questions.matchPairs'])
            ->find($id);
    }

    public function findBySectionId(int $sectionId): ?Test
    {
        return Test::with(['questions.media', 'questions.options', 'questions.matchPairs'])
            ->where('section_id', $sectionId)
            ->first();
    }

    public function create(array $data): Test
    {
        return Test::create($data);
    }

    public function update(Test $test, array $data): Test
    {
        $test->update($data);
        return $test;
    }

    public function delete(Test $test): bool
    {
        return $test->delete();
    }
}
