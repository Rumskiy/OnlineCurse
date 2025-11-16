<?php

namespace App\Http\Requests\Section\UpdateSection;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Тут можна додати логіку авторизації, наприклад,
        // чи може поточний користувач редагувати цю секцію
        return true; // Поки що дозволяємо всім
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'contentSection' => 'sometimes|string',
            // 'course_id' => 'sometimes|exists:courses,id', // Якщо дозволяєте змінювати курс
            'section_file' => 'nullable|file|mimes:pdf,doc,docx,zip,rar,txt,jpg,jpeg,png|max:10240', // Приклад: PDF, DOC, ZIP до 10MB
            'remove_section_file' => 'sometimes|boolean', // Прапорець для видалення файлу
            'section_video' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:204800', // Приклад: Відео до 200MB
            'remove_section_video' => 'sometimes|boolean', // Прапорець для видалення відео
        ];
    }
}
