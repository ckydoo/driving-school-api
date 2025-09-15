<?php
// app/Http/Controllers/Api/CourseController.php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CourseController extends BaseController
{
    public function index(Request $request)
    {
        $query = Course::query();

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $courses = $query->orderBy('name', 'asc')->get();

        return $this->sendResponse($courses, 'Courses retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:courses',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'lessons' => 'required|integer|min:1',
            'type' => 'required|in:practical,theory,combined',
            'duration_minutes' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $course = Course::create($request->all());

        return $this->sendResponse($course, 'Course created successfully.');
    }

    public function show($id)
    {
        $course = Course::find($id);

        if (is_null($course)) {
            return $this->sendError('Course not found.');
        }

        return $this->sendResponse($course, 'Course retrieved successfully.');
    }

    public function update(Request $request, $id)
    {
        $course = Course::find($id);

        if (is_null($course)) {
            return $this->sendError('Course not found.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:courses,name,' . $id,
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'lessons' => 'sometimes|required|integer|min:1',
            'type' => 'sometimes|required|in:practical,theory,combined',
            'status' => 'sometimes|required|in:active,inactive',
            'duration_minutes' => 'sometimes|required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $course->update($request->only([
            'name', 'description', 'price', 'lessons', 'type', 'status', 'requirements', 'duration_minutes'
        ]));

        return $this->sendResponse($course, 'Course updated successfully.');
    }

    public function destroy($id)
    {
        $course = Course::find($id);

        if (is_null($course)) {
            return $this->sendError('Course not found.');
        }

        // Check if course is being used
        if ($course->schedules()->count() > 0 || $course->invoices()->count() > 0) {
            return $this->sendError('Cannot delete course that is being used in schedules or invoices.');
        }

        $course->delete();

        return $this->sendResponse([], 'Course deleted successfully.');
    }
}
