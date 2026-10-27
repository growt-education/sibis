<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul', 'SIBIS') · Bimbingan Skripsi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/sibis.css') }}">
    @stack('kepala')
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark sibis-nav mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('beranda') }}">SIBIS</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"
                aria-controls="menu" aria-expanded="false" aria-label="Buka menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            @auth
                @unless (auth()->user()->wajib_ganti_password)
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link @if (request()->routeIs('beranda')) active @endif" href="{{ route('beranda') }}">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link @if (request()->routeIs('skripsi.*')) active @endif" href="{{ route('skripsi.index') }}">Skripsi</a></li>
                    @if (auth()->user()->peran === \App\Enums\PeranUser::Koordinator)
                        <li class="nav-item"><a class="nav-link @if (request()->routeIs('dosen.*')) active @endif" href="{{ route('dosen.index') }}">Dosen</a></li>
                        <li class="nav-item"><a class="nav-link @if (request()->routeIs('mahasiswa.*')) active @endif" href="{{ route('mahasiswa.index') }}">Mahasiswa</a></li>
                        <li class="nav-item"><a class="nav-link @if (request()->routeIs('bidang-minat.*')) active @endif" href="{{ route('bidang-minat.index') }}">Bidang minat</a></li>
                    @endif
                </ul>
                @endunless
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <a class="nav-link small" href="{{ route('akun.password') }}">{{ auth()->user()->name }} ({{ auth()->user()->peran->label() }})</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline-light btn-sm">Keluar</button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</nav>

<main class="container pb-5">
    @include('partials.flash')
    @yield('isi')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('skrip')
</body>
</html>
