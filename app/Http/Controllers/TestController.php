<?php

namespace App\Http\Controllers;

// Core & Framework
use App\Http\Requests\Test\CreateTest\CreateTestRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

// App Specific
use App\Http\Resources\Test\TestResource;
use App\Models\Test;
use App\Services\TestService;
use App\Repositories\Contracts\TestRepositoryInterface;

class TestController extends Controller
{
    protected $testService;
    protected $testRepository;

    public function __construct(TestService $testService, TestRepositoryInterface $testRepository)
    {
        $this->testService = $testService;
        $this->testRepository = $testRepository;
    }

    /**
     * Отримати тест для конкретної секції.
     */
    public function sectionTest($sectionId)
    {
        Log::debug("Fetching test for section ID: {$sectionId}");
        
        $test = $this->testRepository->findBySectionId($sectionId);

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
        
        $test = $this->testRepository->findWithQuestions($id);

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
        Log::info('Validated test creation data received.');

        try {
            $test = $this->testService->createTest($request->validated(), Auth::user());
            Log::info('Test created successfully with ID: ' . $test->id);
            return response()->json(new TestResource($test), 201);
        } catch (\Throwable $e) {
            Log::error('Test creation failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to create test due to a server error.'], 500);
        }
    }

    /**
     * Оновити існуючий тест.
     */
    public function update(Request $request, Test $test)
    {
        Log::info('Test update request received for Test ID: ' . $test->id);

        try {
            // Використовуємо ту саму валідацію, що була в оригінальному контролері
            $validatedData = $request->validate([
                'title' => 'sometimes|string|max:255',
                'total_time_limit' => 'sometimes|nullable|integer|min:1',
                'time_per_question' => 'sometimes|nullable|integer|min:5',
                'questions' => 'sometimes|array|min:1',

                'questions.*.id' => 'sometimes|integer|exists:questions,id',
                'questions.*.type' => 'sometimes|required|in:single_choice,match,multiple_choice',
                'questions.*.text' => 'sometimes|required|string',
                'questions.*.order' => 'sometimes|required|integer|min:0',
                'questions.*.points' => 'sometimes|integer|min:1',
                'questions.*.image_media_id' => 'nullable|integer|exists:media,id',
                'questions.*.remove_image' => 'sometimes|boolean',

                'questions.*.options' => 'sometimes|required_if:questions.*.type,single_choice,multiple_choice|array|min:2',
                'questions.*.options.*.text' => 'sometimes|required|string',
                'questions.*.options.*.is_correct' => 'sometimes|required|boolean',
                'questions.*.options.*.order' => 'sometimes|integer|min:0',

                'questions.*.match_pairs' => 'sometimes|required_if:questions.*.type,match|array|min:2',
                'questions.*.match_pairs.*.left_text' => 'sometimes|required|string',
                'questions.*.match_pairs.*.right_text' => 'sometimes|required|string',
                'questions.*.match_pairs.*.order' => 'sometimes|integer|min:0',
            ]);

            Log::debug('Test update validation passed for Test ID: ' . $test->id);

            $updatedTest = $this->testService->updateTest($test, $validatedData, Auth::user());

            Log::info('Test updated successfully for ID: ' . $test->id);
            return response()->json(new TestResource($updatedTest), 200);

        } catch (ValidationException $e) {
            Log::warning('Test update validation failed for ID ' . $test->id . ': ', $e->errors());
            return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error('Test update failed for ID ' . $test->id . ': ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to update test due to a server error.'], 500);
        }
    }

    /**
     * Видалити тест.
     */
    public function destroy(Test $test)
    {
        Log::info("Attempting to delete test with ID: {$test->id}");
        try {
            $this->testService->deleteTest($test);
            Log::info("Test with ID: {$test->id} deleted successfully.");
            return response()->json(['message' => 'Test deleted successfully'], 200);
        } catch (\Throwable $e) {
            Log::error("Failed to delete test ID {$test->id}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to delete test.'], 500);
        }
    }
}
