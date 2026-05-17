<?php

namespace App\Repositories\Contracts;

use App\Models\Test;

interface TestRepositoryInterface
{
    public function findWithQuestions(int $id): ?Test;
    public function findBySectionId(int $sectionId): ?Test;
    public function create(array $data): Test;
    public function update(Test $test, array $data): Test;
    public function delete(Test $test): bool;
}
