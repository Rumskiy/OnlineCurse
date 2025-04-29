<?php

namespace App\Http\Controllers;

// Core & Framework
use App\Http\Requests\Test\CreateTest\CreateTestRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

// Spatie Media Library
use Spatie\MediaLibrary\MediaCollections\Models\Media;

// App Specific
use App\Http\Resources\Test\TestResource;
use App\Models\Test;
use App\Models\Question;
use App\Models\User;

class TestController extends Controller
{
    /**
     * Отримати тест для конкретної секції.
     */
    public function sectionTest($sectionId)
    {
        Log::debug("Fetching test for section ID: {$sectionId}");
        $test = Test::with([
            'questions' => function ($query) {
                $query->with(['media', 'options', 'matchPairs'])->orderBy('order');
            }
        ])
            ->where('section_id', $sectionId)
            ->first();

        if (!$test) {
            Log::warning("Test not found for section ID: {$sectionId}");
            return response()->json(['message' => 'Test not found for this section'], 404);
        }

        Log::debug("Test found for section ID: {$sectionId}, Test ID: {$test->id}");
        return response()->json(new TestResource($test));
    }

    /**
     * Отримати конкретний тест за ID.
     */
    public function show($id)
    {
        Log::debug("Fetching test with ID: {$id}");
        $test = Test::with([
            'questions' => function ($query) {
                $query->with(['media', 'options', 'matchPairs'])->orderBy('order');
            }
        ])->find($id);

        if (!$test) {
            Log::warning("Test not found with ID: {$id}");
            return response()->json(['message' => 'Test not found'], 404);
        }

        Log::debug("Test found with ID: {$id}");
        return response()->json(new TestResource($test));
    }


