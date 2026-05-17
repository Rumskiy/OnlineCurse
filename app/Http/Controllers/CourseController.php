<?php

namespace App\Http\Controllers;

use App\Http\Requests\Course\CreateCourse\CreateCourse;
use App\Http\Requests\Course\UpdateCourse\UpdateCourse;
use App\Http\Resources\Course\CourseResource;
use App\Models\Course;
use App\Services\CourseService;
use App\Repositories\Contracts\CourseRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    protected $courseService;
    protected $courseRepository;

    public function __construct(CourseService $courseService, CourseRepositoryInterface $courseRepository)
    {
        $this->courseService = $courseService;
        $this->courseRepository = $courseRepository;
    }

    public function index(Request $request)
    {
        $courses = $this->courseRepository->all();
        return $this->sendJsonWithData($courses, CourseResource::class);
    }

    public function userCourses(Request $request)
    {
        $user = $request->user();
        $courses = $this->courseRepository->findByAuthorId($user->id);
        return $this->sendJsonWithData($courses, CourseResource::class);
    }

    public function store(CreateCourse $request)
    {
        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'author_id' => Auth::id(),
        ];

        $imageFile = $request->file('title_img');
        $course = $this->courseService->createCourse($data, $imageFile);

        return $this->sendJsonWithData($course, CourseResource::class);
    }

    public function show($id)
    {
        $course = $this->courseRepository->findById($id);

        if (!$course) {
            return response()->json(['message' => 'Курс не знайдено'], 404);
        }

        return $this->sendJsonWithData($course, CourseResource::class);
    }

    public function update(UpdateCourse $request, Course $course)
    {
        $data = $request->only(['title', 'description', 'category_id']);
        $imageFile = $request->file('title_img');

        $updatedCourse = $this->courseService->updateCourse($course, $data, $imageFile);

        return $this->sendJsonWithData($updatedCourse, CourseResource::class);
    }

    public function byCategoryId(Request $request, $id)
    {
        $courses = $this->courseRepository->findByCategoryId($id);
        return $this->sendJsonWithData($courses, CourseResource::class);
    }

    public function destroy(Course $course)
    {
        $this->courseService->deleteCourse($course);
        return response()->json(['message' => 'Course deleted']);
    }
}
