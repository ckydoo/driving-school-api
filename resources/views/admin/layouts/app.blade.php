{{-- resources/views/admin/layouts/app.blade.php --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard') - {{ config('app.name', 'Driving School') }}</title>

    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
            --sidebar-width: 14rem;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--light-color);
            margin: 0;
            padding: 0;
        }

        /* Main Wrapper */
        #wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s ease;
        }

        .sidebar-brand {
            height: 4.375rem;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 800;
            padding: 1.5rem 1rem;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
            z-index: 1;
            color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-brand:hover {
            color: #fff;
            text-decoration: none;
        }

        .sidebar-brand-icon {
            margin-right: 0.25rem;
            font-size: 2rem;
        }

        .sidebar-brand-text {
            font-size: 1.25rem;
            display: block;
            margin-left: 0.5rem;
        }

        .sidebar-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            margin: 0 1rem 1rem;
        }

        .sidebar-heading {
            text-align: center;
            padding: 0 1rem;
            font-weight: 800;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.1rem;
            color: rgba(255, 255, 255, 0.4);
            margin-bottom: 1rem;
        }

        .sidebar .nav-item {
            list-style: none;
        }

        .sidebar .nav-link {
            display: block;
            width: 100%;
            text-align: left;
            padding: 1rem;
            position: relative;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 400;
            transition: all 0.15s ease-in-out;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            text-decoration: none;
        }

        .sidebar .nav-link:hover i {
            color: #fff;
        }

        .sidebar .nav-link i {
            font-size: 0.85rem;
            margin-right: 0.25rem;
            text-align: center;
            width: 2rem;
            color: rgba(255, 255, 255, 0.5);
        }

        .sidebar .nav-item.active .nav-link {
            font-weight: 700;
            color: #fff;
        }

        .sidebar .nav-item.active .nav-link i {
            color: #fff;
        }

        /* Content Wrapper */
        .content-wrapper {
            width: 100%;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Topbar */
        .topbar {
            height: 4.375rem;
            background-color: #fff;
            border-bottom: 1px solid #e3e6f0;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .topbar .sidebar-toggler {
            width: 2.5rem;
            height: 2.5rem;
            border: none;
            background: none;
            cursor: pointer;
            display: none;
        }

        /* Main Content */
        .container-fluid {
            flex: 1;
            padding: 1.5rem;
        }

        /* Cards */
        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: 1px solid #e3e6f0;
            border-radius: 0.35rem;
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }

        /* Border utilities */
        .border-left-primary {
            border-left: 0.25rem solid var(--primary-color) !important;
        }

        .border-left-success {
            border-left: 0.25rem solid var(--success-color) !important;
        }

        .border-left-info {
            border-left: 0.25rem solid var(--info-color) !important;
        }

        .border-left-warning {
            border-left: 0.25rem solid var(--warning-color) !important;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(var(--sidebar-width) * -1);
            }

            .sidebar.active {
                margin-left: 0;
            }

            .content-wrapper {
                margin-left: 0;
                width: 100%;
            }

            .topbar .sidebar-toggler {
                display: block;
            }
        }

        /* Additional Styles */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .bg-primary {
            background-color: var(--primary-color) !important;
        }

        /* Badge Fixes - Ensure all text is visible */
        .badge {
            color: #fff !important;
            font-weight: 600;
            font-size: 0.75em;
            padding: 0.375em 0.75em;
            border-radius: 0.375rem;
        }

        .badge-primary {
            background-color: var(--primary-color) !important;
            color: #fff !important;
        }

        .badge-success {
            background-color: var(--success-color) !important;
            color: #fff !important;
        }

        .badge-danger {
            background-color: var(--danger-color) !important;
            color: #fff !important;
        }

        .badge-warning {
            background-color: #f39c12 !important;
            color: #fff !important;
        }

        .badge-info {
            background-color: var(--info-color) !important;
            color: #fff !important;
        }

        .badge-secondary {
            background-color: #6c757d !important;
            color: #fff !important;
        }

        .badge-dark {
            background-color: #343a40 !important;
            color: #fff !important;
        }

        /* Scroll to top button */
        .scroll-to-top {
            position: fixed;
            right: 1rem;
            bottom: 1rem;
            width: 2.75rem;
            height: 2.75rem;
            text-align: center;
            color: #fff;
            background: rgba(90, 92, 105, 0.5);
            line-height: 46px;
            border-radius: 100%;
            display: none;
            z-index: 1000;
        }

        .scroll-to-top:hover {
            background: rgba(90, 92, 105, 0.9);
            color: #fff;
            text-decoration: none;
        }
    </style>

    @stack('styles')