    /**
     * Зберегти новий тест.
     */
    public function store(CreateTestRequest $request)
    {
        $validatedData = $request->validated();
        Log::info('Validated test creation data received.');

        try {
            $test = DB::transaction(function () use ($validatedData) {

                $test = Test::create([
                    'section_id' => $validatedData['section_id'],
                    'title' => $validatedData['title'],
                    'total_time_limit' => $validatedData['total_time_limit'] ?? null,
                    'time_per_question' => $validatedData['time_per_question'] ?? null,
                ]);
                Log::debug("Test created with ID: " . $test->id);

                foreach ($validatedData['questions'] as $qIndex => $questionData) {
                    Log::debug("Processing question index: {$qIndex}, type: {$questionData['type']}");
                    $question = $test->questions()->create([
                        'type' => $questionData['type'],
                        'text' => $questionData['text'],
                        'order' => $questionData['order'] ?? $qIndex,
                        'points' => $questionData['points'] ?? 1,
                    ]);
                    Log::debug("Question created with ID: " . $question->id);

                    // --- ОБРОБКА ЗОБРАЖЕННЯ ---
                    if (!empty($questionData['image']) && $questionData['image'] instanceof \Illuminate\Http\UploadedFile) {
                        $question->addMedia($questionData['image'])->toMediaCollection('default');
                    }
                    // --- КІНЕЦЬ ОБРОБКИ ЗОБРАЖЕННЯ ---

                    // --- СТВОРЕННЯ ОПЦІЙ АБО ПАР ---
                    if (in_array($questionData['type'], ['single_choice', 'multiple_choice']) && isset($questionData['options'])) {
                        Log::debug("Creating options for question ID: " . $question->id);
                        foreach ($questionData['options'] as $oIndex => $optionData) {
                            $question->options()->create([
                                'text' => $optionData['text'],
                                'is_correct' => $optionData['is_correct'],
                                'order' => $optionData['order'] ?? $oIndex,
                            ]);
                        }
                    } elseif ($questionData['type'] === 'match' && isset($questionData['match_pairs'])) {
                        Log::debug("Creating match pairs for question ID: " . $question->id);
                        foreach ($questionData['match_pairs'] as $pIndex => $pairData) {
                            $question->matchPairs()->create([ // <-- Виправлено
                                'left_text' => $pairData['left_text'],
                                'right_text' => $pairData['right_text'],
                                'order' => $pairData['order'] ?? $pIndex,
                            ]);
                        }
                    }
                    // --- КІНЕЦЬ СТВОРЕННЯ ОПЦІЙ/ПАР ---
                }
                Log::debug("Finished processing all questions for test ID: " . $test->id);
                return $test;
            }); // <-- Видалено зайвий $request з use()

            // Завантажуємо зв'язки для відповіді
            $test->load(['questions.options', 'questions.matchPairs', 'questions.media']);

            Log::info('Test created successfully with ID: ' . $test->id);
            return response()->json(new TestResource($test), 201);

        } catch (\Throwable $e) {
            Log::error('Test creation failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'requestData' => $validatedData]);
            return response()->json(['message' => 'Failed to create test due to a server error.'], 500);
        }
    }

    /**
     * Оновити існуючий тест.
     */
    public function update(Request $request, Test $test)
    {
        Log::info('Test update request received for Test ID: ' . $test->id);
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        try {
            // --- ПОВНА ВАЛІДАЦІЯ ДЛЯ ОНОВЛЕННЯ ---
            $validatedData = $request->validate([
                'title' => 'sometimes|string|max:255',
                'total_time_limit' => 'sometimes|nullable|integer|min:1',
                'time_per_question' => 'sometimes|nullable|integer|min:5',
                'questions' => 'sometimes|array|min:1', // Якщо питання передані, їх має бути хоча б одне

                // Валідація полів всередині масиву питань
                'questions.*.id' => ['sometimes', 'integer', Rule::exists('questions', 'id')->where(function ($query) use ($test) {
                    $query->where('test_id', $test->id); // Переконуємось, що ID питання належить цьому тесту
                })],
                'questions.*.type' => ['sometimes', 'required', Rule::in(['single_choice', 'match', 'multiple_choice'])],
                'questions.*.text' => 'sometimes|required|string',
                'questions.*.order' => 'sometimes|required|integer|min:0',
                'questions.*.points' => 'sometimes|integer|min:1',
                'questions.*.image_media_id' => 'nullable|integer|exists:media,id', // Перевірка існування медіа
                'questions.*.remove_image' => 'sometimes|boolean', // Прапорець для видалення

                // Валідація Options (якщо тип відповідний)
                'questions.*.options' => 'sometimes|required_if:questions.*.type,single_choice,multiple_choice|array|min:2',
                'questions.*.options.*.text' => 'sometimes|required_if:questions.*.type,single_choice,multiple_choice|string',
                'questions.*.options.*.is_correct' => 'sometimes|required_if:questions.*.type,single_choice,multiple_choice|boolean',
                'questions.*.options.*.order' => 'sometimes|integer|min:0',

                // Валідація Match Pairs (якщо тип відповідний)
                'questions.*.match_pairs' => 'sometimes|required_if:questions.*.type,match|array|min:2',
                'questions.*.match_pairs.*.left_text' => 'sometimes|required_if:questions.*.type,match|string',
                'questions.*.match_pairs.*.right_text' => 'sometimes|required_if:questions.*.type,match|string',
                'questions.*.match_pairs.*.order' => 'sometimes|integer|min:0',

                // Додаткова перевірка на наявність правильних відповідей для choice-типів
                'questions.*' => ['sometimes', function ($attribute, $value, $fail) {
                    if (isset($value['type'])) {
                        if (($value['type'] === 'single_choice' || $value['type'] === 'multiple_choice') && !empty($value['options'])) {
                            $correctCount = collect($value['options'])->where('is_correct', true)->count();
                            if ($value['type'] === 'single_choice' && $correctCount !== 1) {
                                $fail("Питання типу 'Одиночний вибір' ({$attribute}) повинно мати рівно один правильний варіант.");
                            } elseif ($value['type'] === 'multiple_choice' && $correctCount === 0) {
                                $fail("Питання типу 'Множинний вибір' ({$attribute}) повинно мати хоча б один правильний варіант.");
                            }
                        }
                        // Можна додати перевірки для match, якщо потрібно
                    }
                }],
            ]);
            // --- КІНЕЦЬ ВАЛІДАЦІЇ ---

            Log::debug('Test update validation passed for Test ID: ' . $test->id);

            DB::transaction(function () use ($test, $validatedData, $currentUser) { // <-- Додав $currentUser

                // 1. Оновлюємо базові поля тесту (тільки ті, що є в $fillable Test)
                $test->fill($validatedData);
                $test->save();
                Log::debug("Test base fields updated for ID: " . $test->id);

                // 2. Обробка питань (Синхронізація)
                if (isset($validatedData['questions'])) {
                    $existingQuestionIds = $test->questions()->pluck('id')->toArray();
                    // Отримуємо ID з запиту (ті, що не null/порожні)
                    $incomingQuestionIds = collect($validatedData['questions'])->pluck('id')->filter()->toArray();

                    // Питання на видалення
                    $idsToDelete = array_diff($existingQuestionIds, $incomingQuestionIds);
                    if (!empty($idsToDelete)) {
                        Log::debug("Deleting questions with IDs: " . implode(', ', $idsToDelete));
                        Question::destroy($idsToDelete);
                    }

                    // Оновлення існуючих та створення нових
                    foreach ($validatedData['questions'] as $qIndex => $questionData) {
                        $question = null;
                        $isNewQuestion = empty($questionData['id']) || !in_array($questionData['id'], $existingQuestionIds);

                        if (!$isNewQuestion) {
                            // Оновлення існуючого
                            $question = Question::find($questionData['id']);
                            if ($question) {
                                $question->update([
                                    'type' => $questionData['type'],
                                    'text' => $questionData['text'],
                                    'order' => $questionData['order'] ?? $qIndex,
                                    'points' => $questionData['points'] ?? $question->points,
                                ]);
                                Log::debug("Updated question ID: " . $question->id);
                            }
                        } else {
                            // Створення нового
                            $question = $test->questions()->create([
                                'type' => $questionData['type'],
                                'text' => $questionData['text'],
                                'order' => $questionData['order'] ?? $qIndex,
                                'points' => $questionData['points'] ?? 1,
                            ]);
                            Log::debug("Created new question ID: " . $question->id);
                        }

                        if (!$question) {
                            Log::error("Failed to find or create question at index {$qIndex} for test ID {$test->id}. Skipping.");
                            continue;
                        }

                        // Обробка зображення для ОНОВЛЕННЯ/СТВОРЕННЯ
                        $currentMedia = $question->getFirstMedia('question_image');

                        if (!empty($questionData['remove_image']) && $currentMedia) {
                            Log::debug("Removing image for question ID: " . $question->id);
                            $currentMedia->delete();
                            $currentMedia = null; // Оновлюємо змінну
                        } elseif (isset($questionData['image_media_id'])) {
                            $newMediaId = $questionData['image_media_id'];

                            if ($newMediaId !== null && (!$currentMedia || $currentMedia->id != $newMediaId)) {
                                Log::debug("Processing new media ID {$newMediaId} for question ID {$question->id}");
                                $newMedia = Media::find($newMediaId);

                                if ($newMedia && $newMedia->model_type === User::class && $newMedia->model_id === $currentUser->id) {
                                    if ($currentMedia) {
                                        Log::debug("Deleting old media for question ID: " . $question->id);
                                        $currentMedia->delete();
                                    }
                                    try {
                                        $newMedia->move($question, 'question_image');
                                        Log::info("New media ID {$newMedia->id} successfully moved to question ID {$question->id}.");
                                    } catch (\Exception $e) {
                                        Log::error("Failed to move media ID {$newMedia->id} to question ID {$question->id}: " . $e->getMessage());
                                    }
                                } else {
                                    Log::warning("New media ID {$newMediaId} not found or invalid for question ID {$question->id}.");
                                }
                            }
                            // Якщо newMediaId === null, але remove_image = false - нічого не робимо (зображення не змінювалось)
                        }

                        // Оновлення/Створення Options/MatchPairs (видаляємо старі, створюємо нові)
                        $question->options()->delete();
                        $question->matchPairs()->delete();

                        if (in_array($questionData['type'], ['single_choice', 'multiple_choice']) && isset($questionData['options'])) {
                            Log::debug("Recreating options for question ID: " . $question->id);
                            foreach ($questionData['options'] as $oIndex => $optionData) {
                                $question->options()->create([ // <-- Виправлено
                                    'text' => $optionData['text'],
                                    'is_correct' => $optionData['is_correct'],
                                    'order' => $optionData['order'] ?? $oIndex,
                                ]);
                            }
                        } elseif ($questionData['type'] === 'match' && isset($questionData['match_pairs'])) {
                            Log::debug("Recreating match pairs for question ID: " . $question->id);
                            foreach ($questionData['match_pairs'] as $pIndex => $pairData) {
                                $question->matchPairs()->create([ // <-- Виправлено
                                    'left_text' => $pairData['left_text'],
                                    'right_text' => $pairData['right_text'],
                                    'order' => $pairData['order'] ?? $pIndex,
                                ]);
                            }
                        }
                    } // кінець foreach questionData
                } // кінець if isset($validatedData['questions'])

            }); // кінець DB::transaction

            // Завантажуємо оновлений тест з усіма зв'язками
            $test->load(['questions.options', 'questions.matchPairs', 'questions.media']);

            Log::info('Test updated successfully for ID: ' . $test->id);
            return response()->json(new TestResource($test), 200);

        } catch (ValidationException $e) {
            // Обробляємо помилки валідації конкретно
            Log::warning('Test update validation failed for ID ' . $test->id . ': ', $e->errors());
            return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            // Обробляємо інші помилки
            Log::error('Test update failed for ID ' . $test->id . ': ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to update test due to a server error.'], 500);
        }
    }

    /**
     * Видалити тест.
     * (Додамо базовий метод видалення)
     */
    public function destroy(Test $test)
    {
        // Можна додати перевірку прав доступу (Policy)
        // $this->authorize('delete', $test);

        Log::info("Attempting to delete test with ID: {$test->id}");
        try {
            DB::transaction(function () use ($test) {
                // Питання, опції, пари видаляться через cascade constraints в БД
                // Медіафайли (зображення питань) потрібно видалити окремо
                $test->questions()->each(function (Question $question) {
                    $question->clearMediaCollection('question_image');
                    Log::debug("Cleared media for question ID: {$question->id}");
                });
                // Тепер видаляємо сам тест (і питання через cascade)
                $test->delete();
            });

            Log::info("Test with ID: {$test->id} deleted successfully.");
            return response()->json(['message' => 'Test deleted successfully'], 200); // Або 204 No Content

        } catch (\Throwable $e) {
            Log::error("Failed to delete test ID {$test->id}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to delete test.'], 500);
        }
    }
}
