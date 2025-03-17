<?php

namespace App\Http\Controllers;

use App\Models\Test;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string',
            'options' => 'required|array',
            'correct_answer' => 'required|integer',
            'section_id' => 'required|exists:sections,id',
        ]);

        $test = Test::create([
            'question' => $data['question'],
            'options' => json_encode($data['options']),
            'correct_answer' => $data['correct_answer'],
            'section_id' => $data['section_id'],
        ]);

        return response()->json($test, 201);
    }

    public function update(Request $request, Test $test)
    {
        $data = $request->validate([
            'question' => 'sometimes|string',
            'options' => 'sometimes|array',
            'correct_answer' => 'sometimes|integer',
        ]);

        $test->update([
            'question' => $data['question'] ?? $test->question,
            'options' => isset($data['options']) ? json_encode($data['options']) : $test->options,
            'correct_answer' => $data['correct_answer'] ?? $test->correct_answer,
        ]);

        return response()->json($test);
    }

    public function destroy(Test $test)
    {
        $test->delete();

        return response()->json(['message' => 'Test deleted']);
    }
}

