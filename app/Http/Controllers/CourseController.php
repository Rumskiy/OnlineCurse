<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller {
    public function index(Request $request)
    {
        $query = Course::with('category');

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

        $courses = Course::where('author_id', $user->id)
        ->with('category')
            ->get();

        return response()->json($courses);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'title_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data['author_id'] = Auth::user()->id;

        $course = Course::create($data);

        // Upload title image
        if ($request->hasFile('title_img')) {
            $course->addMediaFromRequest('title_img')->toMediaCollection('title_images');
        }

        return response()->json($course, 201);
    }

    public function show(Course $course) {
        return response()->json($course->load('category', 'author'));
    }

    public function update(Request $request, $id)
    {
        // Validate the request
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'title_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Allow nullable image
        ]);

        // Find the course
        $course = Course::findOrFail($id);

        // Update the course
        $course->update($data);

        // Handle file upload
        if ($request->hasFile('title_img')) {
            $course->clearMediaCollection('title_images');
            $course->addMediaFromRequest('title_img')->toMediaCollection('title_images');
        }

        return response()->json($course);
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return response()->json(['message' => 'Course deleted']);
    }
}
