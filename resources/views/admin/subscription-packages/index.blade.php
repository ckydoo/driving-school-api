{{-- resources/views/admin/subscription-packages/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Subscription Packages')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Subscription Packages</h1>
        <a href="{{ route('admin.subscription-packages.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Package
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Monthly Price</th>
                            <th>Yearly Price</th>
                            <th>Features</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packages as $package)
                        <tr>
                            <td>
                                {{ $package->name }}
                                @if($package->is_popular)
                                    <span class="badge badge-primary">Popular</span>
                                @endif
                            </td>
                            <td>${{ number_format($package->monthly_price, 2) }}</td>
                            <td>
                                @if($package->yearly_price)
                                    ${{ number_format($package->yearly_price, 2) }}
                                    <small class="text-success">
                                        ({{ $package->getYearlyDiscount() }}% off)
                                    </small>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    {{ count($package->features) }} features
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $package->is_active ? 'success' : 'secondary' }}">
                                    {{ $package->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.subscription-packages.edit', $package) }}" 
                                   class="btn btn-sm btn-warning">Edit</a>
                                <form method="POST" 
                                      action="{{ route('admin.subscription-packages.destroy', $package) }}" 
                                      style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection