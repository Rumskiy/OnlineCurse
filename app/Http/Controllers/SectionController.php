<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'content' => 'nullable|string',
            'course_id' => 'required|exists:courses,id',
            'order' => 'required|integer',
        ]);

        $section = Section::create($data);

        return response()->json($section, 201);
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

