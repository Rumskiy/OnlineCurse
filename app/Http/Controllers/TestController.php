<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Test;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string',
            'options' => 'required|array',
            'correct_answers' => 'required|array',
            'section_id' => 'required|exists:sections,id',
        ]);

        $data['correct_answers'] = json_encode($data['correct_answers']);
        $test = Test::create($data);

        return response()->json($test, 201);
    }

    public function update(Request $request, Test $test)
    {
        $data = $request->validate([
            'question' => 'sometimes|string',
            'options' => 'sometimes|array',
            'correct_answers' => 'sometimes|array',
        ]);

        $test->update($data);

        return response()->json($test);
    }

    public function checkTest(Request $request, Section $section)
    {
        $data = $request->validate([
            'answers' => 'required|array',
        ]);

        $correctAnswers = json_decode($section->test->correct_answers, true);
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
                'message' => 'Test passed successfully!',
                'score' => $score,
                'next_section_unlocked' => $nextSection ? true : false,
            ]);
        }

        return response()->json([
            'message' => 'Test failed. Try again!',
            'score' => $score,
        ], 400);
    }


    public function destroy(Test $test)
    {
        $test->delete();

        return response()->json(['message' => 'Test deleted']);
    }
}

