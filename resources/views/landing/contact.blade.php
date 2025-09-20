{{-- resources/views/landing/contact.blade.php --}}
@extends('landing.layout')

@section('title', 'Contact Us - DriveSync Pro')
@section('description', 'Get in touch with our team. We\'re here to help you transform your driving school.')

@section('content')
<!-- Hero Section -->
<section class="hero" style="min-height: 60vh;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center text-white" data-aos="fade-up">
                <h1 class="display-3 fw-bold mb-4">Get in Touch</h1>
                <p class="lead">Have questions? We're here to help you transform your driving school.</p>
                <div class="mt-4">
                    <span class="badge bg-light text-primary px-3 py-2 fs-6">
                        <i class="fas fa-clock"></i> Average response time: 2 hours
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Methods -->
<section class="py-3 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-3">
                <div class="d-flex align-items-center justify-content-center">
                    <i class="fas fa-envelope text-primary me-2"></i>
                    <strong>Email:</strong>
                    <a href="mailto:hello@drivesyncpro.com" class="ms-2">hello@drivesyncpro.com</a>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="d-flex align-items-center justify-content-center">
                    <i class="fas fa-phone text-primary me-2"></i>
                    <strong>Phone:</strong>
                    <a href="tel:+1234567890" class="ms-2">+263784666891</a>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="d-flex align-items-center justify-content-center">
                    <i class="fas fa-comments text-primary me-2"></i>
                    <strong>Live Chat:</strong>
                    <span class="ms-2">Available 9am-6pm EST</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Contact Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Contact Form -->
            <div class="col-lg-8 mb-5" data-aos="fade-right">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="bg-primary text-white rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-paper-plane fa-2x"></i>
                            </div>
                            <h3 class="fw-bold">Send us a Message</h3>
                            <p class="text-muted">We'll get back to you within 24 hours</p>
                        </div>
                        
                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif
                        
                        @if($errors->any())
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</h6>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        
                        <form action="{{ route('contact.submit') }}" method="POST" id="contactForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-user text-primary me-1"></i> Full Name *
                                    </label>
                                    <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required
                                           placeholder="Enter your full name">
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope text-primary me-1"></i> Email Address *
                                    </label>
                                    <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required
                                           placeholder="Enter your email address">
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone text-primary me-1"></i> Phone Number
                                    </label>
                                    <input type="tel" class="form-control form-control-lg" 
                                           id="phone" name="phone" value="{{ old('phone') }}"
                                           placeholder="Enter your phone number">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="company" class="form-label">
                                        <i class="fas fa-building text-primary me-1"></i> School/Company Name
                                    </label>
                                    <input type="text" class="form-control form-control-lg" 
                                           id="company" name="company" value="{{ old('company') }}"
                                           placeholder="Enter your school name">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">
                                    <i class="fas fa-tag text-primary me-1"></i> Subject *
                                </label>
                                <select class="form-select form-select-lg @error('subject') is-invalid @enderror" 
                                        id="subject" name="subject" required>
                                    <option value="">Choose a subject...</option>
                                    <option value="General Inquiry" {{ old('subject') === 'General Inquiry' ? 'selected' : '' }}>General Inquiry</option>
                                    <option value="Demo Request" {{ old('subject') === 'Demo Request' ? 'selected' : '' }}>Request a Demo</option>
                                    <option value="Pricing Question" {{ old('subject') === 'Pricing Question' ? 'selected' : '' }}>Pricing Question</option>
                                    <option value="Technical Support" {{ old('subject') === 'Technical Support' ? 'selected' : '' }}>Technical Support</option>
                                    <option value="Feature Request" {{ old('subject') === 'Feature Request' ? 'selected' : '' }}>Feature Request</option>
                                    <option value="Partnership" {{ old('subject') === 'Partnership' ? 'selected' : '' }}>Partnership Opportunity</option>
                                    <option value="Other" {{ old('subject') === 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-4">
                                <label for="message" class="form-label">
                                    <i class="fas fa-comment text-primary me-1"></i> Message *
                                </label>
                                <textarea class="form-control @error('message') is-invalid @enderror" 
                                          id="message" name="message" rows="6" required
                                          placeholder="Tell us how we can help you...">{{ old('message') }}</textarea>
                                @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <span id="charCount">0</span>/1000 characters
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter" value="1" {{ old('newsletter') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="newsletter">
                                        <i class="fas fa-envelope text-primary me-1"></i> Subscribe to our newsletter for product updates and tips
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                    <i class="fas fa-paper-plane"></i> Send Message
                                </button>
                                <small class="text-muted text-center">
                                    <i class="fas fa-shield-alt"></i> Your information is secure and will never be shared
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Contact Info Sidebar -->
            <div class="col-lg-4" data-aos="fade-left">
                <!-- Contact Information Card -->
                <div class="card shadow-lg border-0 mb-4">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div class="bg-success text-white rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-info-circle fa-lg"></i>
                            </div>
                            <h4 class="fw-bold">Contact Information</h4>
                        </div>
                        
                        <div class="contact-info">
                            <div class="d-flex align-items-start mb-4">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 45px; height: 45px;">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold">Email Us</h6>
                                    <a href="mailto:hello@drivesyncpro.com" class="text-decoration-none">hello@drivesyncpro.com</a>
                                    <br>
                                    <a href="mailto:support@drivesyncpro.com" class="text-decoration-none">support@drivesyncpro.com</a>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-start mb-4">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 45px; height: 45px;">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold">Call Us</h6>
                                    <a href="tel:+1234567890" class="text-decoration-none">+263784666891</a>
                                    <br>
                                    <small class="text-muted">Mon-Fri 9am-6pm EST</small>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-start mb-4">
                                <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 45px; height: 45px;">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold">Visit Us</h6>
                                    <address class="mb-0">
                                        Corner C` Avenue & 4th Street<br>
                                        Mutare<br>
                                        Zimbabwe
                                    </address>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-start">
                                <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 45px; height: 45px;">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold">Live Chat</h6>
                                    <span class="text-muted">Available Mon-Fri</span>
                                    <br>
                                    <button class="btn btn-sm btn-outline-warning mt-1" onclick="openLiveChat()">
                                        <i class="fas fa-comment"></i> Start Chat
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Business Hours Card -->
                <div class="card shadow-lg border-0 mb-4">
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <div class="bg-dark text-white rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-clock fa-lg"></i>
                            </div>
                            <h5 class="fw-bold">Business Hours</h5>
                        </div>
                        
                        <div class="business-hours">
                            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                                <span class="fw-semibold">Monday - Friday</span>
                                <span class="text-muted">9:00 AM - 6:00 PM</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                                <span class="fw-semibold">Saturday</span>
                                <span class="text-muted">10:00 AM - 4:00 PM</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                                <span class="fw-semibold">Sunday</span>
                                <span class="text-muted">Closed</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold">Holidays</span>
                                <span class="text-muted">Limited Hours</span>
                            </div>
                        </div>
                        
                        <div class="mt-3 p-3 bg-light rounded">
                            <div class="d-flex align-items-center">
                                <div class="bg-success rounded-circle me-2" style="width: 8px; height: 8px;"></div>
                                <small class="text-success fw-bold">Currently Open</small>
                            </div>
                            <small class="text-muted">We typically respond within 2 hours</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Alternative Contact Methods -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="display-5 fw-bold mb-3">Other Ways to Reach Us</h2>
                <p class="lead text-muted">Choose the method that works best for you</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="bg-primary text-white rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-calendar-alt fa-2x"></i>
                        </div>
                        <h5 class="fw-bold">Schedule a Demo</h5>
                        <p class="text-muted mb-3">Book a personalized demo to see DriveSync Pro in action</p>
                        <a href="{{ route('login') }}" class="btn btn-outline-primary">
                            <i class="fas fa-video"></i> Book Demo
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="bg-success text-white rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-question-circle fa-2x"></i>
                        </div>
                        <h5 class="fw-bold">Help Center</h5>
                        <p class="text-muted mb-3">Find answers to common questions in our knowledge base</p>
                        <a href="#" class="btn btn-outline-success">
                            <i class="fas fa-book"></i> Browse FAQ
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="bg-warning text-white rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-headset fa-2x"></i>
                        </div>
                        <h5 class="fw-bold">Priority Support</h5>
                        <p class="text-muted mb-3">Get faster support with our priority customer service</p>
                        <a href="{{ route('pricing') }}" class="btn btn-outline-warning">
                            <i class="fas fa-crown"></i> Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section (Optional) -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12" data-aos="fade-up">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h4 class="mb-0">
                            <i class="fas fa-map-marker-alt"></i> Find Our Office
                        </h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="bg-light p-4 text-center">
                            <p class="mb-3 text-muted">We're located in the heart of the business district</p>
                            <div class="bg-secondary rounded" style="height: 300px; display: flex; align-items: center; justify-content: center;">
                                <div class="text-white text-center">
                                    <i class="fas fa-map fa-3x mb-3"></i>
                                    <p class="mb-0">Interactive Map</p>
                                    <small>123 Business Avenue, Suite 100<br>New York, NY 10001</small>
                                </div>
                            </div>
                            <!-- You can replace this with an actual Google Maps embed -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counter for message textarea
    const messageTextarea = document.getElementById('message');
    const charCount = document.getElementById('charCount');
    const maxLength = 1000;
    
    function updateCharCount() {
        const currentLength = messageTextarea.value.length;
        charCount.textContent = currentLength;
        
        if (currentLength > maxLength * 0.9) {
            charCount.style.color = '#dc3545'; // Red when approaching limit
        } else if (currentLength > maxLength * 0.7) {
            charCount.style.color = '#fd7e14'; // Orange when getting close
        } else {
            charCount.style.color = '#6c757d'; // Gray normal state
        }
    }
    
    messageTextarea.addEventListener('input', updateCharCount);
    updateCharCount(); // Initial count
    
    // Form submission with loading state
    const contactForm = document.getElementById('contactForm');
    const submitBtn = document.getElementById('submitBtn');
    const originalBtnText = submitBtn.innerHTML;
    
    contactForm.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        
        // Re-enable button after 5 seconds as fallback
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }, 5000);
    });
    
    // Form validation
    const requiredFields = contactForm.querySelectorAll('[required]');
    
    function validateForm() {
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        // Email validation
        const emailField = document.getElementById('email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (emailField.value && !emailRegex.test(emailField.value)) {
            isValid = false;
            emailField.classList.add('is-invalid');
        }
        
        submitBtn.disabled = !isValid;
    }
    
    // Real-time validation
    requiredFields.forEach(field => {
        field.addEventListener('input', validateForm);
        field.addEventListener('blur', validateForm);
    });
    
    // Initial validation
    validateForm();
});

// Live chat function (placeholder)
function openLiveChat() {
    // You can integrate this with your actual live chat service
    alert('Live chat would open here. Integrate with your preferred live chat service like Intercom, Zendesk, or Tawk.to');
}

// Auto-fill form if coming from pricing page
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('subject')) {
    document.getElementById('subject').value = urlParams.get('subject');
}
</script>
@endpush