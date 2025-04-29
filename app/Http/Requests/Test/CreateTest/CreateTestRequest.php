<?php

namespace App\Http\Requests\Test\CreateTest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Або додай логіку авторизації
    }

    public function rules(): array
    {
        return [
            'section_id' => 'required|exists:sections,id|unique:tests,section_id', // Якщо тест УНІКАЛЬНИЙ для секції
            'title' => 'required|string|max:255',
            'total_time_limit' => 'nullable|integer|min:1',
            'time_per_question' => 'nullable|integer|min:5',

            'questions' => 'required|array|min:1', // Масив питань
            'questions.*.type' => ['required', Rule::in(['single_choice', 'match', 'multiple_choice'])],
            'questions.*.text' => 'required|string',
            'questions.*.order' => 'required|integer|min:0',
            'questions.*.points' => 'sometimes|integer|min:1',
            'questions.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'questions.*.options' => 'required_if:questions.*.type,single_choice,multiple_choice|array|min:2',
            'questions.*' => [function ($attribute, $value, $fail) { /* ... */
            }],

            'questions.*.match_pairs' => 'required_if:questions.*.type,match|array|min:2',
        ];
    }
}
