<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Show the main landing page
     */
    public function index()
    {
        // Get some stats for the landing page
        $stats = [
            'total_schools' => School::where('status', 'active')->count(),
            'total_students' => User::where('role', 'student')->where('status', 'active')->count(),
            'total_instructors' => User::where('role', 'instructor')->where('status', 'active')->count(),
            'total_courses' => Course::where('status', 'active')->count(),
        ];

        // Get featured schools (active schools with students)
        $featuredSchools = School::withCount(['students' => function($q) {
                $q->where('status', 'active');
            }])
            ->where('status', 'active')
            ->orderBy('students_count', 'desc')
            ->take(6)
            ->get();

        return view('landing.index', compact('stats', 'featuredSchools'));
    }

    /**
     * Show the features page
     */
    public function features()
    {
        return view('landing.features');
    }

    /**
     * Show the about page
     */
    public function about()
    {
        return view('landing.about');
    }

    /**
     * Show the contact page
     */
    public function contact()
    {
        return view('landing.contact');
    }

    /**
     * Handle contact form submission
     */
    public function submitContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        // Here you can handle the contact form submission
        // For example, send an email, store in database, etc.
        
        return back()->with('success', 'Thank you for your message! We\'ll get back to you soon.');
    }

    /**
     * Show pricing page
     */
    public function pricing()
    {
        // Define pricing plans
        $plans = [
            [
                'name' => 'Starter',
                'price' => 29,
                'period' => 'month',
                'description' => 'Perfect for small driving schools',
                'features' => [
                    'Up to 50 students',
                    'Basic scheduling',
                    'Invoice management',
                    'Email support',
                    'Mobile app access'
                ],
                'highlighted' => false
            ],
            [
                'name' => 'Professional',
                'price' => 79,
                'period' => 'month',
                'description' => 'Best for growing schools',
                'features' => [
                    'Up to 200 students',
                    'Advanced scheduling',
                    'Payment processing',
                    'Fleet management',
                    'Analytics & reports',
                    'Priority support',
                    'Custom branding'
                ],
                'highlighted' => true
            ],
            [
                'name' => 'Enterprise',
                'price' => 199,
                'period' => 'month',
                'description' => 'For large institutions',
                'features' => [
                    'Unlimited students',
                    'Multi-location support',
                    'API access',
                    'Advanced analytics',
                    'Custom integrations',
                    'Dedicated support',
                    'White-label solution'
                ],
                'highlighted' => false
            ]
        ];

        return view('landing.pricing', compact('plans'));
    }
}