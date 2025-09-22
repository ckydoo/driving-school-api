<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\{Fleet, User, School};
use Illuminate\Support\Facades\Validator;

class AdminFleetController extends Controller
{
    /**
     * Ensure admin access and get current user
     */
    protected function ensureAdminAccess()
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }
        return $user;
    }

    /**
     * Apply school filtering to query based on user role - FIXED VERSION
     */
    protected function applySchoolFilter($query, $currentUser)
    {
        // If school admin, restrict to their school only
        if (!$currentUser->isSuperAdmin() && $currentUser->school_id) {
            $query->where(function($q) use ($currentUser) {
                $q->where('school_id', $currentUser->school_id)
                  ->orWhereNull('school_id'); // Include vehicles without school assignment for now
            });
        }

        return $query;
    }

    /**
     * Check if user can access this vehicle - FIXED VERSION
     */
    protected function canAccessVehicle($currentUser, $vehicle)
    {
        // Super admins can access all vehicles
        if ($currentUser->isSuperAdmin()) {
            return true;
        }

        // School admins can access vehicles from their school OR vehicles without school assignment
        if ($currentUser->school_id) {
            return $vehicle->school_id === $currentUser->school_id || $vehicle->school_id === null;
        }

        return false;
    }

    /**
     * FIXED SHOW METHOD - Display vehicle with improved access control
     */
    public function show(Fleet $vehicle)
    {
        $currentUser = $this->ensureAdminAccess();

        // Check if user can access this vehicle
        if (!$this->canAccessVehicle($currentUser, $vehicle)) {
            abort(403, 'Access denied. You can only view vehicles from your school.');
        }

        // Auto-assign school_id if it's missing and user is school admin
        if (!$vehicle->school_id && $currentUser->school_id) {
            $vehicle->update(['school_id' => $currentUser->school_id]);
            $vehicle->refresh();
        }

        $vehicle->load(['assignedInstructor', 'schedules.student', 'schedules.instructor']);

        return view('admin.fleet.show', compact('vehicle', 'currentUser'));
    }

    /**
     * FIXED EDIT METHOD - Edit form with improved access control
     */
    public function edit(Fleet $vehicle)
    {
        $currentUser = $this->ensureAdminAccess();

        // Check if user can edit this vehicle
        if (!$this->canAccessVehicle($currentUser, $vehicle)) {
            abort(403, 'Access denied. You can only edit vehicles from your school.');
        }

        // Auto-assign school_id if it's missing and user is school admin
        if (!$vehicle->school_id && $currentUser->school_id) {
            $vehicle->update(['school_id' => $currentUser->school_id]);
            $vehicle->refresh();
        }

        $instructors = $this->getAvailableInstructors($currentUser);

        return view('admin.fleet.edit', compact('vehicle', 'instructors', 'currentUser'));
    }

    /**
     * FIXED UPDATE METHOD - Update with improved access control
     */
    public function update(Request $request, Fleet $vehicle)
    {
        $currentUser = $this->ensureAdminAccess();

        // Check if user can update this vehicle
        if (!$this->canAccessVehicle($currentUser, $vehicle)) {
            abort(403, 'Access denied. You can only edit vehicles from your school.');
        }

        $validator = Validator::make($request->all(), [
            'carplate' => 'required|string|max:20|unique:fleet,carplate,' . $vehicle->id,
            'make' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'modelyear' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'status' => 'required|in:available,maintenance,retired',
            'instructor' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validate instructor belongs to correct school (for school admins)
        if ($request->instructor && !$currentUser->isSuperAdmin()) {
            $instructor = User::find($request->instructor);
            if ($instructor && $instructor->school_id !== $currentUser->school_id) {
                return back()->with('error', 'Selected instructor does not belong to your school.');
            }
        }

        $updateData = $request->all();

        // Ensure school_id is set correctly
        if (!$currentUser->isSuperAdmin()) {
            $updateData['school_id'] = $currentUser->school_id;
        } elseif (!isset($updateData['school_id'])) {
            $updateData['school_id'] = $vehicle->school_id ?: $currentUser->school_id;
        }

        $vehicle->update($updateData);

        return redirect()->route('admin.fleet.show', $vehicle)
            ->with('success', 'Vehicle updated successfully.');
    }

    /**
     * FIXED DESTROY METHOD - Delete with improved access control
     */
    public function destroy(Fleet $vehicle)
    {
        $currentUser = $this->ensureAdminAccess();

        // Check if user can delete this vehicle
        if (!$this->canAccessVehicle($currentUser, $vehicle)) {
            abort(403, 'Access denied. You can only delete vehicles from your school.');
        }

        // Check if vehicle is being used in schedules
        if ($vehicle->schedules()->count() > 0) {
            return back()->with('error', 'Cannot delete vehicle that is being used in schedules.');
        }

        $vehicle->delete();

        return redirect()->route('admin.fleet.index')
            ->with('success', 'Vehicle deleted successfully.');
    }

    /**
     * FIXED ASSIGN INSTRUCTOR METHOD - Improved access control
     */
    public function assignInstructor(Request $request, Fleet $vehicle)
    {
        $currentUser = $this->ensureAdminAccess();

        // Check if user can modify this vehicle
        if (!$this->canAccessVehicle($currentUser, $vehicle)) {
            abort(403, 'Access denied. You can only modify vehicles from your school.');
        }

        $validator = Validator::make($request->all(), [
            'instructor' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        // Validate instructor belongs to correct school (for school admins)
        if ($request->instructor && !$currentUser->isSuperAdmin()) {
            $instructor = User::find($request->instructor);
            if ($instructor && $instructor->school_id !== $currentUser->school_id) {
                return back()->with('error', 'Selected instructor does not belong to your school.');
            }
        }

        $updateData = ['instructor' => $request->instructor];

        // Auto-assign school_id if it's missing
        if (!$vehicle->school_id && $currentUser->school_id) {
            $updateData['school_id'] = $currentUser->school_id;
        }

        $vehicle->update($updateData);

        $message = $request->instructor
            ? 'Instructor assigned successfully.'
            : 'Instructor unassigned successfully.';

        return back()->with('success', $message);
    }

    /**
     * FIXED FLEET SCHEDULES METHOD - Improved access control
     */
    public function fleetSchedules(Fleet $vehicle)
    {
        $currentUser = $this->ensureAdminAccess();

        // Check if user can access this vehicle
        if (!$this->canAccessVehicle($currentUser, $vehicle)) {
            abort(403, 'Access denied. You can only view schedules for vehicles from your school.');
        }

        $schedules = $vehicle->schedules()
            ->with(['student', 'instructor', 'course'])
            ->orderBy('start', 'desc')
            ->paginate(20);

        return view('admin.fleet.schedules', compact('vehicle', 'schedules', 'currentUser'));
    }

    /**
     * Get available instructors based on user's school
     */
    protected function getAvailableInstructors($currentUser)
    {
        $query = User::where('role', 'instructor')->where('status', 'active');

        if (!$currentUser->isSuperAdmin() && $currentUser->school_id) {
            $query->where('school_id', $currentUser->school_id);
        }

        return $query->orderBy('fname')->orderBy('lname')->get();
    }

    /**
     * FIXED INDEX METHOD - Display fleet with proper school filtering
     */
    public function index(Request $request)
    {
        $currentUser = $this->ensureAdminAccess();

        $query = Fleet::with(['assignedInstructor']);

        // Apply school filtering first
        $query = $this->applySchoolFilter($query, $currentUser);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('carplate', 'like', "%{$search}%")
                  ->orWhere('make', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by make
        if ($request->filled('make')) {
            $query->where('make', $request->make);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $vehicles = $query->paginate(15);

        return view('admin.fleet.index', compact('vehicles', 'currentUser'));
    }

    /**
     * FIXED CREATE METHOD - Show form with school-specific data
     */
    public function create()
    {
        $currentUser = $this->ensureAdminAccess();

        $instructors = $this->getAvailableInstructors($currentUser);

        return view('admin.fleet.create', compact('instructors', 'currentUser'));
    }

    /**
     * FIXED STORE METHOD - Create vehicle with proper school assignment
     */
    public function store(Request $request)
    {
        $currentUser = $this->ensureAdminAccess();

        $validator = Validator::make($request->all(), [
            'carplate' => 'required|string|max:20|unique:fleet',
            'make' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'modelyear' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'status' => 'required|in:available,maintenance,retired',
            'instructor' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validate instructor belongs to correct school (for school admins)
        if ($request->instructor && !$currentUser->isSuperAdmin()) {
            $instructor = User::find($request->instructor);
            if ($instructor && $instructor->school_id !== $currentUser->school_id) {
                return back()->with('error', 'Selected instructor does not belong to your school.');
            }
        }

        $vehicleData = $request->all();
        $vehicleData['school_id'] = $currentUser->isSuperAdmin()
            ? ($request->school_id ?? $currentUser->school_id)
            : $currentUser->school_id;

        Fleet::create($vehicleData);

        return redirect()->route('admin.fleet.index')
            ->with('success', 'Vehicle added successfully.');
    }

  

    /**
     * Get vehicles for instructor dashboard
     */
    public function instructorVehicles()
    {
        $currentUser = Auth::user();

        if (!$currentUser || !in_array($currentUser->role, ['super_admin', 'admin', 'instructor'])) {
            abort(403, 'Access denied. Instructor privileges required.');
        }

        // Get vehicles assigned to this instructor
        $vehicles = Fleet::where('instructor', $currentUser->id)
            ->with(['schedules' => function($query) {
                $query->where('start', '>=', now())->orderBy('start');
            }])
            ->get();

        return view('instructor.vehicles', compact('vehicles', 'currentUser'));
    }
}