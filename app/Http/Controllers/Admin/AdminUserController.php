<?php
// app/Http/Controllers/Admin/AdminUserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\School;
use App\Models\Schedule;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('school');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                  ->orWhere('lname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by school
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate(20);
        $schools = School::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'schools'));
    }

    public function create()
    {
        $schools = School::orderBy('name')->get();
        return view('admin.users.create', compact('schools'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'required|date',
            'role' => 'required|in:admin,instructor,student',
            'status' => 'required|in:active,inactive,suspended',
            'gender' => 'required|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'idnumber' => 'nullable|string|unique:users,idnumber',
            'school_id' => 'nullable|exists:schools,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $userData = $request->all();
        $userData['password'] = Hash::make($request->password);

        User::create($userData);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully!');
    }

    public function show(User $user)
    {
        $user->load(['school', 'studentSchedules.instructor', 'instructorSchedules.student', 'invoices', 'payments']);
        
        $stats = [
            'total_schedules' => $user->studentSchedules->count() + $user->instructorSchedules->count(),
            'completed_lessons' => $user->studentSchedules->where('status', 'completed')->count(),
            'total_invoices' => $user->invoices->count(),
            'paid_amount' => $user->payments->where('status', 'completed')->sum('amount'),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    public function edit(User $user)
    {
        $schools = School::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'schools'));
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'date_of_birth' => 'required|date',
            'role' => 'required|in:admin,instructor,student',
            'status' => 'required|in:active,inactive,suspended',
            'gender' => 'required|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'idnumber' => ['nullable', 'string', Rule::unique('users')->ignore($user->id)],
            'school_id' => 'nullable|exists:schools,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $userData = $request->except(['password', 'password_confirmation']);
        
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        // Check if user has related data
        if ($user->studentSchedules->count() > 0 || $user->instructorSchedules->count() > 0) {
            return back()->with('error', 'Cannot delete user with existing schedules. Please remove or reassign schedules first.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    public function toggleStatus(User $user)
    {
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        return back()->with('success', "User status updated to {$newStatus}!");
    }

    public function userSchedules(User $user)
    {
        $schedules = collect();
        
        if ($user->role === 'student') {
            $schedules = $user->studentSchedules()->with(['instructor', 'course', 'car'])->paginate(20);
        } elseif ($user->role === 'instructor') {
            $schedules = $user->instructorSchedules()->with(['student', 'course', 'car'])->paginate(20);
        }

        return view('admin.users.schedules', compact('user', 'schedules'));
    }

    public function userInvoices(User $user)
    {
        $invoices = $user->invoices()->with(['payments'])->paginate(20);
        return view('admin.users.invoices', compact('user', 'invoices'));
    }
}