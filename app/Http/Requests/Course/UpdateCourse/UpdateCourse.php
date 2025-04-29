<?php

namespace App\Http\Requests\Course\UpdateCourse;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourse extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'title_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
