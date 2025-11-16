<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf; // Або use PDF; якщо аліас налаштований
use Carbon\Carbon;

class CertificateController extends Controller
{
    /**
     * Перевіряє можливість генерації сертифікату та генерує його.
     *
     * @param Request $request
     * @param Course $course
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function generateCertificate(Request $request, Course $course)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Користувач не автентифікований.'], 401);
        }

        $eligibility = $this->checkEligibilityForCertificate($user, $course);

        if (!$eligibility['can_generate']) {
            return response()->json([
                'message' => 'Неможливо згенерувати сертифікат.',
                'reason' => $eligibility['reason'] ?? 'Не виконані умови для отримання сертифікату.',
                'average_score' => $eligibility['average_score'] ?? null,
            ], 403); // Forbidden
        }

        $data = [
            'userName' => $user->firstName . ' ' . $user->lastName,
            'courseName' => $course->title,
            'averageScore' => $eligibility['average_score'],
            'issueDate' => Carbon::now()->translatedFormat('d F Y р.'), // 'р.' для року, наприклад
            // Можете додати інші дані, наприклад, унікальний номер сертифікату
            // 'certificateNumber' => uniqid('CERT-'),
        ];

        // Завантажуємо HTML вигляд для сертифікату
        // Переконайтесь, що вигляд 'certificates.template' існує
        $pdf = Pdf::loadView('certificates.template', $data)
            ->setPaper('a4', 'landscape');

        // Повертаємо PDF для завантаження або відображення
        // return $pdf->download('certificate-' . Str::slug($course->title) . '-' . $user->id . '.pdf');
        return $pdf->stream('certificate-' . \Illuminate\Support\Str::slug($course->title) . '-' . $user->id . '.pdf');
    }

    /**
     * Перевіряє, чи користувач виконав умови для отримання сертифікату за курсом.
     *
     * @param User $user
     * @param Course $course
     * @return array ['can_generate' => bool, 'average_score' => ?float, 'reason' => ?string]
     */
    private function checkEligibilityForCertificate(User $user, Course $course): array
    {
        // 1. Отримати всі ID тестів для даного курсу
        // Завантажуємо секції та їх тести для курсу
        $course->load('sections.tests');

        $testIds = $course->sections->flatMap(function ($section) {
            return $section->tests;
        })->pluck('id')->unique()->toArray();

        if (empty($testIds)) {
            return [
                'can_generate' => false,
                'reason' => 'У цьому курсі немає тестів.',
                'average_score' => null,
            ];
        }

        $totalPercentageSum = 0;
        $attemptedRequiredTestsCount = 0;
        $allTestsAttempted = true;

        // 2. Для кожного тесту курсу знайти останню спробу користувача
        foreach ($testIds as $testId) {
            $latestAttempt = QuizAttempt::where('user_id', $user->id)
                ->where('test_id', $testId)
                ->orderByDesc('completed_at') // Остання за часом спроба
                ->first();

            if (!$latestAttempt) {
                // Користувач не пройшов цей тест взагалі
                $allTestsAttempted = false;
                break; // Немає сенсу перевіряти далі, якщо один тест не пройдено
            }

            // Умова про те, що кожен тест має бути складений > 50%
            // Згідно з останнім уточненням, головне - середня оцінка > 60%
            // і факт проходження всіх тестів.
            // Якщо б була умова "кожен тест > X%", то тут була б перевірка:
            // if ($latestAttempt->percentage <= 50) {
            //     return [
            //         'can_generate' => false,
            //         'reason' => "Тест (ID: {$testId}) не складено з результатом більше 50%. Ваш результат: {$latestAttempt->percentage}%.",
            //         'average_score' => null, // Можна розрахувати поточну середню, якщо потрібно
            //     ];
            // }

            $totalPercentageSum += $latestAttempt->percentage;
            $attemptedRequiredTestsCount++;
        }

        if (!$allTestsAttempted || $attemptedRequiredTestsCount < count($testIds)) {
            return [
                'can_generate' => false,
                'reason' => 'Ви не пройшли всі необхідні тести для цього курсу.',
                'average_score' => null,
            ];
        }

        // 3. Розрахунок середньої оцінки
        // $attemptedRequiredTestsCount має дорівнювати count($testIds) на цьому етапі
        $averageScore = count($testIds) > 0 ? round($totalPercentageSum / count($testIds), 2) : 0;

        // 4. Перевірка умови на середню оцінку (мінімум 60%)
        if ($averageScore >= 60) {
            return [
                'can_generate' => true,
                'average_score' => $averageScore,
                'reason' => 'Умови для отримання сертифікату виконані.'
            ];
        } else {
            return [
                'can_generate' => false,
                'average_score' => $averageScore,
                'reason' => "Ваш середній бал за курс становить {$averageScore}%. Необхідно мінімум 60%.",
            ];
        }
    }
}
