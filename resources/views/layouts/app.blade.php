<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="iBook 2.0 — Sistem Tempahan Bilik Mesyuarat">
    <title>@yield('title', 'iBook 2.0') — {{ $tetapan['nama_jabatan'] ?? 'Sistem Tempahan Bilik Mesyuarat' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    @stack('styles')
    <style>
        /* ── Pembolehubah CSS ───────────────────────────────────── */
        :root {
            --sidebar-bg: #1a1a2e;
            --accent: #f59e0b;
            --accent-dark: #d97706;
            --focus-ring: 0 0 0 3px rgba(245,158,11,.5);
        }

        /* ── Tipografi & Asas ───────────────────────────────────── */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f6; color: #1f2937; }

        /* ── Skip Navigation (WCAG 2.4.1) ──────────────────────── */
        .skip-link {
            position: absolute;
            top: -100px;
            left: 8px;
            z-index: 9999;
            background: var(--accent);
            color: #1a1a2e;
            font-weight: 700;
            padding: 10px 18px;
            border-radius: 0 0 8px 8px;
            text-decoration: none;
            transition: top .15s;
        }
        .skip-link:focus { top: 0; outline: 3px solid #1a1a2e; outline-offset: 2px; }

        /* ── Focus Visible (WCAG 2.4.7) ────────────────────────── */
        *:focus-visible {
            outline: 3px solid var(--accent);
            outline-offset: 2px;
        }
        a:focus-visible, button:focus-visible, input:focus-visible,
        select:focus-visible, textarea:focus-visible {
            outline: 3px solid var(--accent);
            outline-offset: 2px;
            border-radius: 4px;
        }

        /* ── Sidebar ────────────────────────────────────────────── */
        .sidebar { background: var(--sidebar-bg); min-height: 100vh; width: 260px; flex-shrink: 0; }
        .sidebar-link {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 20px; color: #cbd5e1; border-radius: 8px;
            margin: 2px 8px; transition: all .2s;
            text-decoration: none; font-size: 14px;
        }
        .sidebar-link:hover { background: rgba(245,158,11,.15); color: var(--accent); }
        .sidebar-link[aria-current="page"] {
            background: rgba(245,158,11,.15);
            color: var(--accent);
            border-right: 3px solid var(--accent);
            font-weight: 600;
        }
        .sidebar-link:focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: -2px;
        }

        /* ── Komponen Umum ─────────────────────────────────────── */
        .badge-menunggu { background:#fef3c7; color:#78350f; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600; }
        .badge-lulus    { background:#d1fae5; color:#064e3b; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600; }
        .badge-tolak    { background:#fee2e2; color:#7f1d1d; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600; }

        /* Butang — contrast ratio 4.5:1+ (WCAG 1.4.3) */
        .btn-primary {
            background: var(--accent); color: #1a1a2e;
            padding: 8px 20px; border-radius: 8px; font-weight: 700;
            border: 2px solid var(--accent); cursor: pointer;
            display: inline-flex; align-items: center; gap: 6px;
            text-decoration: none; transition: background .2s, color .2s;
        }
        .btn-primary:hover, .btn-primary:focus-visible {
            background: var(--accent-dark); border-color: var(--accent-dark);
        }
        .btn-danger {
            background: #dc2626; color: #fff;
            padding: 6px 14px; border-radius: 6px; font-weight: 600;
            border: 2px solid #dc2626; cursor: pointer; font-size: 13px;
        }
        .btn-danger:hover { background: #b91c1c; border-color: #b91c1c; }
        .btn-success {
            background: #15803d; color: #fff;
            padding: 6px 14px; border-radius: 6px; font-weight: 600;
            border: 2px solid #15803d; cursor: pointer; font-size: 13px;
        }
        .btn-success:hover { background: #166534; border-color: #166534; }
        .btn-secondary {
            background: #e5e7eb; color: #1f2937;
            padding: 8px 20px; border-radius: 8px; font-weight: 600;
            border: 2px solid #d1d5db; cursor: pointer;
            display: inline-flex; align-items: center; gap: 6px;
            text-decoration: none;
        }
        .btn-secondary:hover { background: #d1d5db; }

        /* ── Borang ─────────────────────────────────────────────── */
        .stat-card { background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,.1); }
        .form-input {
            width: 100%; border: 2px solid #d1d5db; border-radius: 8px;
            padding: 10px 14px; font-size: 14px; outline: none; transition: border .2s;
            background: #fff; color: #1f2937;
        }
        .form-input:focus { border-color: var(--accent); box-shadow: var(--focus-ring); }
        .form-input[aria-invalid="true"] { border-color: #dc2626; }
        .form-label { font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 6px; display: block; }
        .form-hint { font-size: 12px; color: #6b7280; margin-top: 3px; }
        .form-error { font-size: 12px; color: #dc2626; margin-top: 4px; display: flex; align-items: center; gap: 4px; }

        /* ── Bar kemajuan ────────────────────────────────────────── */
        .progress-bar { height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden; }
        .progress-fill { height: 100%; background: var(--accent); border-radius: 4px; transition: width .5s; }

        /* ── Alert ──────────────────────────────────────────────── */
        .alert-success {
            background: #d1fae5; border: 1px solid #6ee7b7; color: #064e3b;
            padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;
        }
        .alert-error {
            background: #fee2e2; border: 1px solid #fca5a5; color: #7f1d1d;
            padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;
        }

        /* ── Jadual ─────────────────────────────────────────────── */
        .table-header { background: #f9fafb; }
        .table th {
            padding: 12px 16px; text-align: left;
            font-size: 13px; font-weight: 600; color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        .table td { padding: 14px 16px; font-size: 14px; color: #374151; border-bottom: 1px solid #f3f4f6; }
        .table tr:hover td { background: #fafafa; }
        .table caption { font-size: 13px; color: #6b7280; padding: 8px 16px; text-align: left; }

        /* ── Badge notifikasi ───────────────────────────────────── */
        .notification-badge {
            background: #ef4444; color: #fff; border-radius: 50%;
            width: 18px; height: 18px; font-size: 10px;
            display: flex; align-items: center; justify-content: center;
            position: absolute; top: -4px; right: -4px;
            border: 2px solid #fff;
        }

        /* ── Mode tinggi kontras (forced-colors) ────────────────── */
        @media (forced-colors: active) {
            .btn-primary, .btn-danger, .btn-success, .btn-secondary { border: 2px solid ButtonText; }
            .sidebar-link[aria-current="page"] { border: 2px solid Highlight; }
        }

        /* ── Animasi hanya jika pengguna benarkan ───────────────── */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>

    {{-- ── Skip Navigation (WCAG 2.4.1) ──────────────────────────── --}}
    <a href="#kandungan-utama" class="skip-link">Langkau ke kandungan utama</a>
    <a href="#nav-utama" class="skip-link" style="left:220px">Langkau ke navigasi</a>

<div class="flex">

    {{-- ── Sidebar / Navigasi Utama ──────────────────────────────── --}}
    <aside class="sidebar fixed top-0 left-0 z-30" aria-label="Bar sisi navigasi">

        {{-- Logo --}}
        <div class="p-5 border-b border-slate-700">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3" aria-label="iBook 2.0 — Halaman Utama">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:var(--accent)" aria-hidden="true">
                    <i class="fa-solid fa-book-open text-white text-sm"></i>
                </div>
                <span class="text-white font-bold text-lg">iBook <span style="color:var(--accent)">2.0</span></span>
            </a>
        </div>

        {{-- Nav menu --}}
        <nav id="nav-utama" aria-label="Menu utama">
            <ul role="list" class="py-4 space-y-0.5">
                <li>
                    <a href="{{ route('dashboard') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('dashboard') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-table-columns w-5" aria-hidden="true"></i>
                        <span>Papan Pemuka</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('kalendar') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('kalendar*') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-calendar-days w-5" aria-hidden="true"></i>
                        <span>Kalendar</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('tempahan.create') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('tempahan.create') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-circle-plus w-5" aria-hidden="true"></i>
                        <span>Tempahan Baru</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('tempahan.index') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('tempahan.index') || request()->routeIs('tempahan.show') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-list w-5" aria-hidden="true"></i>
                        <span>Senarai Tempahan</span>
                    </a>
                </li>

                @if(auth()->user()->bolehLuluskan())
                @php $pending = \App\Models\Tempahan::where('status','menunggu')->count(); @endphp
                <li>
                    <a href="{{ route('kelulusan') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('kelulusan*') ? 'aria-current=page' : '' }}
                       aria-describedby="{{ $pending > 0 ? 'badge-kelulusan' : '' }}">
                        <i class="fa-solid fa-circle-check w-5" aria-hidden="true"></i>
                        <span>Kelulusan</span>
                        @if($pending > 0)
                        <span id="badge-kelulusan"
                              class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-0.5"
                              aria-label="{{ $pending }} permohonan menunggu kelulusan">{{ $pending }}</span>
                        @endif
                    </a>
                </li>
                @endif

                <li>
                    <a href="{{ route('laporan') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('laporan*') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-chart-bar w-5" aria-hidden="true"></i>
                        <span>Laporan</span>
                    </a>
                </li>

                @if(auth()->user()->isPentadbir())
                <li role="separator" aria-hidden="true">
                    <p class="px-8 py-2 text-xs text-slate-500 uppercase tracking-wider mt-2">Pentadbiran</p>
                </li>
                <li>
                    <a href="{{ route('bilik.index') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('bilik*') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-door-open w-5" aria-hidden="true"></i>
                        <span>Bilik Mesyuarat</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('pengguna.index') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('pengguna*') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-users w-5" aria-hidden="true"></i>
                        <span>Pengguna</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('tetapan.index') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('tetapan*') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-gear w-5" aria-hidden="true"></i>
                        <span>Tetapan</span>
                    </a>
                </li>
                @endif
            </ul>
        </nav>
    </aside>

    {{-- ── Kawasan Kandungan Utama ─────────────────────────────── --}}
    <div class="flex-1 ml-[260px]">

        {{-- Top bar --}}
        <header class="bg-white shadow-sm sticky top-0 z-20 px-6 py-3 flex items-center justify-between" role="banner">

            {{-- Carian --}}
            <div class="relative" role="search">
                <label for="carian-global" class="sr-only">Cari tempahan, bilik atau pengguna</label>
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" aria-hidden="true"></i>
                <input type="search" id="carian-global"
                    placeholder="Cari tempahan, bilik atau pengguna..."
                    class="pl-10 pr-4 py-2 bg-gray-100 rounded-lg text-sm w-80 focus:outline-none focus:bg-white focus:ring-2 focus:ring-amber-400"
                    aria-label="Carian sistem">
            </div>

            {{-- Profil & tindakan --}}
            <div class="flex items-center gap-4">

                {{-- Notifikasi --}}
                @php $notif = \App\Models\Tempahan::where('status','menunggu')->count(); @endphp
                <div class="relative">
                    <a href="{{ auth()->user()->bolehLuluskan() ? route('kelulusan') : route('dashboard') }}"
                       class="text-gray-500 hover:text-gray-700 p-1 rounded"
                       aria-label="Notifikasi{{ $notif > 0 ? ' — ' . $notif . ' permohonan menunggu' : '' }}">
                        <i class="fa-solid fa-bell text-lg" aria-hidden="true"></i>
                        @if($notif > 0)
                        <span class="notification-badge" aria-hidden="true">{{ $notif }}</span>
                        @endif
                    </a>
                </div>

                {{-- Maklumat pengguna --}}
                <div class="flex items-center gap-3">
                    <div class="text-right" aria-hidden="true">
                        <div class="font-semibold text-sm text-gray-800">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-gray-500">{{ auth()->user()->label_peranan }}</div>
                    </div>
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold"
                         style="background:var(--accent)"
                         aria-hidden="true"
                         title="{{ auth()->user()->name }}">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>

                    {{-- Log keluar --}}
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="text-gray-400 hover:text-red-500 ml-2 p-1 rounded"
                                aria-label="Log keluar daripada akaun {{ auth()->user()->name }}">
                            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- Kandungan utama --}}
        <main id="kandungan-utama" class="p-6" tabindex="-1">

            {{-- Alert mesej --}}
            @if(session('success'))
            <div role="alert" aria-live="polite" class="alert-success flex items-center gap-2">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif
            @if(session('error'))
            <div role="alert" aria-live="assertive" class="alert-error flex items-center gap-2">
                <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
