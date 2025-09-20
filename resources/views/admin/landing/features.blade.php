@extends('landing.layout')

@section('title', 'Features - DriveMaster')
@section('description', 'Discover all the powerful features that make DriveMaster the best choice for driving school management.')

@section('content')
<!-- Hero Section -->
<section class="hero" style="min-height: 60vh;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center text-white" data-aos="fade-up">
                <h1 class="display-3 fw-bold mb-4">Powerful Features for Modern Driving Schools</h1>
                <p class="lead">Everything you need to run your driving school efficiently and grow your business.</p>
            </div>
        </div>
    </div>
</section>

<!-- Core Features -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Complete Management Solution</h2>
                <p class="lead text-muted">From student enrollment to graduation, manage every aspect of your driving school.</p>
            </div>
        </div>

        <!-- Student Management -->
        <div class="row align-items-center mb-5">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="pe-lg-4">
                    <div class="feature-icon bg-primary text-white mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="fw-bold mb-3">Advanced Student Management</h3>
                    <p class="text-muted mb-4">Complete student profiles with progress tracking, lesson history, and communication tools. Monitor each student's journey from enrollment to license acquisition.</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Detailed student profiles</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Progress tracking & milestones</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Document management</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Communication logs</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Performance analytics</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="text-center">
                    <div class="bg-light rounded-3 p-5">
                        <i class="fas fa-user-graduate fa-8x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scheduling -->
        <div class="row align-items-center mb-5">
            <div class="col-lg-6 order-lg-2" data-aos="fade-left">
                <div class="ps-lg-4">
                    <div class="feature-icon bg-warning text-white mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3 class="fw-bold mb-3">Intelligent Scheduling System</h3>
                    <p class="text-muted mb-4">Smart scheduling that prevents conflicts, optimizes instructor time, and keeps everyone organized. Calendar integration and automated reminders included.</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Conflict prevention</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Instructor optimization</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Automated reminders</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Calendar integration</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Recurring lessons</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6 order-lg-1" data-aos="fade-right">
                <div class="text-center">
                    <div class="bg-light rounded-3 p-5">
                        <i class="fas fa-calendar-check fa-8x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Billing -->
        <div class="row align-items-center mb-5">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="pe-lg-4">
                    <div class="feature-icon bg-success text-white mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3 class="fw-bold mb-3">Automated Billing & Payments</h3>
                    <p class="text-muted mb-4">Streamlined billing process with automated invoicing, payment tracking, and integrated payment processing. Accept online payments and reduce administrative overhead.</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Automated invoicing</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Online payment processing</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Payment tracking</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Overdue notifications</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Financial reporting</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="text-center">
                    <div class="bg-light rounded-3 p-5">
                        <i class="fas fa-money-bill-wave fa-8x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Additional Features Grid -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">More Powerful Features</h2>
                <p class="lead text-muted">Additional tools and capabilities to enhance your driving school operations.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-info text-white mb-3 mx-auto">
                            <i class="fas fa-car"></i>
                        </div>
                        <h5 class="fw-bold">Fleet Management</h5>
                        <p class="text-muted">Track vehicles, maintenance schedules, and assign cars to instructors efficiently.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-danger text-white mb-3 mx-auto">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h5 class="fw-bold">Analytics & Reports</h5>
                        <p class="text-muted">Comprehensive reporting and analytics to make data-driven business decisions.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-purple text-white mb-3 mx-auto" style="background: #6f42c1 !important;">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h5 class="fw-bold">Mobile Access</h5>
                        <p class="text-muted">Access your dashboard anywhere with responsive design and mobile optimization.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-dark text-white mb-3 mx-auto">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5 class="fw-bold">Data Security</h5>
                        <p class="text-muted">Enterprise-grade security with encrypted data storage and regular backups.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="500">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-secondary text-white mb-3 mx-auto">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h5 class="fw-bold">Customization</h5>
                        <p class="text-muted">Customize the platform to match your school's branding and workflow needs.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-primary text-white mb-3 mx-auto">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h5 class="fw-bold">24/7 Support</h5>
                        <p class="text-muted">Round-the-clock customer support to help you when you need it most.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center text-white" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Ready to Experience These Features?</h2>
                <p class="lead mb-4">Start your free trial today and see how DriveMaster can transform your driving school.</p>
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