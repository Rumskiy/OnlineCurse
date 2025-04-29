<?php

namespace App\Http\Controllers;

use App\Http\Requests\Section\CreateSection\CreateSectionRequest;
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


    public function store(CreateSectionRequest $request, Section $section)
    {
        $section = Section::create([
            'title' => $request->title,
            'description' => $request->description,
            'contentSection' => $request->contentSection,
            'course_id' => $request->course_id,
        ]);

        if ($request->hasFile('section_video')) {
            $section->addMediaFromRequest('section_video')->toMediaCollection('default');
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


    public function update(Request $request, Section $section)
    {
        $data = $request->validate([
            'title' => 'sometimes|string',
            'content' => 'sometimes|string',
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
