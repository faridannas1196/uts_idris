<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SIKAD') | Persib Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.22.0/dist/sweetalert2.min.css" rel="stylesheet">
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
        .swal2-popup { border-radius: .75rem; }
        .swal2-toast { border-radius: .75rem; }
        .swal2-confirm { background-color: var(--persib-blue) !important; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-sikad">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ auth()->check() ? route('dashboard') : route('login') }}">
                <span class="brand-mark" aria-hidden="true">PS</span>
                <span class="brand-copy">SIKAD <small>PERSIB UNIVERSITY</small></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavigation" aria-controls="mainNavigation" aria-expanded="false" aria-label="Buka navigasi">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavigation">
                <div class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    @auth
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Beranda</a>
                        @if (auth()->user()->role === 'admin')
                            <a class="nav-link {{ request()->routeIs('program-studi.*') ? 'active' : '' }}" href="{{ route('program-studi.index') }}">Program studi</a>
                            <a class="nav-link {{ request()->routeIs('mahasiswa.*') ? 'active' : '' }}" href="{{ route('mahasiswa.index') }}">Mahasiswa</a>
                            <a class="nav-link {{ request()->routeIs('pengajar.*') ? 'active' : '' }}" href="{{ route('pengajar.index') }}">Pengajar</a>
                            <a class="nav-link {{ request()->routeIs('mata-kuliah.*') ? 'active' : '' }}" href="{{ route('mata-kuliah.index') }}">Mata kuliah</a>
                            <a class="nav-link {{ request()->routeIs('kelas.*') ? 'active' : '' }}" href="{{ route('kelas.index') }}">Kelas</a>
                        @elseif (auth()->user()->role === 'dosen')
                            <a class="nav-link {{ request()->routeIs('dosen.kelas') ? 'active' : '' }}" href="{{ route('dosen.kelas') }}">Kelas saya</a>
                        @elseif (auth()->user()->role === 'mahasiswa')
                            <a class="nav-link {{ request()->routeIs('mahasiswa.kelas') ? 'active' : '' }}" href="{{ route('mahasiswa.kelas') }}">KRS & kelas</a>
                        @endif
                        <span class="account-meta px-lg-2">ID {{ auth()->id() }} · {{ auth()->user()->username }} · {{ ucfirst(auth()->user()->role) }}</span>
                        <form method="POST" action="{{ route('logout') }}" data-confirm-logout>
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
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.22.0/dist/sweetalert2.all.min.js"></script>
    @php($flashNotifications = [
        'success' => session('success'),
        'error' => session('error'),
        'warning' => session('warning'),
        'info' => session('info'),
    ])
    <script>
        (() => {
            const toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4500,
                timerProgressBar: true,
                didOpen: (toastElement) => {
                    toastElement.addEventListener('mouseenter', Swal.stopTimer);
                    toastElement.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });

            window.showSweetAlert = (options) => Swal.fire(options);
            window.showToast = (icon, message) => toast.fire({ icon, titleText: message });

            document.querySelectorAll('form[data-confirm-logout]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    if (form.dataset.confirmed === 'true') {
                        return;
                    }

                    event.preventDefault();

                    window.showSweetAlert({
                        icon: 'question',
                        title: 'Keluar dari akun?',
                        text: 'Anda yakin ingin keluar?',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, keluar',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                        focusCancel: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.dataset.confirmed = 'true';
                            form.requestSubmit();
                        }
                    });
                });
            });

            const showFlashNotifications = async () => {
                const notifications = {{ Illuminate\Support\Js::from($flashNotifications) }};

                for (const [icon, message] of Object.entries(notifications)) {
                    if (message) {
                        await window.showToast(icon, message);
                    }
                }
            };

            @if ($errors->any())
                window.showSweetAlert({
                    icon: 'warning',
                    title: 'Validasi gagal',
                    text: 'Periksa kembali data yang ditandai.'
                }).then(showFlashNotifications);
            @else
                showFlashNotifications();
            @endif
        })();
    </script>
</body>
</html>
