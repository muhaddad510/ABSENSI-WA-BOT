<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', config('app.name', 'Absensi Bot'))</title>

    {{-- AdminLTE CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    @stack('css')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    {{-- ================= NAVBAR ================= --}}
    <nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="fas fa-user-circle mr-1"></i>
                    <span>{{ Auth::user()->name }}</span>
                    <i class="fas fa-caret-down ml-1"></i>
                </a>

                <div class="dropdown-menu dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">
                        {{ Auth::user()->email }}
                    </span>

                    <div class="dropdown-divider"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>

    {{-- ================= SIDEBAR ================= --}}
    <aside class="main-sidebar sidebar-dark-primary elevation-4">

        {{-- Brand --}}
        <a href="{{ route('dashboard') }}" class="brand-link text-center">
            <i class="fas fa-robot mr-1"></i>
            <span class="brand-text font-weight-bold">ABSENSI BOT</span>
        </a>

        <div class="sidebar">

            {{-- User Panel --}}
            <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
                <div class="image">
                    <img
                        src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0D8ABC&color=fff"
                        class="img-circle elevation-2"
                        alt="User Image"
                    >
                </div>
                <div class="info">
                    <a href="#" class="d-block font-weight-bold">
                        {{ Auth::user()->name }}
                    </a>
                    <span class="badge badge-{{ auth()->user()->isAdmin() ? 'danger' : 'primary' }}">
                        {{ auth()->user()->isAdmin() ? 'ADMIN' : 'DOSEN' }}
                    </span>
                </div>
            </div>

            {{-- Menu --}}
            <nav class="mt-2">
                @php
                    $isSettingOpen =
                        request()->routeIs('teaching-location.*') ||
                        request()->routeIs('bot-settings.*');
                @endphp

                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}"
                           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('students.index') }}"
                           class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-graduate"></i>
                            <p>Data Mahasiswa</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('class-rooms.index') }}"
                           class="nav-link {{ request()->routeIs('class-rooms.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chalkboard"></i>
                            <p>Data Kelas</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('monitoring.index') }}"
                           class="nav-link {{ request()->routeIs('monitoring.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-clipboard-check"></i>
                            <p>Monitoring Absensi</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('reports.index') }}"
                           class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Laporan</p>
                        </a>
                    </li>

                    <li class="nav-header">BOT WHATSAPP</li>

                    <li class="nav-item {{ $isSettingOpen ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $isSettingOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cog"></i>
                            <p>
                                Pengaturan
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('teaching-location.index') }}"
                                   class="nav-link {{ request()->routeIs('teaching-location.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-map-marker-alt"></i>
                                    <p>Lokasi Ngajar</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('bot-settings.index') }}"
                                   class="nav-link {{ request()->routeIs('bot-settings.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-robot"></i>
                                    <p>Konfigurasi Bot</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                </ul>
            </nav>
        </div>
    </aside>

    {{-- ================= CONTENT ================= --}}
    <div class="content-wrapper">

        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('page_title')</h1>
                    </div>
                    <div class="col-sm-6 text-right text-muted">
                        <small>{{ now()->translatedFormat('d F Y') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid pb-3">
                @yield('content')
            </div>
        </section>
    </div>

    {{-- Footer --}}
    <footer class="main-footer text-sm">
        <strong>&copy; {{ date('Y') }} {{ config('app.name') }}</strong>
        <div class="float-right d-none d-sm-inline-block">
            v1.0
        </div>
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

@stack('js')
</body>
</html>
