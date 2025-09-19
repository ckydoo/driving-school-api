<?php
// app/Http/Controllers/Admin/AdminSchoolController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminSchoolController extends Controller
{
    public function index(Request $request)
    {
        $query = School::withCount(['users', 'admins']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by country
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $schools = $query->paginate(20);
        $countries = School::distinct()->pluck('country')->sort();

        return view('admin.schools.index', compact('schools', 'countries'));
    }

    public function create()
    {
        return view('admin.schools.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:schools,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'website' => 'nullable|url|max:255',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'operating_days' => 'required|array',
            'operating_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Generate unique invitation code
        $invitationCode = $this->generateInvitationCode();

        $school = School::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'country' => $request->country,
            'website' => $request->website,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'operating_days' => $request->operating_days,
            'invitation_code' => $invitationCode,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.schools.show', $school)
            ->with('success', 'School created successfully! Invitation code: ' . $invitationCode);
    }

    public function show(School $school)
    {
        $school->load(['users']);
        
        $stats = [
            'total_users' => $school->users->count(),
            'students' => $school->users->where('role', 'student')->count(),
            'instructors' => $school->users->where('role', 'instructor')->count(),
            'admins' => $school->users->where('role', 'admin')->count(),
            'active_users' => $school->users->where('status', 'active')->count(),
        ];

        $recentUsers = $school->users()->orderBy('created_at', 'desc')->take(10)->get();

        return view('admin.schools.show', compact('school', 'stats', 'recentUsers'));
    }

    public function edit(School $school)
    {
        return view('admin.schools.edit', compact('school'));
    }

    public function update(Request $request, School $school)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('schools')->ignore($school->id)],
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'website' => 'nullable|url|max:255',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'operating_days' => 'required|array',
            'operating_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $school->update($request->only([
            'name', 'email', 'phone', 'address', 'city', 'country', 
            'website', 'start_time', 'end_time', 'operating_days', 'status'
        ]));

        return redirect()->route('admin.schools.show', $school)
            ->with('success', 'School updated successfully!');
    }

    public function destroy(School $school)
    {
        // Check if school has users
        if ($school->users()->count() > 0) {
            return back()->with('error', 'Cannot delete school with existing users. Please remove or transfer users first.');
        }

        $school->delete();

        return redirect()->route('admin.schools.index')
            ->with('success', 'School deleted successfully!');
    }

    public function toggleStatus(School $school)
    {
        $newStatus = $school->status === 'active' ? 'inactive' : 'active';
        $school->update(['status' => $newStatus]);

        return back()->with('success', "School status updated to {$newStatus}!");
    }

    public function schoolUsers(School $school)
    {
        $users = $school->users()->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.schools.users', compact('school', 'users'));
    }

    private function generateInvitationCode()
    {
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
        } while (School::where('invitation_code', $code)->exists());

        return $code;
    }
}