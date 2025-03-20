<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index($courseId)
    {
        $sections = Section::where('course_id', $courseId)->orderBy('order')->get();
        return response()->json($sections);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'title_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
            'section_video' => 'nullable|mimetypes:video/mp4,video/quicktime|max:102400', // 100MB max
            'content' => 'nullable|json',
            'course_id' => 'required|exists:courses,id',
            'order' => 'required|integer',
        ]);

        $section = Section::create($data);

        if ($request->hasFile('title_img')) {
            $section->addMediaFromRequest('title_img')->toMediaCollection('title_images');
        }

        return response()->json($section, 201);
    }



    public function show(Section $section)
    {
        return response()->json($section);
    }

    public function update(Request $request, Section $section)
    {
        $data = $request->validate([
            'title' => 'sometimes|string',
            'content' => 'sometimes|string',
            'order' => 'sometimes|integer',
        ]);

        $section->update($data);

        return response()->json($section);
    }

    public function destroy(Section $section)
    {
        $section->delete();

        return response()->json(['message' => 'Section deleted']);
    }
}
