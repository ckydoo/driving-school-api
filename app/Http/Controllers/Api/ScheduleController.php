<?php
// app/Http/Controllers/Api/ScheduleController.php

namespace App\Http\Controllers\Api;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ScheduleController extends BaseController
{
    public function index(Request $request)
    {
        $query = Schedule::with(['student', 'instructor', 'course', 'vehicle']);

        // Filter by student
        if ($request->has('student')) {
            $query->where('student', $request->student);
        }

        // Filter by instructor
        if ($request->has('instructor')) {
            $query->where('instructor', $request->instructor_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('start', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('start', '<=', $request->end_date);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $schedules = $query->orderBy('start', 'asc')->get();

        return $this->sendResponse($schedules, 'Schedules retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student' => 'required|exists:users,id',
            'instructor' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'car' => 'nullable|exists:fleet,id',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'class_type' => 'required|in:practical,theory',
            'lessons_deducted' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        // Check for scheduling conflicts
        $conflict = Schedule::where('instructor_id', $request->instructor_id)
            ->where(function($query) use ($request) {
                $query->whereBetween('start', [$request->start, $request->end])
                      ->orWhereBetween('end', [$request->start, $request->end])
                      ->orWhere(function($q) use ($request) {
                          $q->where('start', '<=', $request->start)
                            ->where('end', '>=', $request->end);
                      });
            })
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($conflict) {
            return $this->sendError('Scheduling conflict detected for this instructor.');
        }

        $schedule = Schedule::create($request->all());
        $schedule->load(['student', 'instructor', 'course', 'vehicle']);

        return $this->sendResponse($schedule, 'Schedule created successfully.');
    }

    public function show($id)
    {
        $schedule = Schedule::with(['student', 'instructor', 'course', 'vehicle'])->find($id);

        if (is_null($schedule)) {
            return $this->sendError('Schedule not found.');
        }

        return $this->sendResponse($schedule, 'Schedule retrieved successfully.');
    }

    public function update(Request $request, $id)
    {
        $schedule = Schedule::find($id);

        if (is_null($schedule)) {
            return $this->sendError('Schedule not found.');
        }

        $validator = Validator::make($request->all(), [
            'student' => 'sometimes|required|exists:users,id',
            'instructor' => 'sometimes|required|exists:users,id',
            'course_id' => 'sometimes|required|exists:courses,id',
            'car' => 'nullable|exists:fleet,id',
            'start' => 'sometimes|required|date',
            'end' => 'sometimes|required|date|after:start',
            'class_type' => 'sometimes|required|in:practical,theory',
            'status' => 'sometimes|required|in:scheduled,completed,cancelled,no_show',
            'attended' => 'sometimes|boolean',
            'lessons_deducted' => 'sometimes|required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $schedule->update($request->only([
            'student', 'instructor', 'course_id', 'car',
            'start', 'end', 'class_type', 'status', 'attended',
            'lessons_deducted', 'notes', 'instructor_notes'
        ]));

        $schedule->load(['student', 'instructor', 'course', 'vehicle']);

        return $this->sendResponse($schedule, 'Schedule updated successfully.');
    }

    public function destroy($id)
    {
        $schedule = Schedule::find($id);

        if (is_null($schedule)) {
            return $this->sendError('Schedule not found.');
        }

        $schedule->delete();

        return $this->sendResponse([], 'Schedule deleted successfully.');
    }

    public function markAttended(Request $request, $id)
    {
        $schedule = Schedule::find($id);

        if (is_null($schedule)) {
            return $this->sendError('Schedule not found.');
        }

        $schedule->update([
            'attended' => true,
            'status' => 'completed',
            'instructor_notes' => $request->instructor_notes
        ]);

        return $this->sendResponse($schedule, 'Schedule marked as attended.');
    }
}
