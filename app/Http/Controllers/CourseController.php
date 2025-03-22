<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

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

        return response()->json([
            'data' => $courses
        ]);
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'title_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data['author_id'] = Auth::id();

        $course = Course::create($data);

        $course->addMediaFromRequest('image')->toMediaCollection('images');

        return response()->json(['data' => $course], 201);
    }


    public function show(Course $course) {
        return response()->json([
            'data' => $course->load('category', 'author')
        ]);
    }


    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|integer|exists:categories,id',
            'title_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Delete old image if a new one is uploaded
        if ($request->hasFile('title_img')) {
            $course->clearMediaCollection('images');
            $course->addMediaFromRequest('title_img')->toMediaCollection('images');
        }

        $course->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Course updated successfully',
            'data' => $course,
        ]);
    }



    public function destroy(Course $course)
    {
        $course->delete();

        return response()->json(['message' => 'Course deleted']);
    }
}
