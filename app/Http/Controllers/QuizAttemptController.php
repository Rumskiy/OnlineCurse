<?php

namespace App\Http\Controllers;

use App\Http\Resources\Quiz\QuizAttemptDetailResource;
use App\Http\Resources\Quiz\QuizAttemptResource;
use App\Models\Course;
use App\Models\QuizAttempt;
use App\Models\Test; // Імпортуємо Test
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QuizAttemptController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $attempts = QuizAttempt::with([
            'test.section.course'
        ])
            ->where('user_id', $user->id)
            ->get();

        return QuizAttemptResource::collection($attempts);
    }


    public function store(Request $request)
    {
        // Валідація вхідних даних
        $validator = Validator::make($request->all(), [
            'test_id' => 'required|exists:tests,id',
            'answers' => 'required|array', // Перейменував з answers_details для ясності

            'answers.*.question_id' => 'required|integer|exists:questions,id', // Перевіряємо існування питання

            // Валідація залежно від типу питання (можна зробити складнішу, але поки так)
            'answers.*.selected_option_ids' => 'sometimes|required_without:answers.*.selected_pairs|array', // Для single/multiple choice
            'answers.*.selected_option_ids.*' => 'integer|exists:options,id', // Перевіряємо існування опцій

            'answers.*.selected_pairs' => 'sometimes|required_without:answers.*.selected_option_ids|array', // Для match
            'answers.*.selected_pairs.*.left_id' => 'required_with:answers.*.selected_pairs|integer|exists:match_pairs,id', // ID лівої частини
            'answers.*.selected_pairs.*.selected_right_id' => 'required_with:answers.*.selected_pairs|integer|exists:match_pairs,id', // ID обраної правої частини
        ]);

        if ($validator->fails()) {
            Log::warning('Quiz attempt validation failed.', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $testId = $request->input('test_id');
        $userAnswers = $request->input('answers');

        // Завантажуємо тест з усіма питаннями та їх правильними відповідями
        $test = Test::with([
            'questions.options', // Завантажуємо опції для перевірки
            'questions.matchPairs' // Завантажуємо пари для перевірки
        ])->find($testId);

        if (!$test) return response()->json(['message' => 'Test not found'], 404);

        $score = 0;
        $totalQuestionsInTest = $test->questions->count(); // Кількість питань у тесті
        $processedAnswersDetails = []; // Для збереження результатів перевірки

        // Створюємо мапу питань для швидкого доступу
        $questionsMap = $test->questions->keyBy('id');

        foreach ($userAnswers as $userAnswer) {
            $questionId = $userAnswer['question_id'];
            $question = $questionsMap->get($questionId);

            if (!$question) {
                Log::warning("Attempt processing: Question ID {$questionId} not found in Test ID {$testId}. Skipping.");
                continue;
            }

            $isCorrect = false;
            $answerDetail = ['question_id' => $questionId, 'type' => $question->type]; // Починаємо формувати детальну відповідь

            // --- Перевірка відповіді залежно від типу питання ---
            if ($question->type === 'single_choice' || $question->type === 'multiple_choice') {
                $correctOptionIds = $question->options->where('is_correct', true)->pluck('id')->sort()->values()->toArray();
                $selectedOptionIds = collect($userAnswer['selected_option_ids'] ?? [])->map(fn($id) => (int)$id)->sort()->values()->toArray();

                if ($correctOptionIds === $selectedOptionIds) { // Порівнюємо відсортовані масиви ID
                    $isCorrect = true;
                }
                $answerDetail['selected_option_ids'] = $selectedOptionIds; // Зберігаємо вибір користувача

            } elseif ($question->type === 'match') {
                // { left_id: 10, selected_right_id: 25 }
                $userSelectedPairs = collect($userAnswer['selected_pairs'] ?? []);
                $correctPairsMap = $question->matchPairs->keyBy('id'); // Мапа: ID пари => {left_text, right_text}
                $correctMatchesCount = 0;
                $totalPairs = $question->matchPairs->count();

                if ($totalPairs > 0 && $userSelectedPairs->count() === $totalPairs) {
                    foreach ($userSelectedPairs as $selectedPair) {
                        $leftPairId = $selectedPair['left_id'];
                        $selectedRightPairId = $selectedPair['selected_right_id'];

                        $correctLeftPair = $correctPairsMap->get($leftPairId);
                        $selectedRightPair = $correctPairsMap->get($selectedRightPairId);

                        // Перевіряємо, чи права частина, обрана користувачем,
                        // відповідає правильній правій частині для цієї лівої частини
                        if ($correctLeftPair && $selectedRightPair && $correctLeftPair->right_text === $selectedRightPair->right_text) {
                            $correctMatchesCount++;
                        }
                    }
                    // Бал зараховується, тільки якщо всі пари з'єднані правильно
                    if ($correctMatchesCount === $totalPairs) {
                        $isCorrect = true;
                    }
                }
                $answerDetail['selected_pairs'] = $userSelectedPairs->toArray(); // Зберігаємо вибір користувача
            }
            // --- Кінець перевірки ---

            if ($isCorrect) {
                $score += $question->points ?? 1; // Додаємо бали за питання
            }
            $answerDetail['is_correct'] = $isCorrect;
            $processedAnswersDetails[] = $answerDetail;
        }

        // Розраховуємо відсоток (можна базувати на балах, а не тільки кількості питань)
        $maxScore = $test->questions->sum('points'); // Максимально можливий бал
        $percentage = ($maxScore > 0) ? round(($score / $maxScore) * 100, 2) : 0;

        try {
            $attempt = QuizAttempt::create([
                'user_id' => $user->id,
                'test_id' => $testId,
                'score' => $score, // Зберігаємо суму балів
                'total_questions' => $totalQuestionsInTest, // Кількість питань
                'percentage' => $percentage, // Відсоток (від максимального балу)
                'answers_details' => json_encode($processedAnswersDetails), // Зберігаємо деталі відповідей
                'completed_at' => now(),
            ]);

            // Завантажуємо зв'язки для ресурсу
            $attempt->load(['test.section.course']); // Оптимізовано для QuizAttemptResource

            Log::info("Quiz attempt saved successfully for user {$user->id}, test {$testId}. Score: {$score}/{$maxScore}");
            return response()->json([
                'message' => 'Quiz attempt saved successfully',
                'data' => new QuizAttemptResource($attempt)
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error saving quiz attempt: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to save quiz attempt'], 500);
        }
    }

    public function userAttemptsByCourse(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // 1. Отримати всі спроби користувача з необхідними зв'язками
        $allUserAttempts = QuizAttempt::with([
            'test.section.course', // Для групування та інформації про курс
            'test' => function ($query) { // Для деталей тесту в QuizAttemptResource
                $query->select(['id', 'title', 'section_id']); // Вибираємо тільки потрібні поля тесту
            }
        ])
            ->where('user_id', $user->id)
            ->orderByDesc('completed_at')
            ->get();

        if ($allUserAttempts->isEmpty()) {
            return response()->json(['data' => [], 'message' => 'У вас ще немає спроб проходження тестів.'], 200);
        }

        // 2. Згрупувати спроби за ID курсу
        // Ключем буде ID курсу, значенням - колекція спроб для цього курсу
        $groupedByCourseId = $allUserAttempts->groupBy(function ($attempt) {
            // Переконуємось, що шлях до course_id існує
            if ($attempt->test && $attempt->test->section && $attempt->test->section->course) {
                return $attempt->test->section->course->id;
            }
            return 'unknown_course'; // Для спроб, де курс не вдалося визначити
        });

        // 3. Сформувати відповідь
        $result = [];
        $courseIds = $groupedByCourseId->keys()->filter(fn($key) => $key !== 'unknown_course')->toArray();

        if (!empty($courseIds)) {
            // Отримати моделі курсів одним запитом
            $courses = Course::whereIn('id', $courseIds)->get()->keyBy('id');

            foreach ($groupedByCourseId as $courseId => $attemptsInCourse) {
                if ($courseId === 'unknown_course') {
                    // Обробка спроб без визначеного курсу (якщо потрібно)
                    // $result[] = [
                    // 'course' => ['id' => null, 'title' => 'Курс не визначено'],
                    // 'attempts' => QuizAttemptResource::collection($attemptsInCourse)
                    // ];
                    continue;
                }

                $course = $courses->get($courseId);
                if ($course) {
                    $result[] = [
                        // Використовуйте CourseResource, якщо він у вас є і налаштований
                        // 'course' => new CourseResource($course),
                        'course' => [ // Або просто поверніть потрібні дані курсу
                            'id' => $course->id,
                            'title' => $course->title,
                            // Додайте інші поля курсу, якщо потрібно
                        ],
                        'attempts' => QuizAttemptResource::collection($attemptsInCourse)
                    ];
                }
            }
        }


        return response()->json(['data' => $result]);
    }

    // ... show() ... - Логіка залишається схожою, але QuizAttemptDetailResource
    // повинен коректно обробляти новий формат answers_details та завантажувати тест з новими зв'язками.
    public function show(Request $request, string $id)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $attempt = QuizAttempt::with([
            'test' => function ($query) {
                // Завантажуємо повну структуру тесту для показу деталей
                $query->with(['questions.media', 'questions.options', 'questions.matchPairs']);
            }
        ])
            ->where('user_id', $user->id)
            ->find($id);

        if (!$attempt) {
            return response()->json(['message' => 'Attempt not found or access denied'], 404);
        }
        // QuizAttemptDetailResource має коректно працювати з завантаженими даними
        return new QuizAttemptDetailResource($attempt);
    }

}
