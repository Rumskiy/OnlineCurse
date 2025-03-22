<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string',
            'options' => 'required|array|min:4', // Ensure at least 4 options
            'correct_answers' => 'required|array',
            'section_id' => 'required|exists:sections,id',
        ]);

        // Ensure correct_answers contains valid indices
        foreach ($data['correct_answers'] as $index) {
            if (!isset($data['options'][$index])) {
                return response()->json(['error' => 'Invalid correct_answers index'], 400);
            }
        }

        $data['correct_answers'] = json_encode($data['correct_answers']);
        $test = Test::create($data);

        return response()->json([
            'status' => 'success',
            'data' => $test,
        ], 201);
    }

    public function update(Request $request, Test $test)
    {
        $data = $request->validate([
            'question' => 'sometimes|string',
            'options' => 'sometimes|array|min:4', // Ensure at least 4 options
            'correct_answers' => 'sometimes|array',
        ]);

        // Ensure correct_answers contains valid indices
        if (isset($data['correct_answers'])) {
            foreach ($data['correct_answers'] as $index) {
                if (!isset($data['options'][$index])) {
                    return response()->json(['error' => 'Invalid correct_answers index'], 400);
                }
            }
            $data['correct_answers'] = json_encode($data['correct_answers']);
        }

        $test->update($data);

        return response()->json([
            'status' => 'success',
            'data' => $test,
        ]);
    }

    public function checkTest(Request $request, Section $section)
    {
        $data = $request->validate([
            'answers' => 'required|array',
        ]);

        $test = $section->test;
        if (!$test) {
            return response()->json(['error' => 'Test not found for this section'], 404);
        }

        $correctAnswers = json_decode($test->correct_answers, true);
        if (!is_array($correctAnswers)) {
            return response()->json(['error' => 'Invalid correct_answers format'], 400);
        }

        $totalQuestions = count($correctAnswers);
        $correctCount = 0;

        foreach ($data['answers'] as $key => $answer) {
            if (in_array($answer, $correctAnswers[$key])) {
                $correctCount++;
            }
        }

        $score = ($correctCount / $totalQuestions) * 100;

        if ($score >= 90) {
            $nextSection = Section::where('course_id', $section->course_id)
                ->where('order', '>', $section->order)
                ->orderBy('order')
                ->first();

            if ($nextSection) {
                $nextSection->update(['is_unlocked' => true]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Test passed successfully!',
                'score' => $score,
                'next_section_unlocked' => $nextSection ? true : false,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Test failed. Try again!',
            'score' => $score,
        ], 400);
    }

    public function destroy(Test $test)
    {
        $test->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Test deleted',
        ]);
    }
}
