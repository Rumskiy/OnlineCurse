<?php

namespace App\Http\Requests\Section\UpdateSection;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSection extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'content_section' => ['required', 'json'],
            'course_id' => ['required', 'exists:categories,id'],
            'section_img' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }
}
