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
                    <div class="d-flex flex-column flex-md-row gap-3">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-rocket"></i> Get Started Free
                        </a>
                        <a href="#features" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-play"></i> Watch Demo
                        </a>
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
                    <span class="stats-label">Active Schools</span>
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
                    <span class="stats-label">Active Courses</span>
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
                <p class="lead text-muted">Our comprehensive platform provides all the tools you need to manage and grow your driving school efficiently.</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Student Management</h4>
                        <p class="text-muted">Complete student profiles, progress tracking, and communication tools to keep everyone connected.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h4>Smart Scheduling</h4>
                        <p class="text-muted">Intelligent scheduling system that optimizes instructor time and prevents conflicts.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h4>Billing & Payments</h4>
                        <p class="text-muted">Automated invoicing, payment tracking, and integrated payment processing.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <h4>Fleet Management</h4>
                        <p class="text-muted">Track vehicles, maintenance schedules, and assign cars to instructors efficiently.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="500">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h4>Analytics & Reports</h4>
                        <p class="text-muted">Comprehensive reporting and analytics to make data-driven business decisions.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4>Mobile Access</h4>
                        <p class="text-muted">Access your dashboard anywhere with our responsive design and mobile app.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Schools Section -->
@if($featuredSchools->count() > 0)
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Trusted by Schools Worldwide</h2>
                <p class="lead text-muted">Join thousands of driving schools already using DriveSync Pro to transform their operations.</p>
            </div>
        </div>
        
        <div class="row">
            @foreach($featuredSchools as $school)
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-school"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-0">{{ $school->name }}</h5>
                                <small class="text-muted">{{ $school->city }}, {{ $school->country }}</small>
                            </div>
                        </div>
                        <p class="card-text text-muted">
                            <i class="fas fa-users text-primary"></i> {{ $school->students_count }} Active Students
                        </p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- CTA Section -->
<section class="py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center text-white" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Ready to Get Started?</h2>
                <p class="lead mb-4">Join thousands of driving schools already using DriveSync Pro. Start your free trial today!</p>
                <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                    <a href="{{ route('login') }}" class="btn btn-light btn-lg">
                        <i class="fas fa-rocket"></i> Start Free Trial
                    </a>
                    <a href="{{ route('contact') }}" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-phone"></i> Contact Sales
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection