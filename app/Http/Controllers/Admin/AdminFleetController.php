<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Fleet, User, School};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminFleetController extends Controller
{
    private function getSchoolScope()
    {
        $user = auth()->user();

        // Super admins can see all schools, regular admins see only their school
        if ($user->role === 'super_admin') {
            return null; // No restriction
        }

        return $user->school_id;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope();

        $query = Fleet::with(['assignedInstructor', 'assignedInstructor.school'])
                     ->when($schoolId, fn($q) => $q->where('school_id', $schoolId));

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('carplate', 'like', "%{$search}%")
                  ->orWhere('make', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $vehicles = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.fleet.index', compact('vehicles'));
    }

    public function create()
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope();

        // Get instructors for the current school scope
        $instructors = User::where('role', 'instructor')
                          ->where('status', 'active')
                          ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                          ->get();

        return view('admin.fleet.create', compact('instructors'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope() ?: $user->school_id;

        $validator = Validator::make($request->all(), [
            'carplate' => 'required|string|max:20|unique:fleet',
            'make' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'modelyear' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'status' => 'required|in:available,maintenance,retired',
            'instructor' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        Fleet::create([
            'carplate' => $request->carplate,
            'make' => $request->make,
            'model' => $request->model,
            'modelyear' => $request->modelyear,
            'status' => $request->status,
            'instructor' => $request->instructor,
            'school_id' => $schoolId, // Assign to current school
        ]);

        return redirect()->route('admin.fleet.index')
                        ->with('success', 'Vehicle added successfully.');
    }

    // ADD THIS METHOD - Missing show method
    public function show(Fleet $fleet)
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope();

        // Check if user has permission to view this fleet
        if ($schoolId && $fleet->school_id !== $schoolId) {
            abort(403, 'Access denied.');
        }

        // Load relationships
        $fleet->load(['assignedInstructor', 'schedules.student', 'schedules.instructor']);

        return view('admin.fleet.show', compact('fleet'));
    }

    // ADD THIS METHOD - Missing edit method
    public function edit(Fleet $fleet)
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope();

        // Check if user has permission to edit this fleet
        if ($schoolId && $fleet->school_id !== $schoolId) {
            abort(403, 'Access denied.');
        }

        // Get instructors for the current school scope
        $instructors = User::where('role', 'instructor')
                          ->where('status', 'active')
                          ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                          ->get();

        return view('admin.fleet.edit', compact('fleet', 'instructors'));
    }

    // ADD THIS METHOD - Missing update method
    public function update(Request $request, Fleet $fleet)
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope();

        // Check if user has permission to update this fleet
        if ($schoolId && $fleet->school_id !== $schoolId) {
            abort(403, 'Access denied.');
        }

        $validator = Validator::make($request->all(), [
            'carplate' => 'required|string|max:20|unique:fleet,carplate,' . $fleet->id,
            'make' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'modelyear' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'status' => 'required|in:available,maintenance,retired',
            'instructor' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $fleet->update([
            'carplate' => $request->carplate,
            'make' => $request->make,
            'model' => $request->model,
            'modelyear' => $request->modelyear,
            'status' => $request->status,
            'instructor' => $request->instructor,
        ]);

        return redirect()->route('admin.fleet.show', $fleet)
                        ->with('success', 'Vehicle updated successfully.');
    }

    // ADD THIS METHOD - Missing destroy method
    public function destroy(Fleet $fleet)
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope();

        // Check if user has permission to delete this fleet
        if ($schoolId && $fleet->school_id !== $schoolId) {
            abort(403, 'Access denied.');
        }

        // Check if vehicle has any schedules
        if ($fleet->schedules()->count() > 0) {
            return redirect()->route('admin.fleet.index')
                           ->with('error', 'Cannot delete vehicle that has existing schedules.');
        }

        $fleet->delete();

        return redirect()->route('admin.fleet.index')
                        ->with('success', 'Vehicle deleted successfully.');
    }

    // ADDITIONAL METHODS for specific fleet operations

    public function assignInstructor(Request $request, Fleet $fleet)
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope();

        // Check permissions
        if ($schoolId && $fleet->school_id !== $schoolId) {
            abort(403, 'Access denied.');
        }

        $validator = Validator::make($request->all(), [
            'instructor_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $fleet->update(['instructor' => $request->instructor_id]);

        return redirect()->route('admin.fleet.show', $fleet)
                        ->with('success', 'Instructor assigned successfully.');
    }

    public function fleetSchedules(Fleet $fleet)
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope();

        // Check permissions
        if ($schoolId && $fleet->school_id !== $schoolId) {
            abort(403, 'Access denied.');
        }

        $schedules = $fleet->schedules()
                          ->with(['student', 'instructor'])
                          ->orderBy('scheduled_date', 'desc')
                          ->paginate(15);

        return view('admin.fleet.schedules', compact('fleet', 'schedules'));
    }

    // Method for instructors to view their assigned vehicles
    public function instructorVehicles()
    {
        $user = auth()->user();

        $vehicles = Fleet::where('instructor', $user->id)
                        ->where('school_id', $user->school_id)
                        ->with(['schedules' => function($q) {
                            $q->whereDate('scheduled_date', '>=', today())
                              ->orderBy('scheduled_date', 'asc');
                        }])
                        ->get();

        return view('admin.fleet.instructor-vehicles', compact('vehicles'));
    }
}
