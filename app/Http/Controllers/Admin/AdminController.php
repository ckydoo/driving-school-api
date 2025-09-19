<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, School, Fleet, Course, Schedule, Invoice};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope($request);

        // Dashboard statistics scoped to school
        $stats = [
            'total_students' => User::when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                                  ->where('role', 'student')
                                  ->count(),
            
            'total_instructors' => User::when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                                     ->where('role', 'instructor') 
                                     ->count(),
            
            'total_vehicles' => Fleet::when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                                   ->count(),
            
            'active_schedules' => Schedule::when($schoolId, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('school_id', $schoolId)))
                                        ->where('date', '>=', today())
                                        ->count(),
            
            'pending_invoices' => Invoice::when($schoolId, fn($q) => $q->whereHas('studentUser', fn($sq) => $sq->where('school_id', $schoolId)))
                                        ->where('status', 'pending')
                                        ->count(),
        ];

        // Recent activities scoped to school
        $recentUsers = User::when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                          ->latest()
                          ->take(5)
                          ->get();

        $recentSchedules = Schedule::when($schoolId, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('school_id', $schoolId)))
                                 ->with(['student', 'instructor', 'vehicle'])
                                 ->latest()
                                 ->take(5)
                                 ->get();

        // School info for context
        $school = $schoolId ? School::find($schoolId) : null;

        return view('admin.dashboard', compact(
            'stats', 
            'recentUsers', 
            'recentSchedules',
            'school'
        ));
    }

    /**
     * Get the school scope based on user role
     */
    private function getSchoolScope(Request $request)
    {
        $user = auth()->user();
        
        // Super admin sees everything
        if ($user->role === 'super_admin') {
            return null; // No scoping
        }
        
        // Regular admin sees only their school
        return $user->school_id;
    }
}