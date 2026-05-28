<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Cegah FOUC: pakai tema tersimpan sebelum CSS dimuatkan --}}
    <script nonce="{{ $cspNonce ?? '' }}">
    (function(){try{var t=localStorage.getItem('ibook-theme');if(t==='dark'||t==='light')document.documentElement.classList.add(t);}catch(e){}})();
    </script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $tetapan['nama_sistem'] ?? 'iBook 2.0' }} — Sistem Tempahan Bilik Mesyuarat">
    <title>@yield('title', $tetapan['nama_sistem'] ?? 'iBook 2.0') — {{ $tetapan['nama_jabatan'] ?? 'Sistem Tempahan Bilik Mesyuarat' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERwTkw=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="{{ asset('css/ibook.css') }}">
    @stack('styles')
</head>
<body>

    {{-- ── Skip Navigation (WCAG 2.4.1) ──────────────────────────── --}}
    <a href="#kandungan-utama" class="skip-link">Langkau ke kandungan utama</a>
    <a href="#nav-utama" class="skip-link" style="left:220px">Langkau ke navigasi</a>

    <div id="sidebar-overlay" aria-hidden="true"></div>

    {{-- Pembolehubah dikongsi oleh sidebar & topbar --}}
    @php
        $namaSistem  = $tetapan['nama_sistem']  ?? 'iBook 2.0';
        $namaJabatan = $tetapan['nama_jabatan'] ?? '';
        $logoJabatan = $tetapan['logo_jabatan'] ?? '/images/jata-negara.png';
        if (empty($logoJabatan)) $logoJabatan = '/images/jata-negara.png';
    @endphp

    <div class="flex">
        @include('layouts.partials._sidebar')

        <div class="flex-1 lg:ml-[260px]">
            @include('layouts.partials._topbar')

            <main id="kandungan-utama" class="p-4 lg:p-6" tabindex="-1">
                @include('layouts.partials._alerts')
                @yield('content')
            </main>

            @include('layouts.partials._footer')
        </div>
    </div>

    @include('layouts.partials._scripts')
</body>
</html>
