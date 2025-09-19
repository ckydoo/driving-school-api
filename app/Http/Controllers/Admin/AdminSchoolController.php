<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminSchoolController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        
        // Only super admins can view all schools
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super administrator privileges required.');
        }

        $query = School::withCount(['users', 'students' => function($q) {
            $q->where('role', 'student');
        }, 'instructors' => function($q) {
            $q->where('role', 'instructor');
        }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schools = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.schools.index', compact('schools'));
    }

    public function show(School $school)
    {
        $currentUser = Auth::user();
        
        // Check permissions
        if (!$this->canAccessSchool($currentUser, $school)) {
            abort(403, 'Access denied.');
        }

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

    // === HELPER METHODS ===

    private function canAccessSchool($currentUser, $school): bool
    {
        // Super admins can access all schools
        if ($currentUser->isSuperAdmin()) {
            return true;
        }

        // School admins can only access their own school
        if ($currentUser->isSchoolAdmin()) {
            return $school->id === $currentUser->school_id;
        }

        return false;
    }
}