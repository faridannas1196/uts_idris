<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SIKAD') | Persib Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --persib-blue: #0b3d91; --persib-deep: #071d49; --persib-yellow: #ffd21c; --sikad-wash: #f3f6fb; --sikad-ink: #17243a; }
        body { background: var(--sikad-wash); color: var(--sikad-ink); }
        .navbar-sikad { background: linear-gradient(105deg, var(--persib-deep), var(--persib-blue)); border-bottom: 4px solid var(--persib-yellow); }
        .navbar-sikad .navbar-brand { letter-spacing: 0; }
        .brand-mark { display: grid; place-items: center; width: 38px; height: 38px; color: var(--persib-yellow); border: 2px solid var(--persib-yellow); font-size: .72rem; font-weight: 800; background: repeating-linear-gradient(90deg, transparent 0 8px, rgba(255, 210, 28, .14) 8px 11px); }
        .brand-copy { line-height: 1.05; }
        .brand-copy small { display: block; margin-top: 3px; color: var(--persib-yellow); font-size: .58rem; font-weight: 700; }
        .navbar-sikad .nav-link { color: rgba(255, 255, 255, .76); }
        .navbar-sikad .nav-link:hover, .navbar-sikad .nav-link.active { color: #fff; }
        .account-meta { color: rgba(255, 255, 255, .78); font-size: .76rem; white-space: nowrap; }
        .btn-success, .btn-primary { --bs-btn-bg: var(--persib-blue); --bs-btn-border-color: var(--persib-blue); --bs-btn-hover-bg: var(--persib-deep); --bs-btn-hover-border-color: var(--persib-deep); }
        .btn-outline-success { --bs-btn-color: var(--persib-blue); --bs-btn-border-color: var(--persib-blue); --bs-btn-hover-bg: var(--persib-blue); --bs-btn-hover-border-color: var(--persib-blue); }
        .text-success { color: var(--persib-blue) !important; }
        .text-bg-warning { color: var(--persib-deep) !important; background-color: var(--persib-yellow) !important; }
        .card { border-radius: .5rem; }
        .form-control, .form-select, .btn { border-radius: .375rem; }
        .login-panel { border-top: 5px solid var(--persib-yellow); }
        .login-mark { color: var(--persib-blue); border-color: var(--persib-blue); background-color: #eef3fb; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-sikad">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ auth()->check() ? route('dashboard') : route('login') }}">
                <span class="brand-mark" aria-hidden="true">PS</span>
                <span class="brand-copy">SIKAD <small>PERSIB BANDUNG</small></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavigation" aria-controls="mainNavigation" aria-expanded="false" aria-label="Buka navigasi">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavigation">
                <div class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    @auth
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Beranda</a>
                        @if (auth()->user()->role === 'admin')
                            <a class="nav-link {{ request()->routeIs('mahasiswa.*') ? 'active' : '' }}" href="{{ route('mahasiswa.index') }}">Mahasiswa</a>
                        @endif
                        <span class="account-meta px-lg-2">ID {{ auth()->id() }} · {{ auth()->user()->username }} · {{ ucfirst(auth()->user()->role) }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-link border-0 bg-transparent">Keluar</button>
                        </form>
                    @else
                        <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">Masuk</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="container py-4 py-md-5">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