</head>

<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">
            <!-- Sidebar - Brand -->
            <a class="sidebar-brand" href="{{ route('admin.dashboard') }}">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-car-side"></i>
                </div>
                <div class="sidebar-brand-text">Admin Panel</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">Management</div>

            <!-- Nav Item - Users -->
            <li class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.users.index') }}">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Users</span>
                </a>
            </li>

            <!-- Nav Item - Schools -->
            <li class="nav-item {{ request()->routeIs('admin.schools*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.schools.index') }}">
                    <i class="fas fa-fw fa-school"></i>
                    <span>Schools</span>
                </a>
            </li>

            <!-- Nav Item - Fleet -->
            <li class="nav-item {{ request()->routeIs('admin.fleet*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.fleet.index') }}">
                    <i class="fas fa-fw fa-car"></i>
                    <span>Fleet</span>
                </a>
            </li>

            <!-- Nav Item - Courses -->
            <li class="nav-item {{ request()->routeIs('admin.courses*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.courses.index') }}">
                    <i class="fas fa-fw fa-book"></i>
                    <span>Courses</span>
                </a>
            </li>

            <!-- Nav Item - Schedules -->
            <li class="nav-item {{ request()->routeIs('admin.schedules*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.schedules.index') }}">
                    <i class="fas fa-fw fa-calendar"></i>
                    <span>Schedules</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">Financial</div>

            <!-- Nav Item - Invoices -->
            <li class="nav-item {{ request()->routeIs('admin.invoices*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.invoices.index') }}">
                    <i class="fas fa-fw fa-file-invoice"></i>
                    <span>Invoices</span>
                </a>
            </li>

            <!-- Nav Item - Payments -->
            <li class="nav-item {{ request()->routeIs('admin.payments*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.payments.index') }}">
                    <i class="fas fa-fw fa-credit-card"></i>
                    <span>Payments</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">Reports</div>

            <!-- Nav Item - Reports -->
            <li class="nav-item {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.reports.index') }}">
                    <i class="fas fa-fw fa-chart-area"></i>
                    <span>Reports</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle" style="background: rgba(255,255,255,0.2); color: white; width: 2rem; height: 2rem; border: none !important;"></button>
            </div>
        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Topbar -->
            <nav class="navbar navbar-expand navbar-light topbar mb-4">
                <!-- Sidebar Toggle (Topbar) -->
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3 sidebar-toggler">
                    <i class="fa fa-bars"></i>
                </button>

                <!-- Topbar Navbar -->
                <ul class="navbar-nav ml-auto">
                    <!-- Nav Item - User Information -->
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ Auth::user()->fname }} {{ Auth::user()->lname }}</span>
                            <div class="topbar-divider d-none d-sm-block"></div>
                            <i class="fas fa-user-circle fa-lg text-gray-400"></i>
                        </a>
                        <!-- Dropdown - User Information -->
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                            aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                Profile
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>
            <!-- End of Topbar -->

            <!-- Begin Page Content -->
            <div class="container-fluid">
                <!-- Page Content -->
                @yield('content')
            </div>
            <!-- /.container-fluid -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Custom scripts -->
    <script>
        $(document).ready(function() {
            // Initialize DataTables if table exists
            if ($('.datatable').length) {
                $('.datatable').DataTable({
                    "pageLength": 25,
                    "responsive": true,
                    "order": [[0, "desc"]]
                });
            }

            // Sidebar toggle functionality for mobile
            $("#sidebarToggleTop").on('click', function(e) {
                e.preventDefault();
                $(".sidebar").toggleClass("active");
            });

            // Sidebar toggle for desktop (collapse)
            $("#sidebarToggle").on('click', function(e) {
                e.preventDefault();
                $("body").toggleClass("sidebar-toggled");
                $(".sidebar").toggleClass("toggled");
            });

            // Close sidebar on mobile when clicking outside
            $(document).on('click', function(e) {
                if ($(window).width() < 768) {
                    if (!$(e.target).closest('.sidebar, #sidebarToggleTop').length) {
                        $('.sidebar').removeClass('active');
                    }
                }
            });

            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert:not(.alert-permanent)').fadeOut('slow');
            }, 5000);

            // Scroll to top functionality
            $(window).scroll(function() {
                if ($(this).scrollTop() > 100) {
                    $('.scroll-to-top').fadeIn();
                } else {
                    $('.scroll-to-top').fadeOut();
                }
            });

            $('.scroll-to-top').click(function(e) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: 0
                }, 600);
                return false;
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
