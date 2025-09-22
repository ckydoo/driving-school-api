{{-- resources/views/admin/courses/create.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Create Course')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Create New Course</h1>
            <p class="text-muted mb-0">Add a new course to your curriculum</p>
        </div>
        <div>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Courses
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Course Information</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.courses.store') }}" method="POST" id="courseForm">
                        @csrf
                        
                        <!-- Course Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Course Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" 
                                   placeholder="e.g., Basic Driving Course"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Price -->
                            <div class="col-md-4 mb-3">
                                <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" 
                                           id="price" 
                                           name="price" 
                                           class="form-control @error('price') is-invalid @enderror"
                                           value="{{ old('price') }}" 
                                           step="0.01"
                                           min="0"
                                           max="999999.99"
                                           placeholder="0.00"
                                           required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Type -->
                            <div class="col-md-4 mb-3">
                                <label for="type" class="form-label">Course Type</label>
                                <select id="type" name="type" class="form-control @error('type') is-invalid @enderror">
                                    <option value="">Select Type (Optional)</option>
                                    <option value="practical" {{ old('type') === 'practical' ? 'selected' : '' }}>Practical</option>
                                    <option value="theory" {{ old('type') === 'theory' ? 'selected' : '' }}>Theory</option>
                                    <option value="combined" {{ old('type') === 'combined' ? 'selected' : '' }}>Combined</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Duration -->
                            <div class="col-md-6 mb-3">
                                <label for="duration_hours" class="form-label">Duration (Hours)</label>
                                <div class="input-group">
                                    <input type="number" 
                                           id="duration_hours" 
                                           name="duration_hours" 
                                           class="form-control @error('duration_hours') is-invalid @enderror"
                                           value="{{ old('duration_hours') }}" 
                                           min="1"
                                           max="1000"
                                           placeholder="e.g., 40">
                                    <div class="input-group-append">
                                        <span class="input-group-text">hours</span>
                                    </div>
                                    @error('duration_hours')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">Total course duration (optional)</small>
                            </div>

                            <!-- Lessons Included -->
                            <div class="col-md-6 mb-3">
                                <label for="lessons_included" class="form-label">Lessons Included</label>
                                <div class="input-group">
                                    <input type="number" 
                                           id="lessons_included" 
                                           name="lessons_included" 
                                           class="form-control @error('lessons_included') is-invalid @enderror"
                                           value="{{ old('lessons_included') }}" 
                                           min="1"
                                           max="200"
                                           placeholder="e.g., 20">
                                    <div class="input-group-append">
                                        <span class="input-group-text">lessons</span>
                                    </div>
                                    @error('lessons_included')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">Number of lessons included (optional)</small>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" 
                                      name="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="4" 
                                      placeholder="Course description, objectives, and what students will learn...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Describe what this course covers and its objectives</small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Course
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar with Guidelines -->
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Course Guidelines</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Course Types</h6>
                        <ul class="mb-0">
                            <li><strong>Practical:</strong> Hands-on driving lessons</li>
                            <li><strong>Theory:</strong> Classroom/online learning</li>
                            <li><strong>Combined:</strong> Mix of practical and theory</li>
                        </ul>
                    </div>

                    <div class="alert alert-success">
                        <h6><i class="fas fa-lightbulb"></i> Pricing Tips</h6>
                        <ul class="mb-0">
                            <li>Research competitor pricing</li>
                            <li>Consider your cost structure</li>
                            <li>Factor in instructor time and vehicle costs</li>
                            <li>Leave room for promotions</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Best Practices</h6>
                        <ul class="mb-0">
                            <li>Use clear, descriptive course names</li>
                            <li>Include learning objectives</li>
                            <li>Set realistic duration estimates</li>
                            <li>Start with 'Active' status for immediate use</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Course Statistics -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Current Courses</h6>
                </div>
                <div class="card-body">
                    @php
                        $courseStats = [
                            'total' => App\Models\Course::count(),
                            'active' => App\Models\Course::where('status', 'active')->count(),
                            'practical' => App\Models\Course::where('type', 'practical')->count(),
                            'theory' => App\Models\Course::where('type', 'theory')->count(),
                        ];
                    @endphp

                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-right">
                                <div class="h5 font-weight-bold text-primary">{{ $courseStats['total'] }}</div>
                                <div class="small text-muted">Total Courses</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h5 font-weight-bold text-success">{{ $courseStats['active'] }}</div>
                            <div class="small text-muted">Active</div>
                        </div>
                    </div>
                    <div class="row text-center mt-3">
                        <div class="col-6">
                            <div class="border-right">
                                <div class="h5 font-weight-bold text-info">{{ $courseStats['practical'] }}</div>
                                <div class="small text-muted">Practical</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h5 font-weight-bold text-warning">{{ $courseStats['theory'] }}</div>
                            <div class="small text-muted">Theory</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.border-right {
    border-right: 1px solid #e3e6f0;
}

.form-label {
    font-weight: 600;
    color: #5a5c69;
}

.alert ul {
    padding-left: 20px;
}

.alert li {
    margin-bottom: 5px;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('courseForm');
    const priceInput = document.getElementById('price');
    const nameInput = document.getElementById('name');
    const durationInput = document.getElementById('duration_hours');
    const lessonsInput = document.getElementById('lessons_included');

    // Form validation
    form.addEventListener('submit', function(e) {
        const price = parseFloat(priceInput.value);
        const name = nameInput.value.trim();

        if (!name) {
            e.preventDefault();
            alert('Please enter a course name');
            nameInput.focus();
            return false;
        }

        if (!price || price <= 0) {
            e.preventDefault();
            alert('Please enter a valid price greater than 0');
            priceInput.focus();
            return false;
        }

        if (price > 999999.99) {
            e.preventDefault();
            alert('Price cannot exceed $999,999.99');
            priceInput.focus();
            return false;
        }
    });

    // Auto-calculate per lesson price
    function calculatePerLessonPrice() {
        const price = parseFloat(priceInput.value) || 0;
        const lessons = parseInt(lessonsInput.value) || 0;
        
        if (price > 0 && lessons > 0) {
            const perLesson = price / lessons;
            const hint = document.getElementById('perLessonHint');
            if (hint) {
                hint.textContent = `≈ $${perLesson.toFixed(2)} per lesson`;
            }
        }
    }

    // Add per lesson calculation hint
    if (lessonsInput && priceInput) {
        const hint = document.createElement('small');
        hint.id = 'perLessonHint';
        hint.className = 'form-text text-muted';
        lessonsInput.parentNode.appendChild(hint);

        priceInput.addEventListener('input', calculatePerLessonPrice);
        lessonsInput.addEventListener('input', calculatePerLessonPrice);
        
        // Calculate on page load
        calculatePerLessonPrice();
    }

    // Format price input
    priceInput.addEventListener('blur', function() {
        const value = parseFloat(this.value);
        if (!isNaN(value)) {
            this.value = value.toFixed(2);
        }
    });
});
</script>
@endpush