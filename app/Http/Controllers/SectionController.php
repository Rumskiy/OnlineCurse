<?php

namespace App\Http\Controllers;

use App\Http\Requests\Section\CreateSection\CreateSectionRequest;
use App\Http\Requests\Section\UpdateSection\UpdateSectionRequest;
use App\Http\Resources\Course\CourseResource;
use App\Http\Resources\Section\SectionResource;
use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index($courseId)
    {
        $sections = Section::where('course_id', $courseId)->orderBy('order')->get();

        return response()->json(SectionResource::collection($sections));
    }


    public function store(CreateSectionRequest $request) // Змінив Section $section на CreateSectionRequest
    {
        // Ваша логіка створення
        $section = Section::create([
            'title' => $request->title,
            'description' => $request->description,
            'contentSection' => $request->contentSection,
            'course_id' => $request->course_id,
        ]);

        // У вашому store методі ви використовували 'section_video' для відео
        // Тепер для файлу домашнього завдання будемо використовувати 'section_file'
        if ($request->hasFile('section_file')) {
            // Ви можете створити окрему медіа-колекцію для файлів
            $section->addMediaFromRequest('section_file')->toMediaCollection('section_files');
        }

        // Якщо є відео
        if ($request->hasFile('section_video')) {
            $section->addMediaFromRequest('section_video')->toMediaCollection('section_videos');
        }


        return $this->sendJsonWhisData($section, SectionResource::class);
    }

    public function show($id)
    {
        $section = Section::find($id);

        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        return response()->json($section);
    }


    public function update(UpdateSectionRequest $request, Section $section)
    {
        $validatedData = $request->validated(); // Отримуємо валідовані дані

        // Оновлюємо текстові поля
        $section->update([
            'title' => $validatedData['title'] ?? $section->title,
            'description' => $validatedData['description'] ?? $section->description,
            'contentSection' => $validatedData['contentSection'] ?? $section->contentSection,
            // course_id зазвичай не оновлюється, але якщо потрібно, додайте
        ]);

        // Обробка завантаження нового файлу
        if ($request->hasFile('section_file')) {
            // Опціонально: видалити попередній файл, якщо він існував
            // Потрібно вказати назву колекції, яку ви використовуєте для файлів секції
            $section->clearMediaCollection('section_files'); // Або інша назва вашої колекції для файлів
            $section->addMediaFromRequest('section_file')->toMediaCollection('section_files');
        } elseif ($request->boolean('remove_section_file')) {
            // Якщо прийшов прапорець на видалення файлу
            $section->clearMediaCollection('section_files');
        }

        // Обробка завантаження нового відео (якщо потрібно оновлювати і відео тут)
        if ($request->hasFile('section_video')) {
            $section->clearMediaCollection('section_videos');
            $section->addMediaFromRequest('section_video')->toMediaCollection('section_videos');
        } elseif ($request->boolean('remove_section_video')) {
            $section->clearMediaCollection('section_videos');
        }


        // Повертаємо оновлену секцію через ресурс
        // Перезавантажуємо модель, щоб ресурс отримав оновлені медіа (якщо потрібно)
        return $this->sendJsonWhisData($section->fresh(), SectionResource::class);
    }

    public function destroy(Section $section)
    {
        $section->delete();

        return response()->json(['message' => 'Section deleted']);
    }
}
