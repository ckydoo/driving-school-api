@extends('landing.layout')

@section('title', 'DriveSync Pro - Professional Driving School Management System')
@section('description', 'Transform your driving school with our comprehensive management platform. Student tracking, scheduling, billing, and more.')

@section('content')
<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="hero-content">
                    <h1>Transform Your Driving School</h1>
                    <p class="lead">Streamline operations, track student progress, and grow your business with our comprehensive driving school management platform.</p>

                    {{-- Updated CTA buttons with registration link --}}
                    <div class="d-flex flex-column flex-md-row gap-3">
                        <a href="{{ route('school.register.form') }}" class="btn btn-primary btn-lg">
                            Start Free Trial
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg">
                             Sign In
                        </a>
                    </div>

                    {{-- Added trial benefits --}}
                    <div class="mt-4">
                        <div class="d-flex flex-wrap align-items-center text-white-50">
                            <span class="me-3 mb-2">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                30-day free trial
                            </span>
                            <span class="me-3 mb-2">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                No credit card required
                            </span>
                            <span class="me-3 mb-2">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                Setup in 2 minutes
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="text-center">
                    <div class="position-relative">
                        <i class="fas fa-car fa-10x text-white opacity-75"></i>
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <i class="fas fa-graduation-cap fa-5x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="stats-card">
                    <span class="stats-number">{{ number_format($stats['total_schools']) }}+</span>
                    <span class="stats-label">Schools</span>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="stats-card">
                    <span class="stats-number">{{ number_format($stats['total_students']) }}+</span>
                    <span class="stats-label">Students</span>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="stats-card">
                    <span class="stats-number">{{ number_format($stats['total_instructors']) }}+</span>
                    <span class="stats-label">Instructors</span>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="stats-card">
                    <span class="stats-number">{{ number_format($stats['total_courses']) }}+</span>
                    <span class="stats-label">Courses</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Everything You Need to Succeed</h2>
                <p class="lead text-muted">Comprehensive tools designed specifically for driving school management.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5 class="card-title">Student Management</h5>
                        <p class="card-text">Track student progress, manage enrollments, and monitor lesson completion rates with detailed profiles and communication tools.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h5 class="card-title">Smart Scheduling</h5>
                        <p class="card-text">Easy lesson scheduling with instructor availability, vehicle assignment, and automated reminders for students and instructors.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-car-side"></i>
                        </div>
                        <h5 class="card-title">Fleet Management</h5>
                        <p class="card-text">Track your vehicles, maintenance schedules, assignments to instructors, and ensure optimal utilization of your fleet.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h5 class="card-title">Payment Tracking</h5>
                        <p class="card-text">Invoice generation, payment tracking, financial reporting, and automated reminders for outstanding balances.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="500">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h5 class="card-title">Analytics & Reports</h5>
                        <p class="card-text">Detailed insights into your school's performance, revenue trends, student progress, and instructor efficiency metrics.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h5 class="card-title">Mobile Ready</h5>
                        <p class="card-text">Access your school data anywhere with our fully responsive design that works perfectly on all devices and screen sizes.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Schools Section (if any schools exist) -->
@if($featuredSchools->count() > 0)
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center" data-aos="fade-up">
                <h2 class="display-5 fw-bold mb-3">Trusted by Leading Driving Schools</h2>
                <p class="lead text-muted">Join successful driving schools already using our platform.</p>
            </div>
        </div>

        <div class="row">
            @foreach($featuredSchools as $school)
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="fas fa-school fa-2x"></i>
                            </div>
                        </div>
                        <h5 class="card-title">{{ $school->name }}</h5>
                        <p class="card-text text-muted">{{ $school->city }}, {{ $school->country }}</p>
                        <div class="d-flex justify-content-center">
                            <small class="text-muted">
                                <i class="fas fa-users me-1"></i>
                                {{ $school->students_count }} {{ Str::plural('student', $school->students_count) }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Call to Action Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center" data-aos="fade-up">
                <h2 class="display-5 fw-bold mb-3">Ready to Transform Your Driving School?</h2>
                <p class="lead mb-4">Join hundreds of driving schools already using our platform to streamline their operations and grow their business.</p>

                {{-- Free trial highlight --}}
                <div class="mb-4">
                    <div class="badge bg-light text-primary fs-6 px-4 py-2 mb-3">
                        <i class="fas fa-gift me-2"></i>
                        <strong>30-Day Free Trial</strong> - No Credit Card Required
                    </div>
                </div>

                {{-- Main CTA buttons --}}
                <div class="d-flex flex-column flex-md-row gap-3 justify-content-center mb-4">
                    <a href="{{ route('school.register.form') }}" class="btn btn-light btn-lg text-primary">

                        <strong>Register Your School</strong>
                    </a>
                    <a href="{{ route('features') }}" class="btn btn-outline-light btn-lg">

                        View All Features
                    </a>
                </div>

                {{-- Trust indicators --}}
                <div class="row text-center mt-5">
                    <div class="col-md-4">
                        <i class="fas fa-shield-alt fa-2x mb-2 text-light"></i>
                        <p class="mb-0"><small>Bank-level Security</small></p>
                    </div>
                    <div class="col-md-4">
                        <i class="fas fa-headset fa-2x mb-2 text-light"></i>
                        <p class="mb-0"><small>24/7 Support</small></p>
                    </div>
                    <div class="col-md-4">
                        <i class="fas fa-clock fa-2x mb-2 text-light"></i>
                        <p class="mb-0"><small>2-minute Setup</small></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section (optional - you can add this later) -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center" data-aos="fade-up">
                <h2 class="display-5 fw-bold mb-3">What Our Customers Say</h2>
                <p class="lead text-muted">Don't just take our word for it - see what driving school owners are saying.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-warning mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="card-text">"DriveSync Pro has completely transformed how we manage our driving school. The scheduling system alone has saved us hours each week!"</p>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Sarah Johnson</h6>
                                <small class="text-muted">Elite Driving School</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-warning mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="card-text">"The payment tracking and invoicing features are fantastic. We've reduced outstanding payments by 60% since implementing this system."</p>
                        <div class="d-flex align-items-center">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Michael Chen</h6>
                                <small class="text-muted">Metro Driving Academy</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-warning mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="card-text">"Easy to use, great support team, and the reporting features help us make better business decisions. Highly recommended!"</p>
                        <div class="d-flex align-items-center">
                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Emma Rodriguez</h6>
                                <small class="text-muted">Fast Track Driving</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
