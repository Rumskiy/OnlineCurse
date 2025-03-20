<?php

namespace App\Http\Requests\Course\CreateCourse;

use Illuminate\Foundation\Http\FormRequest;

class CreateCourse extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
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
