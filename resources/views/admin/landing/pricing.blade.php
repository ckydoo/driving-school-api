{{-- resources/views/landing/pricing.blade.php --}}
@extends('landing.layout')

@section('title', 'Pricing - DriveMaster')
@section('description', 'Choose the perfect plan for your driving school. Affordable pricing with no hidden fees.')

@section('content')
<!-- Hero Section -->
<section class="hero" style="min-height: 60vh;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center text-white" data-aos="fade-up">
                <h1 class="display-3 fw-bold mb-4">Simple, Transparent Pricing</h1>
                <p class="lead">Choose the perfect plan for your driving school. No hidden fees, no surprises.</p>
                <div class="mt-4">
                    <span class="badge bg-light text-primary px-3 py-2 fs-6">
                        <i class="fas fa-gift"></i> 30-Day Free Trial Available
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Toggle -->
<section class="py-3 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mx-auto text-center">
                <div class="btn-group" role="group" aria-label="Pricing toggle">
                    <input type="radio" class="btn-check" name="pricingToggle" id="monthly" checked>
                    <label class="btn btn-outline-primary" for="monthly">Monthly</label>
                    
                    <input type="radio" class="btn-check" name="pricingToggle" id="yearly">
                    <label class="btn btn-outline-primary" for="yearly">
                        Yearly <span class="badge bg-success ms-1">Save 20%</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Plans -->
