<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller {
    public function index(Request $request)
    {
        $query = Course::with('Rategory');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        return response()->json([
            'data' => $query->paginate(24)
        ]);
    }

    public function userCourses(Request $request)
    {
        $user = $request->user();

        $courses = Course::where($user->id)
            ->with('Rategory')
            ->get();

        return response()->json($courses);
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'title_img' => 'nullable|string',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $data['author_id'] = auth()->id();

        $course = Course::create($data);

        return response()->json($course, 201);
    }

    public function show(Course $course) {
        return response()->json($course->load('Rategory', 'author'));
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'title' => 'sometimes|string',
            'title_img' => 'sometimes|string',
            'description' => 'sometimes|string',
            'category_id' => 'sometimes|exists:categories,id',
        ]);

        $course->update($data);

        return response()->json($course);
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return response()->json(['message' => 'Course deleted']);
    }
}

