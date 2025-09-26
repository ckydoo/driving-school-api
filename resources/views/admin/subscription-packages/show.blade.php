{{-- resources/views/admin/subscription-packages/show.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Package: ' . $package->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-box"></i> {{ $package->name }}
            @if($package->is_popular)
                <span class="badge badge-warning ml-2">Popular</span>
            @endif
            <span class="badge badge-{{ $package->is_active ? 'success' : 'secondary' }} ml-2">
                {{ $package->is_active ? 'Active' : 'Inactive' }}
            </span>
        </h1>
        <div>
            <a href="{{ route('admin.subscription-packages.edit', $package) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.subscription-packages.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Packages
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="row">
        <!-- Package Details -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="font-weight-bold">Basic Information</h6>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="font-weight-bold">Name:</td>
                                    <td>{{ $package->name }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Slug:</td>
                                    <td><code>{{ $package->slug }}</code></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Description:</td>
                                    <td>{{ $package->description ?: 'No description' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Trial Period:</td>
                                    <td>{{ $package->trial_days }} days</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Sort Order:</td>
                                    <td>{{ $package->sort_order }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Created:</td>
                                    <td>{{ $package->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="font-weight-bold">Pricing</h6>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="font-weight-bold">Monthly Price:</td>
                                    <td class="text-success font-weight-bold">{{ $package->getFormattedMonthlyPrice() }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Yearly Price:</td>
                                    <td>
                                        @if($package->hasYearlyPricing())
                                            <span class="text-success font-weight-bold">{{ $package->getFormattedYearlyPrice() }}</span>
                                            @if($package->getYearlyDiscount() > 0)
                                                <small class="text-muted">
                                                    ({{ $package->getYearlyDiscount() }}% off - Save {{ $package->getFormattedYearlySavings() }})
                                                </small>
                                            @endif
                                        @else
                                            <span class="text-muted">Not available</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($package->isFree())
                                <tr>
                                    <td colspan="2">
                                        <span class="badge badge-info">Free Package</span>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Features</h6>
                </div>
                <div class="card-body">
                    @if($package->features && count($package->features) > 0)
                        <div class="row">
                            @foreach($package->features as $feature)
                                <div class="col-md-6 mb-2">
                                    <i class="fas fa-check text-success mr-2"></i>{{ $feature }}
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No features defined for this package.</p>
                    @endif
                </div>
            </div>

            <!-- Package Limits -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Package Limits</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="text-primary">
                                    <i class="fas fa-user-graduate fa-2x"></i>
                                </div>
                                <div class="mt-2">
                                    <div class="h5 mb-0">{{ $package->getLimitDescription('max_students') }}</div>
                                    <small class="text-muted">Students</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="text-info">
                                    <i class="fas fa-chalkboard-teacher fa-2x"></i>
                                </div>
                                <div class="mt-2">
                                    <div class="h5 mb-0">{{ $package->getLimitDescription('max_instructors') }}</div>
                                    <small class="text-muted">Instructors</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="text-warning">
                                    <i class="fas fa-car fa-2x"></i>
                                </div>
                                <div class="mt-2">
                                    <div class="h5 mb-0">{{ $package->getLimitDescription('max_vehicles') }}</div>
                                    <small class="text-muted">Vehicles</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schools Using This Package -->
            @if($package->schools()->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Schools Using This Package ({{ $package->schools()->count() }})
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>School Name</th>
                                    <th>Status</th>
                                    <th>Students</th>
                                    <th>Instructors</th>
                                    <th>Vehicles</th>
                                    <th>Usage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($package->schools()->limit(10)->get() as $school)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.subscriptions.show', $school) }}" class="text-decoration-none">
                                            {{ $school->name }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $school->subscription_status === 'active' ? 'success' : 'warning' }}">
                                            {{ ucfirst($school->subscription_status) }}
                                        </span>
                                    </td>
                                    <td>{{ $school->getCurrentUsage('max_students') }}</td>
                                    <td>{{ $school->getCurrentUsage('max_instructors') }}</td>
                                    <td>{{ $school->getCurrentUsage('max_vehicles') }}</td>
                                    <td>
                                        @php
                                            $studentUsage = $school->getUsagePercentage('max_students');
                                            $usageColor = $studentUsage > 80 ? 'danger' : ($studentUsage > 60 ? 'warning' : 'success');
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $usageColor }}" 
                                                 style="width: {{ $studentUsage }}%"
                                                 title="Students: {{ round($studentUsage, 1) }}%">
                                                {{ round($studentUsage, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($package->schools()->count() > 10)
                            <p class="text-muted mb-0">
                                Showing 10 of {{ $package->schools()->count() }} schools.
                                <a href="{{ route('admin.subscriptions.index') }}?subscription_package_id={{ $package->id }}">
                                    View all schools with this package
                                </a>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Statistics Sidebar -->
        <div class="col-lg-4">
            <!-- Package Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row no-gutters align-items-center mb-3">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Schools
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_schools'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-school fa-2x text-gray-300"></i>
                        </div>
                    </div>

                    <div class="row no-gutters align-items-center mb-3">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Schools
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['active_schools'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>

                    <div class="row no-gutters align-items-center mb-3">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Trial Schools
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['trial_schools'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>

                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Monthly Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($stats['monthly_revenue'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.subscription-packages.edit', $package) }}" 
                           class="btn btn-warning btn-sm btn-block">
                            <i class="fas fa-edit"></i> Edit Package
                        </a>
                        
                        <form method="POST" 
                              action="{{ route('admin.subscription-packages.toggle-status', $package) }}" 
                              style="display: inline;">
                            @csrf
                            <button type="submit" 
                                    class="btn btn-{{ $package->is_active ? 'secondary' : 'success' }} btn-sm btn-block">
                                <i class="fas fa-{{ $package->is_active ? 'pause' : 'play' }}"></i> 
                                {{ $package->is_active ? 'Deactivate' : 'Activate' }} Package
                            </button>
                        </form>

                        @if($package->schools()->count() === 0)
                        <form method="POST" 
                              action="{{ route('admin.subscription-packages.destroy', $package) }}" 
                              onsubmit="return confirm('Are you sure you want to delete this package? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm btn-block">
                                <i class="fas fa-trash"></i> Delete Package
                            </button>
                        </form>
                        @else
                        <div class="alert alert-warning small">
                            <i class="fas fa-exclamation-triangle"></i>
                            Cannot delete package while schools are using it.
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Package Preview -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Package Preview</h6>
                </div>
                <div class="card-body p-0">
                    <!-- This shows how the package would look in a pricing table -->
                    <div class="pricing-card {{ $package->is_popular ? 'popular' : '' }}">
                        <div class="card-body text-center p-4">
                            @if($package->is_popular)
                                <div class="badge badge-warning mb-2">Most Popular</div>
                            @endif
                            
                            <h5 class="card-title">{{ $package->name }}</h5>
                            
                            @if($package->isFree())
                                <div class="display-4 text-primary">FREE</div>
                            @else
                                <div class="display-4 text-primary">
                                    ${{ number_format($package->monthly_price, 0) }}
                                    <small class="text-muted">/month</small>
                                </div>
                                @if($package->hasYearlyPricing())
                                    <small class="text-success">
                                        Save {{ $package->getYearlyDiscount() }}% yearly
                                    </small>
                                @endif
                            @endif
                            
                            @if($package->description)
                                <p class="text-muted small mt-2">{{ $package->description }}</p>
                            @endif
                            
                            <div class="text-left small">
                                @foreach(array_slice($package->features, 0, 5) as $feature)
                                    <div class="mb-1">
                                        <i class="fas fa-check text-success mr-1"></i>{{ $feature }}
                                    </div>
                                @endforeach
                                @if(count($package->features) > 5)
                                    <div class="text-muted">+{{ count($package->features) - 5 }} more features</div>
                                @endif
                            </div>
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
.pricing-card.popular {
    border: 2px solid #ffc107;
    position: relative;
}

.pricing-card.popular::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(45deg, #ffc107, #ff8c00);
    z-index: -1;
    border-radius: inherit;
}

.progress {
    background-color: #e9ecef;
}

.table-borderless td {
    border: none;
    padding: 0.25rem 0.75rem;
}
</style>
@endpushheader py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Package Information</h6>
                </div>
                <div class="card-