<section class="py-5">
    <div class="container">
        <div class="row">
            @foreach($plans as $plan)
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div class="card h-100 {{ $plan['highlighted'] ? 'border-primary shadow-lg' : '' }} position-relative">
                    @if($plan['highlighted'])
                    <div class="position-absolute top-0 start-50 translate-middle">
                        <span class="badge bg-primary px-4 py-2 rounded-pill">
                            <i class="fas fa-star"></i> Most Popular
                        </span>
                    </div>
                    @endif
                    
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <div class="bg-{{ $plan['highlighted'] ? 'primary' : 'light' }} rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" 
                                 style="width: 80px; height: 80px;">
                                @if($plan['name'] === 'Starter')
                                    <i class="fas fa-rocket fa-2x {{ $plan['highlighted'] ? 'text-white' : 'text-primary' }}"></i>
                                @elseif($plan['name'] === 'Professional')
                                    <i class="fas fa-crown fa-2x {{ $plan['highlighted'] ? 'text-white' : 'text-primary' }}"></i>
                                @else
                                    <i class="fas fa-building fa-2x {{ $plan['highlighted'] ? 'text-white' : 'text-primary' }}"></i>
                                @endif
                            </div>
                            <h3 class="fw-bold mb-3">{{ $plan['name'] }}</h3>
                        </div>
                        
                        <div class="mb-4">
                            <div class="pricing-display">
                                <span class="display-4 fw-bold monthly-price">${{ $plan['price'] }}</span>
                                <span class="display-4 fw-bold yearly-price d-none">${{ floor($plan['price'] * 0.8) }}</span>
                                <div>
                                    <span class="text-muted monthly-text">per month</span>
                                    <span class="text-muted yearly-text d-none">per month, billed yearly</span>
                                </div>
                            </div>
                            <div class="yearly-savings d-none mt-2">
                                <small class="text-success fw-bold">
                                    <i class="fas fa-piggy-bank"></i> Save ${{ floor($plan['price'] * 2.4) }}/year
                                </small>
                            </div>
                        </div>
                        
                        <p class="text-muted mb-4">{{ $plan['description'] }}</p>
                        
                        <ul class="list-unstyled mb-4 text-start">
                            @foreach($plan['features'] as $feature)
                            <li class="mb-3">
                                <i class="fas fa-check text-success me-3"></i> 
                                <span>{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>
                        
                        <div class="mt-auto">
                            <a href="{{ route('login') }}" class="btn {{ $plan['highlighted'] ? 'btn-primary' : 'btn-outline-primary' }} btn-lg w-100 mb-3">
                                <i class="fas fa-rocket"></i> Start Free Trial
                            </a>
                            <small class="text-muted">No credit card required</small>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Plan Comparison -->
        <div class="row mt-5">
            <div class="col-12" data-aos="fade-up">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0"><i class="fas fa-table"></i> Feature Comparison</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40%;">Features</th>
                                        <th class="text-center">Starter</th>
                                        <th class="text-center">Professional</th>
                                        <th class="text-center">Enterprise</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Students</strong></td>
                                        <td class="text-center">Up to 50</td>
                                        <td class="text-center">Up to 200</td>
                                        <td class="text-center">Unlimited</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Instructors</strong></td>
                                        <td class="text-center">Up to 5</td>
                                        <td class="text-center">Up to 20</td>
                                        <td class="text-center">Unlimited</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Vehicles</strong></td>
                                        <td class="text-center">Up to 10</td>
                                        <td class="text-center">Up to 50</td>
                                        <td class="text-center">Unlimited</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Scheduling</strong></td>
                                        <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                        <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                        <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Basic Reports</strong></td>
                                        <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                        <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                        <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Payment Processing</strong></td>
                                        <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                        <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                        <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Advanced Analytics</strong></td>
                                        <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                        <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                        <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    </tr>
                                    <tr>
                                        <td><strong>API Access</strong></td>
                                        <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                        <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                        <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    </tr>
                                    <tr>
                                        <td><strong>White-label</strong></td>
                                        <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                        <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                        <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Support</strong></td>
                                        <td class="text-center">Email</td>
                                        <td class="text-center">Priority</td>
                                        <td class="text-center">Dedicated</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Included Features -->
        <div class="row mt-5">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h3 class="fw-bold mb-4">All Plans Include</h3>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-check text-success me-2"></i> 
                            <span>30-day free trial</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-check text-success me-2"></i> 
                            <span>Cancel anytime</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-check text-success me-2"></i> 
                            <span>SSL security</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-check text-success me-2"></i> 
                            <span>Regular backups</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-check text-success me-2"></i> 
                            <span>Mobile access</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-check text-success me-2"></i> 
                            <span>24/7 monitoring</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Frequently Asked Questions</h2>
                <p class="lead text-muted">Everything you need to know about our pricing.</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="accordion" id="pricingFAQ">
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h3 class="accordion-header">
                            <button class="accordion-button rounded" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                <i class="fas fa-question-circle text-primary me-2"></i>
                                Can I change plans later?
                            </button>
                        </h3>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#pricingFAQ">
                            <div class="accordion-body">
                                Yes, you can upgrade or downgrade your plan at any time. Changes take effect immediately and billing is prorated. There are no penalties for changing plans.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed rounded" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                <i class="fas fa-credit-card text-primary me-2"></i>
                                Is there a setup fee?
                            </button>
                        </h3>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#pricingFAQ">
                            <div class="accordion-body">
                                No setup fees! You only pay the monthly or yearly subscription. We'll help you get started for free, including data migration and training.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed rounded" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                <i class="fas fa-money-bill-wave text-primary me-2"></i>
                                What payment methods do you accept?
                            </button>
                        </h3>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#pricingFAQ">
                            <div class="accordion-body">
                                We accept all major credit cards (Visa, MasterCard, American Express), PayPal, and bank transfers. All payments are processed securely with 256-bit SSL encryption.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed rounded" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                <i class="fas fa-undo text-primary me-2"></i>
                                Do you offer refunds?
                            </button>
                        </h3>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#pricingFAQ">
                            <div class="accordion-body">
                                Yes, we offer a 30-day money-back guarantee. If you're not satisfied within the first 30 days, we'll refund your payment in full, no questions asked.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed rounded" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                <i class="fas fa-users text-primary me-2"></i>
                                What if I exceed my plan limits?
                            </button>
                        </h3>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#pricingFAQ">
                            <div class="accordion-body">
                                We'll notify you when you're approaching your limits. You can upgrade your plan anytime or contact us for a custom solution. We never cut off access unexpectedly.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed rounded" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                <i class="fas fa-headset text-primary me-2"></i>
                                What kind of support do you provide?
                            </button>
                        </h3>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#pricingFAQ">
                            <div class="accordion-body">
                                All plans include comprehensive support. Starter gets email support, Professional gets priority support with faster response times, and Enterprise gets dedicated account management.
                            </div>
                        </div>
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
                <h2 class="display-4 fw-bold mb-3">Ready to Get Started?</h2>
                <p class="lead mb-4">Join thousands of driving schools already using DriveMaster. Start your free trial today!</p>
                <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                    <a href="{{ route('login') }}" class="btn btn-light btn-lg">
                        <i class="fas fa-rocket"></i> Start Free Trial
                    </a>
                    <a href="{{ route('contact') }}" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-phone"></i> Contact Sales
                    </a>
                </div>
                <div class="mt-3">
                    <small class="opacity-75">
                        <i class="fas fa-shield-alt"></i> No credit card required • Cancel anytime
                    </small>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
// Pricing toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const monthlyRadio = document.getElementById('monthly');
    const yearlyRadio = document.getElementById('yearly');
    
    function togglePricing() {
        const isYearly = yearlyRadio.checked;
        
        document.querySelectorAll('.monthly-price').forEach(el => {
            el.classList.toggle('d-none', isYearly);
        });
        
        document.querySelectorAll('.yearly-price').forEach(el => {
            el.classList.toggle('d-none', !isYearly);
        });
        
        document.querySelectorAll('.monthly-text').forEach(el => {
            el.classList.toggle('d-none', isYearly);
        });
        
        document.querySelectorAll('.yearly-text').forEach(el => {
            el.classList.toggle('d-none', !isYearly);
        });
        
        document.querySelectorAll('.yearly-savings').forEach(el => {
            el.classList.toggle('d-none', !isYearly);
        });
    }
    
    monthlyRadio.addEventListener('change', togglePricing);
    yearlyRadio.addEventListener('change', togglePricing);
});
</script>
@endpush