{{-- resources/views/admin/dashboard.blade.php - FIXED for your models --}}

@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                    <p class="mb-0 text-muted">Welcome back, {{ Auth::user()->fname }}!</p>
                </div>
                <div class="text-muted">
                    <i class="fas fa-clock"></i> {{ now()->format('M d, Y - H:i') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        {{-- Schools --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Schools
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_schools']) }}</div>
                            <div class="text-xs text-muted">{{ $stats['active_schools'] }} active</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-school fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Users --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_users']) }}</div>
                            <div class="text-xs text-muted">{{ $stats['total_students'] }} students, {{ $stats['total_instructors'] }} instructors</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Vehicles --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Fleet
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_vehicles'] }}</div>
                            <div class="text-xs text-muted">{{ $stats['available_vehicles'] }} available</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-car fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Revenue --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($stats['total_revenue'], 2) }}</div>
                            <div class="text-xs text-muted">{{ $stats['pending_invoices'] }} pending invoices</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity Row --}}
    <div class="row mb-4">
        {{-- Recent Users --}}
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Users</h6>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> Add User
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($recentUsers) && $recentUsers->count() > 0)
                        @foreach($recentUsers as $user)
                            <div class="d-flex align-items-center mb-3">
                                <div class="mr-3">
                                    <div class="icon-circle bg-{{ $user->role === 'admin' ? 'primary' : ($user->role === 'instructor' ? 'info' : 'secondary') }}">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold">{{ $user->full_name }}</div>
                                    <div class="small text-gray-500">
                                        {{ $user->school->name ?? 'No School' }} •
                                        <span class="badge badge-{{ $user->role === 'admin' ? 'primary' : ($user->role === 'instructor' ? 'info' : 'secondary') }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="small text-gray-500">{{ $user->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        @endforeach
                    @elseif(isset($recentStudents) && $recentStudents->count() > 0)
                        @foreach($recentStudents as $student)
                            <div class="d-flex align-items-center mb-3">
                                <div class="mr-3">
                                    <div class="icon-circle bg-primary">
                                        <i class="fas fa-graduation-cap text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold">{{ $student->full_name }}</div>
                                    <div class="small text-gray-500">
                                        {{ $student->email }} •
                                        <span class="badge badge-{{ $student->status === 'active' ? 'success' : 'warning' }}">
                                            {{ ucfirst($student->status) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="small text-gray-500">{{ $student->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-user-plus fa-3x mb-3"></i>
                            <p>No recent users</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent/Upcoming Schedules --}}
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        {{ isset($upcomingSchedules) ? 'Upcoming Lessons' : 'Recent Schedules' }}
                    </h6>
                    <a href="{{ route('admin.schedules.create') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> {{ isset($upcomingSchedules) ? 'Schedule Lesson' : 'Add Schedule' }}
                    </a>
                </div>
                <div class="card-body">
                    @php
                        $schedules = $upcomingSchedules ?? $recentSchedules ?? collect();
                    @endphp

                    @if($schedules->count() > 0)
                        @foreach($schedules as $schedule)
                            <div class="d-flex align-items-center mb-3">
                                <div class="mr-3">
                                    <div class="icon-circle bg-info">
                                        <i class="fas fa-calendar text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold">
                                        {{-- FIXED: Handle both relationship and direct access --}}
                                        @if(isset($schedule->student_info))
                                            {{ $schedule->student_info->full_name ?? 'Student #' . $schedule->student }}
                                        @elseif($schedule->student instanceof \App\Models\User)
                                            {{ $schedule->student->full_name }}
                                        @else
                                            Student #{{ $schedule->student }}
                                        @endif
                                    </div>
                                    <div class="small text-gray-500">
                                        with
                                        @if(isset($schedule->instructor_info))
                                            {{ $schedule->instructor_info->full_name ?? 'Instructor #' . $schedule->instructor }}
                                        @elseif($schedule->instructor instanceof \App\Models\User)
                                            {{ $schedule->instructor->full_name }}
                                        @else
                                            Instructor #{{ $schedule->instructor }}
                                        @endif
                                        •
                                        @if(isset($schedule->start))
                                            {{ \Carbon\Carbon::parse($schedule->start)->format('M j \a\t H:i') }}
                                        @else
                                            Date TBD
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-{{ $schedule->status === 'completed' ? 'success' : 'info' }}">
                                        {{ ucfirst($schedule->status ?? 'pending') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-calendar-plus fa-3x mb-3"></i>
                            <p>{{ isset($upcomingSchedules) ? 'No upcoming lessons' : 'No recent schedules' }}</p>
                            <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary btn-sm">
                                {{ isset($upcomingSchedules) ? 'Schedule First Lesson' : 'Create First Schedule' }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions Row --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-block">
                                <i class="fas fa-user-plus"></i> Add User
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.schedules.create') }}" class="btn btn-success btn-block">
                                <i class="fas fa-calendar-plus"></i> Schedule Lesson
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.fleet.index') }}" class="btn btn-info btn-block">
                                <i class="fas fa-car"></i> Manage Fleet
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-chart-bar"></i> View Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .icon-circle {
        height: 2.5rem;
        width: 2.5rem;
        border-radius: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush
@endsection
