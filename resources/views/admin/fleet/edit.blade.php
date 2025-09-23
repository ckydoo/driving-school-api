{{-- resources/views/admin/fleet/edit.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Edit Vehicle - ' . $fleet->make . ' ' . $fleet->model)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit"></i> Edit Vehicle
        </h1>
        <div>
            <a href="{{ route('admin.fleet.show', $fleet) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Vehicle
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-car"></i> Vehicle Information
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

                    <form method="POST" action="{{ route('admin.fleet.update', $fleet) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="carplate" class="font-weight-bold">Car Plate <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('carplate') is-invalid @enderror"
                                           id="carplate"
                                           name="carplate"
                                           value="{{ old('carplate', $fleet->carplate) }}"
                                           required
                                           maxlength="20"
                                           placeholder="e.g., ABC-123">
                                    @error('carplate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="make" class="font-weight-bold">Make <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('make') is-invalid @enderror"
                                           id="make"
                                           name="make"
                                           value="{{ old('make', $fleet->make) }}"
                                           required
                                           maxlength="50"
                                           placeholder="e.g., Toyota">
                                    @error('make')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="model" class="font-weight-bold">Model <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('model') is-invalid @enderror"
                                           id="model"
                                           name="model"
                                           value="{{ old('model', $fleet->model) }}"
                                           required
                                           maxlength="50"
                                           placeholder="e.g., Corolla">
                                    @error('model')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="modelyear" class="font-weight-bold">Model Year <span class="text-danger">*</span></label>
                                    <input type="number"
                                           class="form-control @error('modelyear') is-invalid @enderror"
                                           id="modelyear"
                                           name="modelyear"
                                           value="{{ old('modelyear', $fleet->modelyear) }}"
                                           required
                                           min="1900"
                                           max="{{ date('Y') + 1 }}"
                                           placeholder="e.g., 2020">
                                    @error('modelyear')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status" class="font-weight-bold">Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror"
                                            id="status"
                                            name="status"
                                            required>
                                        <option value="">Select Status...</option>
                                        <option value="available" {{ old('status', $fleet->status) === 'available' ? 'selected' : '' }}>
                                            Available
                                        </option>
                                        <option value="maintenance" {{ old('status', $fleet->status) === 'maintenance' ? 'selected' : '' }}>
                                            Maintenance
                                        </option>
                                        <option value="retired" {{ old('status', $fleet->status) === 'retired' ? 'selected' : '' }}>
                                            Retired
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="instructor" class="font-weight-bold">Assigned Instructor</label>
                                    <select class="form-control @error('instructor') is-invalid @enderror"
                                            id="instructor"
                                            name="instructor">
                                        <option value="">No Instructor Assigned</option>
                                        @foreach($instructors as $instructor)
                                            <option value="{{ $instructor->id }}"
                                                    {{ old('instructor', $fleet->instructor) == $instructor->id ? 'selected' : '' }}>
                                                {{ $instructor->fname }} {{ $instructor->lname }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('instructor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Leave empty if no instructor should be assigned to this vehicle.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fas fa-save"></i> Update Vehicle
                                </button>
                                <a href="{{ route('admin.fleet.show', $fleet) }}" class="btn btn-secondary">
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
