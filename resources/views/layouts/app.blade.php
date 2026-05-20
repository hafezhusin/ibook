<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $tetapan['nama_sistem'] ?? 'iBook 2.0' }} — Sistem Tempahan Bilik Mesyuarat">
    <title>@yield('title', $tetapan['nama_sistem'] ?? 'iBook 2.0') — {{ $tetapan['nama_jabatan'] ?? 'Sistem Tempahan Bilik Mesyuarat' }}</title>
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

        /* ══════════════════════════════════════════════════════════
           DARK MODE — Auto ikut tetapan sistem pengguna
           ══════════════════════════════════════════════════════════ */
        @media (prefers-color-scheme: dark) {

            /* ── Asas ────────────────────────────────────────────── */
            body { background: #0f172a !important; color: #e2e8f0 !important; }

            /* ── Top header ──────────────────────────────────────── */
            header[role="banner"] {
                background: #1e293b !important;
                box-shadow: 0 1px 3px rgba(0,0,0,.4) !important;
            }
            header[role="banner"] input[type="search"] {
                background: #334155 !important;
                color: #f1f5f9 !important;
            }
            header[role="banner"] input[type="search"]::placeholder { color: #64748b !important; }

            /* ── Kad / Panel putih ───────────────────────────────── */
            .bg-white { background: #1e293b !important; }
            .bg-gray-50 { background: #0f172a !important; }
            .bg-gray-100 { background: #334155 !important; }
            .shadow-sm { box-shadow: 0 1px 4px rgba(0,0,0,.35) !important; }
            .rounded-xl.shadow-sm, .rounded-xl.shadow { box-shadow: 0 2px 8px rgba(0,0,0,.4) !important; }

            /* ── Teks ────────────────────────────────────────────── */
            .text-gray-800, .text-gray-900 { color: #f1f5f9 !important; }
            .text-gray-700 { color: #e2e8f0 !important; }
            .text-gray-600 { color: #cbd5e1 !important; }
            .text-gray-500 { color: #94a3b8 !important; }
            .text-gray-400 { color: #64748b !important; }
            .text-gray-300 { color: #475569 !important; }

            /* ── Border ──────────────────────────────────────────── */
            .border-gray-100 { border-color: #334155 !important; }
            .border-gray-200 { border-color: #475569 !important; }
            .border-b, .border-t { border-color: #334155 !important; }
            .divide-gray-50 > * + * { border-color: #334155 !important; }
            .divide-y > * + * { border-color: #334155 !important; }

            /* ── Borang ──────────────────────────────────────────── */
            .form-input {
                background: #0f172a !important;
                border-color: #475569 !important;
                color: #f1f5f9 !important;
            }
            .form-input:focus { border-color: #f59e0b !important; }
            .form-input::placeholder { color: #64748b !important; }
            .form-label { color: #e2e8f0 !important; }
            .form-hint { color: #94a3b8 !important; }
            .form-error { color: #f87171 !important; }
            select.form-input option { background: #1e293b; color: #f1f5f9; }

            /* ── Jadual ──────────────────────────────────────────── */
            .table-header { background: #0f172a !important; }
            .table th { color: #cbd5e1 !important; border-bottom-color: #334155 !important; }
            .table td { color: #e2e8f0 !important; border-bottom-color: #1e293b !important; }
            .table tr:hover td { background: #273447 !important; }

            /* ── Hover ───────────────────────────────────────────── */
            .hover\:bg-gray-50:hover { background: #273447 !important; }
            .hover\:bg-amber-50:hover { background: #1c1200 !important; }

            /* ── Butang ──────────────────────────────────────────── */
            .btn-secondary {
                background: #334155 !important;
                color: #e2e8f0 !important;
                border-color: #475569 !important;
            }
            .btn-secondary:hover { background: #475569 !important; }

            /* ── Stat cards ──────────────────────────────────────── */
            .stat-card { background: #1e293b !important; }
            .stat-card-v2 { background: #1e293b !important; }
            .stat-action { border-top-color: #334155 !important; }
            .stat-action:hover { background: #273447 !important; }

            /* ── Progress bar ────────────────────────────────────── */
            .progress-bar { background: #334155 !important; }

            /* ── Badge ───────────────────────────────────────────── */
            .badge-lulus    { background: #14532d !important; color: #86efac !important; }
            .badge-tolak    { background: #7f1d1d !important; color: #fca5a5 !important; }

            /* ── Alert ───────────────────────────────────────────── */
            .alert-success { background: #052e16 !important; border-color: #166534 !important; color: #86efac !important; }
            .alert-error   { background: #450a0a !important; border-color: #991b1b 100% !important; color: #fca5a5 !important; }

            /* ── Modal ───────────────────────────────────────────── */
            #event-modal > div { background: #1e293b !important; }
            #event-modal dl { background: #1e293b !important; }
            #event-modal .px-6.py-4 { background: #1e293b !important; }

            /* ── Sidebar (sudah gelap, tapi perbaiki hover) ──────── */
            .sidebar-link:hover { background: rgba(245,158,11,.2) !important; }

            /* ── Butang tapis bilik (kalendar sidebar) ───────────── */
            .bilik-btn {
                background: #1e293b !important;
                border-color: #334155 !important;
                color: #e2e8f0 !important;
            }
            .bilik-btn:hover {
                background: #1c1a00 !important;
                border-color: #f59e0b !important;
                color: #f59e0b !important;
            }
            .bilik-btn.aktif {
                background: #1c1a00 !important;
                border-color: #f59e0b !important;
                color: #f59e0b !important;
            }
            .bilik-btn .text-gray-800 { color: #f1f5f9 !important; }
            .bilik-btn .text-gray-400 { color: #94a3b8 !important; }

            /* ── Ketersediaan bilik cards ─────────────────────────── */
            .bilik-card { background: #1e293b !important; }
            .kemudahan-tag { background: #334155 !important; color: #e2e8f0 !important; }

            /* ── Flatpickr calendar ──────────────────────────────── */
            .flatpickr-calendar {
                background: #1e293b !important;
                box-shadow: 0 4px 20px rgba(0,0,0,.5) !important;
            }
            .flatpickr-day { color: #e2e8f0 !important; }
            .flatpickr-day:hover { background: #334155 !important; }
            .flatpickr-day.selected, .flatpickr-day.selected:hover {
                background: #f59e0b !important;
                border-color: #f59e0b !important;
                color: #1a1a2e !important;
            }
            .flatpickr-day.today { border-color: #f59e0b !important; }
            .flatpickr-day.disabled { color: #475569 !important; }
            .flatpickr-months .flatpickr-month,
            .flatpickr-weekdays,
            span.flatpickr-weekday {
                background: #0f172a !important;
                color: #94a3b8 !important;
                fill: #94a3b8 !important;
            }
            .flatpickr-current-month input,
            .flatpickr-current-month .numInputWrapper,
            .flatpickr-current-month span.arrowUp,
            .flatpickr-current-month span.arrowDown { color: #f1f5f9 !important; }
            .numInputWrapper:hover { background: #334155 !important; }
            .flatpickr-prev-month svg, .flatpickr-next-month svg { fill: #94a3b8 !important; }
            .flatpickr-prev-month:hover svg, .flatpickr-next-month:hover svg { fill: #f59e0b !important; }

            /* ── Footer ─────────────────────────────────────────── */
            footer[role="contentinfo"] {
                background: #0f172a !important;
                border-color: #1e293b !important;
                color: #475569 !important;
            }
            footer[role="contentinfo"] a:hover { color: #f59e0b !important; }

            /* ── FullCalendar ────────────────────────────────────── */
            .fc { color: #e2e8f0 !important; }
            .fc-scrollgrid { border-color: #334155 !important; }
            .fc-scrollgrid-sync-table td, .fc-scrollgrid-sync-table th { border-color: #334155 !important; }
            .fc-col-header-cell { background: #0f172a !important; }
            .fc-col-header-cell-cushion { color: #94a3b8 !important; }
            .fc-daygrid-day { background: #1e293b !important; }
            .fc-daygrid-day:hover { background: #273447 !important; }
            .fc-daygrid-day-number { color: #cbd5e1 !important; }
            .fc-day-today { background: #1a2a1a !important; }
            .fc-day-today .fc-daygrid-day-number { color: #f59e0b !important; font-weight: 800; }
            .fc-button { background: #334155 !important; border-color: #475569 !important; color: #e2e8f0 !important; }
            .fc-button:hover { background: #475569 !important; }
            .fc-button-primary:not(:disabled).fc-button-active { background: #f59e0b !important; border-color: #f59e0b !important; color: #1a1a2e !important; }
            .fc-toolbar-title { color: #f1f5f9 !important; }
            .fc-daygrid-more-link { color: #f59e0b !important; }
            .fc-popover { background: #1e293b !important; border-color: #334155 !important; }
            .fc-popover-header { background: #0f172a !important; color: #f1f5f9 !important; }
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
        @php $namaSistem = $tetapan['nama_sistem'] ?? ''; @endphp
        <div class="p-5 border-b border-slate-700">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3"
               aria-label="{{ $namaSistem ?: 'iBook 2.0' }} — Halaman Utama">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--accent)" aria-hidden="true">
                    <i class="fa-solid fa-book-open text-white text-sm"></i>
                </div>
                @if($namaSistem)
                <span class="text-white font-bold leading-tight" style="font-size:13px">{{ $namaSistem }}</span>
                @else
                <span class="text-white font-bold text-lg">iBook <span style="color:var(--accent)">2.0</span></span>
                @endif
            </a>
        </div>

        {{-- Nav menu --}}
        <nav id="nav-utama" aria-label="Menu utama">
            <ul role="list" class="py-4 space-y-0.5">

                {{-- ── Kumpulan: Operasi ────────────────────────── --}}
                <li role="separator" aria-hidden="true">
                    <p class="px-8 pb-1 pt-2 text-[10px] text-slate-500 uppercase tracking-widest font-semibold">Operasi</p>
                </li>
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
                <li>
                    <a href="{{ route('ketersediaan') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('ketersediaan*') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-magnifying-glass-location w-5" aria-hidden="true"></i>
                        <span>Semak Bilik Kosong</span>
                    </a>
                </li>

                {{-- ── Kumpulan: Analitik ───────────────────────── --}}
                <li role="separator" aria-hidden="true">
                    <p class="px-8 pb-1 pt-3 text-[10px] text-slate-500 uppercase tracking-widest font-semibold">Analitik</p>
                </li>
                <li>
                    <a href="{{ route('laporan') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('laporan*') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-chart-bar w-5" aria-hidden="true"></i>
                        <span>Laporan</span>
                    </a>
                </li>

                @if(auth()->user()->isPentadbir() || auth()->user()->isUrusSetia())
                {{-- ── Kumpulan: Pentadbiran ────────────────────── --}}
                <li role="separator" aria-hidden="true">
                    <p class="px-8 pb-1 pt-3 text-[10px] text-slate-500 uppercase tracking-widest font-semibold">Pentadbiran</p>
                </li>
                @if(auth()->user()->isPentadbir())
                <li>
                    <a href="{{ route('bilik.index') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('bilik*') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-door-open w-5" aria-hidden="true"></i>
                        <span>Bilik Mesyuarat</span>
                    </a>
                </li>
                @endif
                <li>
                    <a href="{{ route('pengguna.index') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('pengguna*') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-users w-5" aria-hidden="true"></i>
                        <span>Pengguna</span>
                    </a>
                </li>
                @if(auth()->user()->isPentadbir())
                <li>
                    <a href="{{ route('tetapan.index') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('tetapan*') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-gear w-5" aria-hidden="true"></i>
                        <span>Tetapan</span>
                    </a>
                </li>
                @endif
                @endif
            </ul>
        </nav>
    </aside>

    {{-- ── Kawasan Kandungan Utama ─────────────────────────────── --}}
    <div class="flex-1 ml-[260px]">

        {{-- Top bar --}}
        <header class="bg-white shadow-sm sticky top-0 z-20 px-6 py-3 flex items-center justify-between" role="banner">

            {{-- Carian Global --}}
            <form method="GET" action="{{ route('carian') }}" role="search" aria-label="Carian sistem merentas semua modul">
                <div class="relative">
                    <label for="carian-global" class="sr-only">Cari tempahan, bilik atau pengguna</label>
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm" aria-hidden="true"></i>
                    <input type="search" id="carian-global" name="q"
                        value="{{ request()->routeIs('carian') ? request('q') : '' }}"
                        placeholder="Cari semua modul…"
                        class="pl-9 pr-16 py-2 bg-gray-100 rounded-lg text-sm w-72 focus:outline-none focus:bg-white focus:ring-2 focus:ring-amber-400 transition-all"
                        aria-label="Carian global — tekan / untuk fokus"
                        autocomplete="off">
                    {{-- Hint pintasan papan kekunci --}}
                    <kbd id="search-hint"
                         class="absolute right-3 top-1/2 -translate-y-1/2 hidden sm:flex items-center gap-0.5 text-[10px] text-gray-400 font-mono border border-gray-300 rounded px-1.5 py-0.5 pointer-events-none select-none"
                         aria-hidden="true"
                         title="Tekan / untuk fokus ke carian">
                        /
                    </kbd>
                </div>
            </form>

            {{-- Profil & tindakan --}}
            <div class="flex items-center gap-4">


                {{-- Maklumat pengguna + dropdown --}}
                <div class="relative flex items-center gap-3" id="profil-dropdown-wrap">
                    <div class="text-right" aria-hidden="true">
                        <div class="font-semibold text-sm text-gray-800">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-gray-500">{{ auth()->user()->label_peranan }}</div>
                    </div>

                    {{-- Avatar — klik untuk dropdown --}}
                    <button type="button" id="profil-btn"
                            onclick="toggleProfilMenu()"
                            class="w-9 h-9 rounded-full flex items-center justify-center font-bold focus:outline-none focus:ring-2 focus:ring-amber-400"
                            style="background:var(--accent); color:#1a1a2e;"
                            aria-haspopup="menu" aria-expanded="false"
                            aria-label="Menu profil {{ auth()->user()->name }}">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </button>

                    {{-- Dropdown menu --}}
                    <div id="profil-menu"
                         class="hidden absolute right-0 top-12 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
                         role="menu" aria-labelledby="profil-btn">
                        <a href="{{ route('profil') }}"
                           class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition-colors"
                           role="menuitem">
                            <i class="fa-solid fa-user-pen w-4" aria-hidden="true"></i> Profil Saya
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors"
                                    role="menuitem"
                                    aria-label="Log keluar daripada akaun {{ auth()->user()->name }}">
                                <i class="fa-solid fa-right-from-bracket w-4" aria-hidden="true"></i> Log Keluar
                            </button>
                        </form>
                    </div>
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

        {{-- Footer --}}
        @php
            $namaBahagian  = $tetapan['nama_jabatan'] ?? '';
            $emelPentadbir = $tetapan['emel_pentadbir'] ?? '';
            $tahunSemasa   = date('Y');
        @endphp
        @if($namaBahagian || $emelPentadbir)
        <footer class="border-t border-gray-200 px-6 py-4 text-center"
                style="background:#f9fafb"
                role="contentinfo">
            <div class="flex flex-col sm:flex-row items-center justify-center gap-2 text-xs text-gray-400">
                @if($namaBahagian)
                <span>
                    Hak Cipta &copy; {{ $namaBahagian }} {{ $tahunSemasa }}
                </span>
                @endif
                @if($namaBahagian && $emelPentadbir)
                <span class="hidden sm:inline text-gray-300" aria-hidden="true">|</span>
                @endif
                @if($emelPentadbir)
                <span>
                    <i class="fa-solid fa-envelope text-gray-300 mr-1" aria-hidden="true"></i>
                    <a href="mailto:{{ $emelPentadbir }}"
                       class="hover:text-amber-500 transition-colors"
                       aria-label="Hubungi pentadbir sistem">{{ $emelPentadbir }}</a>
                </span>
                @endif
            </div>
        </footer>
        @endif
    </div>
</div>

@stack('scripts')
<script>
// ── Pintasan papan kekunci: tekan "/" untuk fokus carian global ──
(function () {
    const input = document.getElementById('carian-global');
    const hint  = document.getElementById('search-hint');
    if (!input) return;

    // Sembunyikan hint bila input aktif
    input.addEventListener('focus', () => hint && (hint.style.display = 'none'));
    input.addEventListener('blur',  () => hint && (hint.style.display = ''));

    document.addEventListener('keydown', function (e) {
        // Tekan "/" bila tiada input aktif
        if (e.key === '/' && document.activeElement.tagName !== 'INPUT'
                          && document.activeElement.tagName !== 'TEXTAREA'
                          && document.activeElement.tagName !== 'SELECT') {
            e.preventDefault();
            input.focus();
            input.select();
        }
        // Tekan Esc untuk batalkan fokus
        if (e.key === 'Escape' && document.activeElement === input) {
            input.blur();
        }
    });
})();

// ── Dropdown profil ──────────────────────────────────────
function toggleProfilMenu() {
    const menu = document.getElementById('profil-menu');
    const btn  = document.getElementById('profil-btn');
    const open = menu.classList.toggle('hidden');
    btn.setAttribute('aria-expanded', !open);
}
// Tutup bila klik luar
document.addEventListener('click', function(e) {
    const wrap = document.getElementById('profil-dropdown-wrap');
    if (wrap && !wrap.contains(e.target)) {
        document.getElementById('profil-menu')?.classList.add('hidden');
        document.getElementById('profil-btn')?.setAttribute('aria-expanded', 'false');
    }
});
</script>
</body>
</html>
