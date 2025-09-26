{{-- resources/views/admin/subscription-packages/edit.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Edit Subscription Package')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit"></i> Edit Package: {{ $package->name }}
        </h1>
        <div>
            <a href="{{ route('admin.subscription-packages.show', $package) }}" class="btn btn-info btn-sm">
                <i class="fas fa-eye"></i> View
            </a>
            <a href="{{ route('admin.subscription-packages.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Packages
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Edit Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Package Details</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.subscription-packages.update', $package) }}">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- Package Name -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name" class="font-weight-bold">Package Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $package->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Trial Days -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="trial_days" class="font-weight-bold">Trial Days <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('trial_days') is-invalid @enderror" 
                                   id="trial_days" 
                                   name="trial_days" 
                                   value="{{ old('trial_days', $package->trial_days) }}" 
                                   min="0" 
                                   required>
                            @error('trial_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="description" class="font-weight-bold">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" 
                              name="description" 
                              rows="3">{{ old('description', $package->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <!-- Monthly Price -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="monthly_price" class="font-weight-bold">Monthly Price ($) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('monthly_price') is-invalid @enderror" 
                                   id="monthly_price" 
                                   name="monthly_price" 
                                   value="{{ old('monthly_price', $package->monthly_price) }}" 
                                   step="0.01" 
                                   min="0" 
                                   required>
                            @error('monthly_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Yearly Price -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="yearly_price" class="font-weight-bold">Yearly Price ($)</label>
                            <input type="number" 
                                   class="form-control @error('yearly_price') is-invalid @enderror" 
                                   id="yearly_price" 
                                   name="yearly_price" 
                                   value="{{ old('yearly_price', $package->yearly_price) }}" 
                                   step="0.01" 
                                   min="0">
                            <small class="form-text text-muted">
                                @if($package->yearly_price && $package->monthly_price > 0)
                                    Current discount: {{ $package->getYearlyDiscount() }}%
                                @endif
                            </small>
                            @error('yearly_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Features Section -->
                <div class="form-group">
                    <label class="font-weight-bold">Features <span class="text-danger">*</span></label>
                    <div id="features-container">
                        @if(old('features') || $package->features)
                            @foreach(old('features', $package->features) as $index => $feature)
                                <div class="input-group mb-2 feature-input">
                                    <input type="text" 
                                           class="form-control" 
                                           name="features[]" 
                                           value="{{ $feature }}" 
                                           placeholder="Enter feature" 
                                           required>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-danger remove-feature">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="input-group mb-2 feature-input">
                                <input type="text" 
                                       class="form-control" 
                                       name="features[]" 
                                       placeholder="Enter feature" 
                                       required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-danger remove-feature">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-sm btn-success" id="add-feature">
                        <i class="fas fa-plus"></i> Add Feature
                    </button>
                    @error('features')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Limits Section -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">Package Limits</h6>
                        <small class="text-muted">Set to -1 for unlimited</small>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="max_students" class="font-weight-bold">Max Students <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('max_students') is-invalid @enderror" 
                                           id="max_students" 
                                           name="max_students" 
                                           value="{{ old('max_students', $package->getLimit('max_students')) }}" 
                                           required>
                                    @error('max_students')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="max_instructors" class="font-weight-bold">Max Instructors <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('max_instructors') is-invalid @enderror" 
                                           id="max_instructors" 
                                           name="max_instructors" 
                                           value="{{ old('max_instructors', $package->getLimit('max_instructors')) }}" 
                                           required>
                                    @error('max_instructors')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="max_vehicles" class="font-weight-bold">Max Vehicles</label>
                                    <input type="number" 
                                           class="form-control @error('max_vehicles') is-invalid @enderror" 
                                           id="max_vehicles" 
                                           name="max_vehicles" 
                                           value="{{ old('max_vehicles', $package->getLimit('max_vehicles')) }}">
                                    @error('max_vehicles')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Package Options -->
                <div class="form-group mt-3">
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="is_popular" 
                               name="is_popular" 
                               value="1" 
                               {{ old('is_popular', $package->is_popular) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_popular">
                            Mark as Popular Package
                        </label>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="is_active" 
                               name="is_active" 
                               value="1" 
                               {{ old('is_active', $package->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Active Package
                        </label>
                    </div>
                </div>

                <!-- Warning if package has schools -->
                @if($package->schools()->count() > 0)
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> This package is currently used by {{ $package->schools()->count() }} school(s). 
                    Changes to limits may affect existing schools.
                </div>
                @endif

                <!-- Form Actions -->
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Package
                    </button>
                    <a href="{{ route('admin.subscription-packages.show', $package) }}" class="btn btn-info">
                        <i class="fas fa-eye"></i> View Package
                    </a>
                    <a href="{{ route('admin.subscription-packages.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add feature functionality
    document.getElementById('add-feature').addEventListener('click', function() {
        const container = document.getElementById('features-container');
        const newFeature = document.createElement('div');
        newFeature.className = 'input-group mb-2 feature-input';
        newFeature.innerHTML = `
            <input type="text" 
                   class="form-control" 
                   name="features[]" 
                   placeholder="Enter feature" 
                   required>
            <div class="input-group-append">
                <button type="button" class="btn btn-danger remove-feature">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(newFeature);
    });

    // Remove feature functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-feature')) {
            const featureInputs = document.querySelectorAll('.feature-input');
            if (featureInputs.length > 1) {
                e.target.closest('.feature-input').remove();
            } else {
                alert('At least one feature is required.');
            }
        }
    });

    // Auto-calculate yearly discount
    const monthlyPrice = document.getElementById('monthly_price');
    const yearlyPrice = document.getElementById('yearly_price');
    
    function updateDiscount() {
        const monthly = parseFloat(monthlyPrice.value) || 0;
        const yearly = parseFloat(yearlyPrice.value) || 0;
        
        if (monthly > 0 && yearly > 0) {
            const yearlyEquivalent = monthly * 12;
            if (yearly < yearlyEquivalent) {
                const discount = Math.round(((yearlyEquivalent - yearly) / yearlyEquivalent) * 100);
                const savings = yearlyEquivalent - yearly;
                yearlyPrice.setAttribute('title', `${discount}% discount (Save $${savings.toFixed(2)})`);
            }
        }
    }
    
    monthlyPrice.addEventListener('input', updateDiscount);
    yearlyPrice.addEventListener('input', updateDiscount);
    updateDiscount(); // Run once on page load
});
</script>
@endpush