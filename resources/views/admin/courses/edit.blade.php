{{-- resources/views/admin/courses/edit.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Edit Course - ' . $course->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit"></i> Edit Course
        </h1>
        <div>
            <a href="{{ route('admin.courses.show', $course) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Course
            </a>
        </div>
    </div>

    <!-- Edit Course Form -->
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-book"></i> Course Information
                    </h6>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.courses.update', $course) }}">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name" class="font-weight-bold">Course Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name', $course->name) }}"
                                           required
                                           maxlength="255"
                                           placeholder="e.g., Basic Driving Course">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status" class="font-weight-bold">Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror"
                                            id="status"
                                            name="status"
                                            required>
                                        <option value="">Select Status...</option>
                                        <option value="active" {{ old('status', $course->status) === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $course->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Course Details -->
                        <div class="row">

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="price" class="font-weight-bold">Price ($) <span class="text-danger">*</span></label>
                                    <input type="number"
                                           class="form-control @error('price') is-invalid @enderror"
                                           id="price"
                                           name="price"
                                           value="{{ old('price', $course->price) }}"
                                           required
                                           min="0"
                                           max="999999.99"
                                           step="0.01"
                                           placeholder="0.00">
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <br>
                        <!-- Submit Buttons -->
                        <div class="form-group row mb-0">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fas fa-save"></i> Update Course
                                </button>
                                <a href="{{ route('admin.courses.show', $course) }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-calculate total based on lessons and price
    const priceInput = document.getElementById('price');
    const lessonsInput = document.getElementById('lessons');

    function updateCalculations() {
        const price = parseFloat(priceInput.value) || 0;
        const lessons = parseInt(lessonsInput.value) || 0;
        const total = price * lessons;

        // You can add a total display here if needed
        console.log('Course total would be:  + total.toFixed(2));
    }

    priceInput.addEventListener('input', updateCalculations);
    lessonsInput.addEventListener('input', updateCalculations);
});
</script>
@endpush
