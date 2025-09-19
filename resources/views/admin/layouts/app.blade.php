<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - {{ config('app.name', 'Driving School') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --info-color: #36b9cc;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.35rem;
            margin: 0.125rem 0.5rem;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 16px;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 80px;
            text-decoration: none;
            color: #fff;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .content-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .topbar {
            background-color: #fff;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 1rem 1.5rem;
        }

        .main-content {
            padding: 1.5rem;
        }

        .sidebar-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            margin: 0.5rem 0;
        }

        .sidebar-heading {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1rem;
            padding: 0.75rem 1rem 0.25rem;
        }

        /* Dropdown Styles */
        .collapse-inner {
            background-color: #f8f9fc;
            border-radius: 0.5rem;
            margin: 0 1rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .collapse-header {
            color: #6e707e;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1rem;
            padding: 0.75rem 1.5rem 0.25rem;
            margin-bottom: 0;
        }

        .collapse-item {
            display: block;
            padding: 0.5rem 1.5rem;
            color: #3a3b45;
            text-decoration: none;
            font-size: 0.85rem;
            white-space: nowrap;
            border-radius: 0.35rem;
            margin: 0.125rem 0.5rem;
        }

        .collapse-item:hover {
            background-color: rgba(78, 115, 223, 0.1);
            color: #4e73df;
        }

        .collapse-item.active {
            background-color: #4e73df;
            color: #fff;
        }

        .collapse-item i {
            width: 16px;
            text-align: center;
        }

        .nav-link.collapsed::after {
            content: '\f107';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            float: right;
            margin-top: 2px;
        }

        .nav-link:not(.collapsed)::after {
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            float: right;
            margin-top: 2px;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .content-wrapper {
                margin-left: 0;
            }
        }

        .impersonation-banner {
            background: linear-gradient(45deg, #ff6b6b, #feca57);
            color: white;
            padding: 0.5rem;
            text-align: center;
            font-weight: bold;
        }
    </style>

    @stack('styles')
</head>
<body>
    {{-- Impersonation Banner --}}
    @if(session('super_admin_id'))
        <div class="impersonation-banner">
            <i class="fas fa-user-secret"></i>
            Impersonation Mode Active - You are logged in as a school admin
            <a href="{{ route('admin.schools.return-super-admin') }}" class="btn btn-sm btn-light ms-2">
                <i class="fas fa-crown"></i> Return to Super Admin
            </a>
        </div>
    @endif

    <!-- Sidebar -->
    <nav class="sidebar">
        <a class="sidebar-brand" href="{{ Auth::user()->isSuperAdmin() ? route('admin.super.dashboard') : route('admin.dashboard') }}">
            <div class="sidebar-brand-icon">
                <i class="fas fa-car-side"></i>
            </div>
            <div class="sidebar-brand-text ms-2">
                @if(Auth::user()->isSuperAdmin())
                    Super Admin
                @else
                    Admin Panel
                @endif
            </div>
        </a>

        <hr class="sidebar-divider my-0">

        <!-- Dashboard -->
        <ul class="navbar-nav">
            <li class="nav-item {{ request()->routeIs('admin.dashboard*', 'admin.super.dashboard*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ Auth::user()->isSuperAdmin() ? route('admin.super.dashboard') : route('admin.dashboard') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        </ul>

        <hr class="sidebar-divider">

        @if(Auth::user()->isSuperAdmin())
            <!-- SUPER ADMIN ONLY MENU -->
            <div class="sidebar-heading">System Management</div>
            <ul class="navbar-nav">
                <!-- Schools Management -->
                <li class="nav-item {{ request()->routeIs('admin.schools*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.schools.index') }}">
                        <i class="fas fa-fw fa-school"></i>
                        <span>Schools</span>
                    </a>
                </li>

                <!-- System Users -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.users.all*', 'admin.users.super-admins*') ? '' : 'collapsed' }}"
                       href="#" data-bs-toggle="collapse" data-bs-target="#collapseSystemUsers"
                       aria-expanded="{{ request()->routeIs('admin.users.all*', 'admin.users.super-admins*') ? 'true' : 'false' }}"
                       aria-controls="collapseSystemUsers">
                        <i class="fas fa-fw fa-users-cog"></i>
                        <span>System Users</span>
                    </a>
                    <div id="collapseSystemUsers" class="collapse {{ request()->routeIs('admin.users.all*', 'admin.users.super-admins*') ? 'show' : '' }}"
                         aria-labelledby="headingSystemUsers" data-bs-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">User Management:</h6>
                            <a class="collapse-item {{ request()->routeIs('admin.users.all*') ? 'active' : '' }}"
                               href="{{ route('admin.users.all') }}">
                                <i class="fas fa-users text-sm me-2"></i>All Users
                            </a>
                            <a class="collapse-item {{ request()->routeIs('admin.users.super-admins*') ? 'active' : '' }}"
                               href="{{ route('admin.users.super-admins') }}">
                                <i class="fas fa-crown text-sm me-2"></i>Super Admins
                            </a>
                        </div>
                    </div>
                </li>

                <!-- System Reports -->
                <li class="nav-item {{ request()->routeIs('admin.reports.system*', 'admin.system.stats*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.reports.system') }}">
                        <i class="fas fa-fw fa-chart-area"></i>
                        <span>System Reports</span>
                    </a>
                </li>

                <!-- Subscriptions -->
                <li class="nav-item {{ request()->routeIs('admin.subscriptions*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.subscriptions.index') }}">
                        <i class="fas fa-fw fa-credit-card"></i>
                        <span>Subscriptions</span>
                    </a>
                </li>

                <!-- System Settings -->
                <li class="nav-item {{ request()->routeIs('admin.system.settings*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.system.settings') }}">
                        <i class="fas fa-fw fa-cogs"></i>
                        <span>System Settings</span>
                    </a>
                </li>
            </ul>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">Monitoring</div>
            <ul class="navbar-nav">
                <!-- System Health -->
                <li class="nav-item {{ request()->routeIs('admin.system.health*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.system.health') }}">
                        <i class="fas fa-fw fa-heartbeat"></i>
                        <span>System Health</span>
                    </a>
                </li>

                <!-- Activity Logs -->
                <li class="nav-item {{ request()->routeIs('admin.logs*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.logs.index') }}">
                        <i class="fas fa-fw fa-list-alt"></i>
                        <span>Activity Logs</span>
                    </a>
                </li>

                <!-- Backups -->
                <li class="nav-item {{ request()->routeIs('admin.backups*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.backups.index') }}">
                        <i class="fas fa-fw fa-database"></i>
                        <span>Backups</span>
                    </a>
                </li>
            </ul>

        @else
            <!-- SCHOOL ADMIN ONLY MENU -->
            <div class="sidebar-heading">School Management</div>
            <ul class="navbar-nav">
                <!-- Users -->
                <li class="nav-item {{ request()->routeIs('admin.users.index*', 'admin.users.create*', 'admin.users.show*', 'admin.users.edit*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.users.index') }}">
                        <i class="fas fa-fw fa-users"></i>
                        <span>Users</span>
                    </a>
                </li>

                <!-- Students -->
                <li class="nav-item {{ request()->routeIs('admin.students*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.students.index') }}">
                        <i class="fas fa-fw fa-user-graduate"></i>
                        <span>Students</span>
                    </a>
                </li>

                <!-- Instructors -->
                <li class="nav-item {{ request()->routeIs('admin.instructors*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.instructors.index') }}">
                        <i class="fas fa-fw fa-chalkboard-teacher"></i>
                        <span>Instructors</span>
                    </a>
                </li>

                <!-- Schedules -->
                <li class="nav-item {{ request()->routeIs('admin.schedules*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.schedules.index') }}">
                        <i class="fas fa-fw fa-calendar-alt"></i>
                        <span>Schedules</span>
                    </a>
                </li>

                <!-- Fleet -->
                <li class="nav-item {{ request()->routeIs('admin.fleet*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.fleet.index') }}">
                        <i class="fas fa-fw fa-car"></i>
                        <span>Vehicles</span>
                    </a>
                </li>

                <!-- Courses -->
                <li class="nav-item {{ request()->routeIs('admin.courses*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.courses.index') }}">
                        <i class="fas fa-fw fa-graduation-cap"></i>
                        <span>Courses</span>
                    </a>
                </li>
            </ul>

            <hr class="sidebar-divider">

            <!-- Financial Management (School Admin) -->
            <div class="sidebar-heading">Financial</div>
            <ul class="navbar-nav">
                <!-- Invoices -->
                <li class="nav-item {{ request()->routeIs('admin.invoices*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.invoices.index') }}">
                        <i class="fas fa-fw fa-file-invoice"></i>
                        <span>Invoices</span>
                    </a>
                </li>

                <!-- Payments -->
                <li class="nav-item {{ request()->routeIs('admin.payments*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.payments.index') }}">
                        <i class="fas fa-fw fa-credit-card"></i>
                        <span>Payments</span>
                    </a>
                </li>

                <!-- Reports -->
                <li class="nav-item {{ request()->routeIs('admin.reports.index*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.reports.index') }}">
                        <i class="fas fa-fw fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                </li>
            </ul>

            <hr class="sidebar-divider">

            <!-- School Settings -->
            <div class="sidebar-heading">Settings</div>
            <ul class="navbar-nav">
                <!-- My School -->
                <li class="nav-item {{ request()->routeIs('admin.my-school*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.my-school') }}">
                        <i class="fas fa-fw fa-school"></i>
                        <span>My School</span>
                    </a>
                </li>

                <!-- Settings -->
                <li class="nav-item {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.settings') }}">
                        <i class="fas fa-fw fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        @endif

        <hr class="sidebar-divider d-none d-md-block">

        <!-- Sidebar Toggler (for mobile) -->
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle" style="background: rgba(255,255,255,0.2); color: white; width: 2rem; height: 2rem;"></button>
        </div>
    </nav>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Topbar -->
        <nav class="topbar d-flex justify-content-between align-items-center">
            <!-- Mobile Sidebar Toggle -->
            <button class="btn btn-link d-md-none" id="sidebarToggleTop">
                <i class="fa fa-bars"></i>
            </button>

            <!-- Breadcrumb -->
            <div class="d-none d-md-block">
                @yield('breadcrumb')
            </div>

            <!-- User Info -->
            <div class="dropdown">
                <a class="dropdown-toggle text-decoration-none d-flex align-items-center" href="#"
                   data-bs-toggle="dropdown">
                    <div class="me-2 text-end d-none d-lg-block">
                        <div class="fw-bold">{{ Auth::user()->fname }} {{ Auth::user()->lname }}</div>
                        <small class="text-muted">
                            @if(Auth::user()->isSuperAdmin())
                                Super Administrator
                            @else
                                {{ ucfirst(Auth::user()->role) }}
                                @if(Auth::user()->school)
                                    • {{ Auth::user()->school->name }}
                                @endif
                            @endif
                        </small>
                    </div>
                    <div class="avatar bg-primary rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 40px; height: 40px;">
                        <i class="fas fa-user text-white"></i>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('admin.profile') }}">
                        <i class="fas fa-user me-2"></i> Profile
                    </a></li>
                    @if(Auth::user()->isAdmin())
                        <li><a class="dropdown-item" href="{{ route('admin.settings') }}">
                            <i class="fas fa-cog me-2"></i> Settings
                        </a></li>
                    @endif
                    @if(session('super_admin_id'))
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-warning" href="{{ route('admin.schools.return-super-admin') }}">
                            <i class="fas fa-crown me-2"></i> Return to Super Admin
                        </a></li>
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Main Content Area --}}
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Sidebar toggle functionality
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('toggled');
        });

        document.getElementById('sidebarToggleTop')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Add active state to current navigation items
        const currentUrl = window.location.pathname;
        document.querySelectorAll('.nav-link').forEach(function(link) {
            if (link.getAttribute('href') === currentUrl) {
                link.classList.add('active');
                // Also activate parent if it's a collapse item
                const parent = link.closest('.collapse');
                if (parent) {
                    parent.classList.add('show');
                    parent.previousElementSibling?.classList.add('active');
                }
            }
        });

        // Handle collapse items
        document.querySelectorAll('.collapse-item').forEach(function(item) {
            if (item.getAttribute('href') === currentUrl) {
                item.classList.add('active');
                // Show parent collapse
                const parent = item.closest('.collapse');
                if (parent) {
                    parent.classList.add('show');
                    // Remove collapsed class from trigger
                    const trigger = document.querySelector(`[data-bs-target="#${parent.id}"]`);
                    if (trigger) {
                        trigger.classList.remove('collapsed');
                        trigger.setAttribute('aria-expanded', 'true');
                    }
                }
            }
        });

        // Fix for Bootstrap 5 collapse behavior
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all collapse elements
            const collapseElements = document.querySelectorAll('.collapse');
            collapseElements.forEach(function(element) {
                const collapse = new bootstrap.Collapse(element, {
                    toggle: false
                });

                // Handle toggle button state
                const trigger = document.querySelector(`[data-bs-target="#${element.id}"]`);
                if (trigger) {
                    element.addEventListener('show.bs.collapse', function() {
                        trigger.classList.remove('collapsed');
                        trigger.setAttribute('aria-expanded', 'true');
                    });

                    element.addEventListener('hide.bs.collapse', function() {
                        trigger.classList.add('collapsed');
                        trigger.setAttribute('aria-expanded', 'false');
                    });
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
