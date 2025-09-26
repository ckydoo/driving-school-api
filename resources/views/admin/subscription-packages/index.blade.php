{{-- resources/views/admin/subscription-packages/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Subscription Packages')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-boxes"></i> Subscription Packages
        </h1>
        <a href="{{ route('admin.subscription-packages.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Create Package
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <!-- Packages List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Subscription Packages ({{ $packages->count() }})
            </h6>
        </div>
        <div class="card-body">
            @if($packages->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Monthly Price</th>
                                <th>Yearly Price</th>
                                <th>Features</th>
                                <th>Limits</th>
                                <th>Status</th>
                                <th>Schools</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($packages as $package)
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ $package->name }}</div>
                                    @if($package->is_popular)
                                        <span class="badge badge-warning">Popular</span>
                                    @endif
                                </td>
                                <td>${{ number_format($package->monthly_price, 2) }}</td>
                                <td>
                                    @if($package->yearly_price)
                                        ${{ number_format($package->yearly_price, 2) }}
                                        <small class="text-success">({{ $package->getYearlyDiscount() }}% off)</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <small>
                                        @foreach(array_slice($package->features, 0, 3) as $feature)
                                            • {{ $feature }}<br>
                                        @endforeach
                                        @if(count($package->features) > 3)
                                            <span class="text-muted">+{{ count($package->features) - 3 }} more</span>
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <small>
                                        Students: {{ $package->isUnlimited('max_students') ? 'Unlimited' : $package->getLimit('max_students') }}<br>
                                        Instructors: {{ $package->isUnlimited('max_instructors') ? 'Unlimited' : $package->getLimit('max_instructors') }}<br>
                                        Vehicles: {{ $package->isUnlimited('max_vehicles') ? 'Unlimited' : $package->getLimit('max_vehicles') }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $package->is_active ? 'success' : 'secondary' }} text-primary">
                                        {{ $package->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-info text-dark">{{ $package->schools()->count() }}</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.subscription-packages.show', $package) }}" 
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.subscription-packages.edit', $package) }}" 
                                           class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" 
                                              action="{{ route('admin.subscription-packages.toggle-status', $package) }}" 
                                              style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-{{ $package->is_active ? 'secondary' : 'success' }} btn-sm">
                                                <i class="fas fa-{{ $package->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-boxes fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-500">No subscription packages found</h5>
                    <p class="text-gray-400">Create your first subscription package to get started.</p>
                    <a href="{{ route('admin.subscription-packages.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Package
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection