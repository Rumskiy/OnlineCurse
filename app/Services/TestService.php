<?php

namespace App\Services;

use App\Models\Test;
use App\Models\Question;
use App\Repositories\Contracts\TestRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TestService
{
    protected $testRepository;

    public function __construct(TestRepositoryInterface $testRepository)
    {
        $this->testRepository = $testRepository;
    }

    /**
     * Створення тесту разом з питаннями та медіа
     */
    public function createTest(array $data, $user): Test
    {
        return DB::transaction(function () use ($data, $user) {
            $test = $this->testRepository->create([
                'section_id' => $data['section_id'],
                'title' => $data['title'],
                'total_time_limit' => $data['total_time_limit'] ?? null,
                'time_per_question' => $data['time_per_question'] ?? null,
            ]);

            foreach ($data['questions'] as $index => $qData) {
                $this->createQuestion($test, $qData, $index);
            }

            return $test->load(['questions.options', 'questions.matchPairs', 'questions.media']);
        });
    }

    /**
     * Оновлення тесту (Синхронізація питань)
     */
    public function updateTest(Test $test, array $data, $user): Test
    {
        return DB::transaction(function () use ($test, $data, $user) {
            // Оновлюємо основні поля тесту через репозиторій
            $this->testRepository->update($test, array_filter([
                'title' => $data['title'] ?? null,
                'total_time_limit' => $data['total_time_limit'] ?? null,
                'time_per_question' => $data['time_per_question'] ?? null,
            ]));

            if (isset($data['questions'])) {
                $existingQuestionIds = $test->questions()->pluck('id')->toArray();
                $incomingQuestionIds = collect($data['questions'])->pluck('id')->filter()->toArray();

                // 1. Видалення зайвих питань
                $toDelete = array_diff($existingQuestionIds, $incomingQuestionIds);
                if (!empty($toDelete)) {
                    Question::destroy($toDelete);
                }

                // 2. Обробка кожного питання
                foreach ($data['questions'] as $index => $qData) {
                    if (!empty($qData['id']) && in_array($qData['id'], $existingQuestionIds)) {
                        $this->updateQuestion($qData['id'], $qData, $index, $user);
                    } else {
                        $this->createQuestion($test, $qData, $index);
                    }
                }
            }

            return $test->load(['questions.options', 'questions.matchPairs', 'questions.media']);
        });
    }

    /**
     * Видалення тесту
     */
    public function deleteTest(Test $test): bool
    {
        return DB::transaction(function () use ($test) {
            $test->questions()->each(function (Question $question) {
                $question->clearMediaCollection('question_image');
            });
            return $this->testRepository->delete($test);
        });
    }

    /* --- Допоміжні методи для створення/оновлення питань --- */

    private function createQuestion(Test $test, array $qData, int $index): Question
    {
        $question = $test->questions()->create([
            'type' => $qData['type'],
            'text' => $qData['text'],
            'order' => $qData['order'] ?? $index,
            'points' => $qData['points'] ?? 1,
        ]);

        if (!empty($qData['image']) && $qData['image'] instanceof \Illuminate\Http\UploadedFile) {
            $question->addMedia($qData['image'])->toMediaCollection('default');
        }

        $this->saveAnswers($question, $qData);

        return $question;
    }

    private function updateQuestion(int $id, array $qData, int $index, $user): void
    {
        $question = Question::find($id);
        if (!$question) return;

        $question->update([
            'type' => $qData['type'],
            'text' => $qData['text'],
            'order' => $qData['order'] ?? $index,
            'points' => $qData['points'] ?? $question->points,
        ]);

        // Обробка зображення
        $currentMedia = $question->getFirstMedia('question_image');
        if (!empty($qData['remove_image']) && $currentMedia) {
            $currentMedia->delete();
        } elseif (isset($qData['image_media_id'])) {
            $newMediaId = $qData['image_media_id'];
            if ($newMediaId !== null && (!$currentMedia || $currentMedia->id != $newMediaId)) {
                $newMedia = Media::find($newMediaId);
                if ($newMedia && $newMedia->model_id === $user->id) {
                    if ($currentMedia) $currentMedia->delete();
                    $newMedia->move($question, 'question_image');
                }
            }
        }

        // Перезапис варіантів відповідей
        $question->options()->delete();
        $question->matchPairs()->delete();
        $this->saveAnswers($question, $qData);
    }

    private function saveAnswers(Question $question, array $qData): void
    {
        if (in_array($qData['type'], ['single_choice', 'multiple_choice']) && isset($qData['options'])) {
            foreach ($qData['options'] as $oIndex => $optionData) {
                $question->options()->create([
                    'text' => $optionData['text'],
                    'is_correct' => $optionData['is_correct'],
                    'order' => $optionData['order'] ?? $oIndex,
                ]);
            }
        } elseif ($qData['type'] === 'match' && isset($qData['match_pairs'])) {
            foreach ($qData['match_pairs'] as $pIndex => $pairData) {
                $question->matchPairs()->create([
                    'left_text' => $pairData['left_text'],
                    'right_text' => $pairData['right_text'],
                    'order' => $pairData['order'] ?? $pIndex,
                ]);
            }
        }
    }
}
