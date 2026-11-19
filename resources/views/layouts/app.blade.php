<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'LibrarySystem')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('dashboard') }}">Library<span>System</span></a>
        <nav aria-label="Navigasi utama">
            <a href="#books">Buku</a>
            <a href="#members">Anggota</a>
            <a href="#loans">Peminjaman</a>
            <a href="#fines">Denda</a>
        </nav>
    </header>

    <main class="container">
        @if (session('success'))
            <div class="notice notice-success" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="notice notice-error" role="alert">
                <strong>Periksa kembali data yang dimasukkan.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
