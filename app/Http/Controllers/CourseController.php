<?php

namespace App\Http\Controllers;

use App\Http\Requests\Course\CreateCourse\CreateCourse;
use App\Http\Requests\Course\UpdateCourse\UpdateCourse;
use App\Http\Resources\Course\CourseResource;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $data = Course::all();

        return $this->sendJsonWithData($data, CourseResource::class);
    }

    public function userCourses(Request $request)
    {
        $user = $request->user();

        $courses = Course::where('author_id', $user->id)
            ->with('category')
            ->get();

        return $this->sendJsonWithData($courses, CourseResource::class);
    }


    public function store(CreateCourse $request, Course $course)
    {
        $course = Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'author_id' => Auth::id(),
        ]);

        if ($request->hasFile('title_img')) {
            $course->addMediaFromRequest('title_img')->toMediaCollection('default');
        }


        return $this->sendJsonWithData($course, CourseResource::class);
    }


    public function show(Course $course)
    {
        $course->load(['category', 'author']);
        return $this->sendJsonWithData($course, CourseResource::class);
//        return response()->json([
//            'data' => $course->load('category', 'author')
//        ]);
    }

    public function update(UpdateCourse $request, Course $course)
    {

        if ($request->hasFile('title_img')) {
            $course->clearMediaCollection('default');
            $course->addMediaFromRequest('title_img')->toMediaCollection('default');
        }

        $course->update($request->all());

        return $this->sendJsonWithData($course, CourseResource::class);
    }

    public function byCategoryId(Request $request, $id)
    {
        $courses = Course::where('category_id', $id)
            ->with(['category', 'author'])
            ->get();

        return $this->sendJsonWithData($courses, CourseResource::class);
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return response()->json(['message' => 'Course deleted']);
    }
}
