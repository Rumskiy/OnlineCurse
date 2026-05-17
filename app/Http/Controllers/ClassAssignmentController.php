<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SchoolClass;
use App\Models\Course;

class ClassAssignmentController extends Controller
{
    /**
     * Get all classes, courses, and current assignments for the teacher's school.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->school_id) {
            return response()->json([
                'classes' => [],
                'courses' => [],
                'assignments' => []
            ]);
        }

        // Get classes belonging to the same school
        $classes = SchoolClass::where('school_id', $user->school_id)
            ->select('id', 'name', 'academic_year')
            ->get();

        // Get all available courses
        $courses = Course::select('id', 'title', 'description')
            ->get();

        // Get current assignments with course and class details
        $assignments = DB::table('class_course')
            ->join('school_classes', 'class_course.school_class_id', '=', 'school_classes.id')
            ->join('courses', 'class_course.course_id', '=', 'courses.id')
            ->leftJoin('users', 'class_course.teacher_id', '=', 'users.id')
            ->where('school_classes.school_id', $user->school_id)
            ->select(
                'class_course.id',
                'class_course.school_class_id',
                'class_course.course_id',
                'class_course.teacher_id',
                'class_course.start_date',
                'class_course.end_date',
                'school_classes.name as class_name',
                'courses.title as course_title',
                DB::raw("CONCAT(users.firstName, ' ', users.lastName) as teacher_name")
            )
            ->orderBy('school_classes.name')
            ->get();

        return response()->json([
            'classes' => $classes,
            'courses' => $courses,
            'assignments' => $assignments
        ]);
    }

    /**
     * Assign a course to a school class.
     */
    public function assign(Request $request)
    {
        $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'course_id' => 'required|exists:courses,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $user = $request->user();

        // Verify that the class belongs to the teacher's school
        $class = SchoolClass::where('id', $request->school_class_id)
            ->where('school_id', $user->school_id)
            ->first();

        if (!$class) {
            return response()->json([
                'message' => 'Клас не належить до вашої школи або не існує.'
            ], 403);
        }

        // Check if the assignment already exists
        $exists = DB::table('class_course')
            ->where('school_class_id', $request->school_class_id)
            ->where('course_id', $request->course_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Цей курс вже призначено обраному класу.'
            ], 422);
        }

        // Create assignment
        DB::table('class_course')->insert([
            'school_class_id' => $request->school_class_id,
            'course_id' => $request->course_id,
            'teacher_id' => $user->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Курс успішно призначено класу!'
        ]);
    }

    /**
     * Unassign a course from a school class.
     */
    public function unassign(Request $request, $id)
    {
        $user = $request->user();

        // Verify the assignment belongs to the teacher's school
        $assignment = DB::table('class_course')
            ->join('school_classes', 'class_course.school_class_id', '=', 'school_classes.id')
            ->where('class_course.id', $id)
            ->where('school_classes.school_id', $user->school_id)
            ->select('class_course.id')
            ->first();

        if (!$assignment) {
            return response()->json([
                'message' => 'Призначення не знайдено або воно не належить вашій школі.'
            ], 404);
        }

        // Delete assignment
        DB::table('class_course')->where('id', $id)->delete();

        return response()->json([
            'message' => 'Курс успішно відкріплено від класу.'
        ]);
    }
}
