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
        // 1. Отримати всі ID секцій для даного курсу
        $course->load('sections.tests');
        $sectionIds = $course->sections->pluck('id')->toArray();
        $testIds = $course->sections->flatMap(function ($section) {
            return $section->tests;
        })->pluck('id')->unique()->toArray();

        if (empty($sectionIds)) {
            return [
                'can_generate' => false,
                'reason' => 'У цьому курсі немає розділів.',
                'average_score' => null,
            ];
        }

        // 2. Перевірка прогресу по секціях
        $completedSectionsCount = \App\Models\SectionProgress::where('user_id', $user->id)
            ->whereIn('section_id', $sectionIds)
            ->where('is_completed', true)
            ->count();

        if ($completedSectionsCount < count($sectionIds)) {
            return [
                'can_generate' => false,
                'reason' => "Ви пройшли {$completedSectionsCount} з " . count($sectionIds) . " розділів. Необхідно пройти всі розділи.",
                'average_score' => null,
            ];
        }

        // 3. Перевірка тестів
        if (empty($testIds)) {
            return [
                'can_generate' => true,
                'average_score' => 100, // Якщо тестів немає, але розділи пройдені
                'reason' => 'Умови виконані (тести відсутні).',
            ];
        }

        $totalPercentageSum = 0;
        $allTestsAttempted = true;

        foreach ($testIds as $testId) {
            $latestAttempt = QuizAttempt::where('user_id', $user->id)
                ->where('test_id', $testId)
                ->orderByDesc('completed_at')
                ->first();

            if (!$latestAttempt) {
                $allTestsAttempted = false;
                break;
            }

            $totalPercentageSum += $latestAttempt->percentage;
        }

        if (!$allTestsAttempted) {
            return [
                'can_generate' => false,
                'reason' => 'Ви не пройшли всі необхідні тести для цього курсу.',
                'average_score' => null,
            ];
        }

        $averageScore = count($testIds) > 0 ? round($totalPercentageSum / count($testIds), 2) : 0;

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
