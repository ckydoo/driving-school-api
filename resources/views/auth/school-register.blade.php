{{-- resources/views/auth/school-register.blade.php --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Register Your Driving School - {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .registration-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo i {
            font-size: 50px;
            color: #667eea;
            margin-bottom: 15px;
        }

        .logo h1 {
            color: #2d3748;
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 5px;
        }

        .logo p {
            color: #6b7280;
            font-size: 16px;
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-floating > .form-control {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-floating > .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
            background: white;
        }

        .form-floating > label {
            color: #6b7280;
            font-weight: 500;
        }

        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }

        .btn-register:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .trial-info {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
        }

        .trial-info i {
            font-size: 20px;
            margin-right: 10px;
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e5e7eb;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #5a6fd8;
            text-decoration: underline;
        }

        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
        }

        .invalid-feedback {
            display: block;
            margin-top: 5px;
            font-size: 14px;
            color: #dc2626;
        }

        .form-control.is-invalid {
            border-color: #dc2626;
        }

        .form-control.is-invalid:focus {
            border-color: #dc2626;
            box-shadow: 0 0 0 0.25rem rgba(220, 38, 38, 0.25);
        }

        @media (max-width: 576px) {
            .registration-container {
                padding: 30px 25px;
                margin: 10px;
            }

            .logo h1 {
                font-size: 24px;
            }

            .logo i {
                font-size: 40px;
            }
        }
    </style>
</head>

<body>
    <div class="registration-container">
        <!-- Logo Section -->
        <div class="logo">
            <i class="fas fa-car"></i>
            <h1>Register Your School</h1>
            <p>Start managing your driving school today</p>
        </div>

        <!-- Trial Info -->
        <div class="trial-info">
            <i class="fas fa-gift"></i>
            <strong>30-Day Free Trial</strong> - No credit card required!
        </div>

        <!-- Error Messages -->
        @if ($errors->any() || session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                @if(session('error'))
                    {{ session('error') }}
                @else
                    Please fix the errors below and try again.
                @endif
            </div>
        @endif

        <!-- Registration Form -->
        <form method="POST" action="{{ route('school.register') }}" id="registrationForm">
            @csrf

            <!-- School Name -->
            <div class="form-floating">
                <input
                    type="text"
                    class="form-control @error('school_name') is-invalid @enderror"
                    id="school_name"
                    name="school_name"
                    value="{{ old('school_name') }}"
                    required
                    autocomplete="organization"
                    placeholder="Enter your driving school name"
                >
                <label for="school_name">
                    <i class="fas fa-school me-2"></i>School Name
                </label>
                @error('school_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- School Address -->
            <div class="form-floating">
                <textarea
                    class="form-control @error('school_address') is-invalid @enderror"
                    id="school_address"
                    name="school_address"
                    style="height: 100px"
                    required
                    placeholder="Enter your school address"
                >{{ old('school_address') }}</textarea>
                <label for="school_address">
                    <i class="fas fa-map-marker-alt me-2"></i>School Address
                </label>
                @error('school_address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Admin Email -->
            <div class="form-floating">
                <input
                    type="email"
                    class="form-control @error('admin_email') is-invalid @enderror"
                    id="admin_email"
                    name="admin_email"
                    value="{{ old('admin_email') }}"
                    required
                    autocomplete="email"
                    placeholder="Enter admin email address"
                >
                <label for="admin_email">
                    <i class="fas fa-envelope me-2"></i>Admin Email
                </label>
                @error('admin_email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Admin Password -->
            <div class="form-floating">
                <input
                    type="password"
                    class="form-control @error('admin_password') is-invalid @enderror"
                    id="admin_password"
                    name="admin_password"
                    required
                    autocomplete="new-password"
                    placeholder="Create a secure password"
                    minlength="8"
                >
                <label for="admin_password">
                    <i class="fas fa-lock me-2"></i>Password (min. 8 characters)
                </label>
                @error('admin_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="form-floating">
                <input
                    type="password"
                    class="form-control @error('admin_password_confirmation') is-invalid @enderror"
                    id="admin_password_confirmation"
                    name="admin_password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Confirm your password"
                    minlength="8"
                >
                <label for="admin_password_confirmation">
                    <i class="fas fa-lock me-2"></i>Confirm Password
                </label>
                @error('admin_password_confirmation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-register" id="submitBtn">
                <i class="fas fa-rocket me-2"></i>
                <span class="btn-text">Start Free Trial</span>
                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
            </button>
        </form>

        <!-- Login Link -->
        <div class="login-link">
            Already have an account?
            <a href="{{ route('login') }}">Sign in here</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Form submission handling
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const btnText = submitBtn.querySelector('.btn-text');
            const spinner = submitBtn.querySelector('.spinner-border');

            // Disable button and show loading
            submitBtn.disabled = true;
            btnText.textContent = 'Creating Account...';
            spinner.classList.remove('d-none');
        });

        // Password confirmation validation
        const password = document.getElementById('admin_password');
        const confirmPassword = document.getElementById('admin_password_confirmation');

        function validatePassword() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity("Passwords don't match");
                confirmPassword.classList.add('is-invalid');
            } else {
                confirmPassword.setCustomValidity('');
                confirmPassword.classList.remove('is-invalid');
            }
        }

        password.addEventListener('change', validatePassword);
        confirmPassword.addEventListener('keyup', validatePassword);
    </script>
</body>
</html>
