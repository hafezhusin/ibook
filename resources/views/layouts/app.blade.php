<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'iBook 2.0') - {{ $tetapan['nama_jabatan'] ?? 'Sistem Tempahan Bilik' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    @stack('styles')
    <style>
        :root { --sidebar-bg: #1a1a2e; --accent: #f59e0b; }
        body { font-family: 'Segoe UI', sans-serif; background: #f3f4f6; }
        .sidebar { background: var(--sidebar-bg); min-height: 100vh; width: 260px; flex-shrink: 0; }
        .sidebar-link { display:flex; align-items:center; gap:10px; padding:10px 20px; color:#cbd5e1; border-radius:8px; margin:2px 8px; transition:all .2s; text-decoration:none; font-size:14px; }
        .sidebar-link:hover, .sidebar-link.active { background:rgba(245,158,11,.15); color:#f59e0b; }
        .sidebar-link.active { border-right:3px solid #f59e0b; }
        .badge-menunggu { background:#fef3c7; color:#92400e; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600; }
        .badge-lulus { background:#d1fae5; color:#065f46; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600; }
        .badge-tolak { background:#fee2e2; color:#991b1b; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600; }
        .btn-primary { background:#f59e0b; color:#fff; padding:8px 20px; border-radius:8px; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px; }
        .btn-primary:hover { background:#d97706; }
        .btn-danger { background:#ef4444; color:#fff; padding:6px 14px; border-radius:6px; font-weight:600; border:none; cursor:pointer; font-size:13px; }
        .btn-success { background:#10b981; color:#fff; padding:6px 14px; border-radius:6px; font-weight:600; border:none; cursor:pointer; font-size:13px; }
        .btn-secondary { background:#e5e7eb; color:#374151; padding:8px 20px; border-radius:8px; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px; }
        .stat-card { background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,.1); }
        .form-input { width:100%; border:1.5px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:14px; outline:none; transition:border .2s; }
        .form-input:focus { border-color:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.15); }
        .form-label { font-size:14px; font-weight:600; color:#374151; margin-bottom:6px; display:block; }
        .progress-bar { height:8px; background:#e5e7eb; border-radius:4px; overflow:hidden; }
        .progress-fill { height:100%; background:#f59e0b; border-radius:4px; transition:width .5s; }
        .alert-success { background:#d1fae5; border:1px solid #6ee7b7; color:#065f46; padding:12px 16px; border-radius:8px; margin-bottom:16px; }
        .alert-error { background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; padding:12px 16px; border-radius:8px; margin-bottom:16px; }
        .table-header { background:#f9fafb; }
        .table th { padding:12px 16px; text-align:left; font-size:13px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb; }
        .table td { padding:14px 16px; font-size:14px; color:#374151; border-bottom:1px solid #f3f4f6; }
        .table tr:hover td { background:#fafafa; }
        .notification-badge { background:#ef4444; color:#fff; border-radius:50%; width:18px; height:18px; font-size:10px; display:flex; align-items:center; justify-content:center; position:absolute; top:-4px; right:-4px; }
    </style>
</head>
<body>
<div class="flex">
    {{-- Sidebar --}}
    <aside class="sidebar fixed top-0 left-0 z-30">
        <div class="p-5 border-b border-slate-700">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:#f59e0b">
                    <i class="fa-solid fa-book-open text-white text-sm"></i>
                </div>
                <div>
                    <span class="text-white font-bold text-lg">iBook</span>
                    <span style="color:#f59e0b" class="font-bold text-lg"> 2.0</span>
                </div>
            </div>
        </div>

        <nav class="py-4">
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-table-columns w-5"></i> Papan Pemuka
            </a>
            <a href="{{ route('kalendar') }}" class="sidebar-link {{ request()->routeIs('kalendar*') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-days w-5"></i> Kalendar
            </a>
            <a href="{{ route('tempahan.create') }}" class="sidebar-link {{ request()->routeIs('tempahan.create') ? 'active' : '' }}">
                <i class="fa-solid fa-circle-plus w-5"></i> Tempahan Baru
            </a>
            <a href="{{ route('tempahan.index') }}" class="sidebar-link {{ request()->routeIs('tempahan.index') || request()->routeIs('tempahan.show') ? 'active' : '' }}">
                <i class="fa-solid fa-list w-5"></i> Senarai Tempahan
            </a>

            @if(auth()->user()->bolehLuluskan())
            <a href="{{ route('kelulusan') }}" class="sidebar-link {{ request()->routeIs('kelulusan*') ? 'active' : '' }}">
                <i class="fa-solid fa-circle-check w-5"></i> Kelulusan
                @php $pending = \App\Models\Tempahan::where('status','menunggu')->count(); @endphp
                @if($pending > 0)
                <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-0.5">{{ $pending }}</span>
                @endif
            </a>
            @endif

            <a href="{{ route('laporan') }}" class="sidebar-link {{ request()->routeIs('laporan*') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-bar w-5"></i> Laporan
            </a>

            @if(auth()->user()->isPentadbir())
            <div class="px-8 py-2 text-xs text-slate-500 uppercase tracking-wider mt-2">Pentadbiran</div>
            <a href="{{ route('bilik.index') }}" class="sidebar-link {{ request()->routeIs('bilik*') ? 'active' : '' }}">
                <i class="fa-solid fa-door-open w-5"></i> Bilik Mesyuarat
            </a>
            <a href="{{ route('pengguna.index') }}" class="sidebar-link {{ request()->routeIs('pengguna*') ? 'active' : '' }}">
                <i class="fa-solid fa-users w-5"></i> Pengguna
            </a>
            <a href="{{ route('tetapan.index') }}" class="sidebar-link {{ request()->routeIs('tetapan*') ? 'active' : '' }}">
                <i class="fa-solid fa-gear w-5"></i> Tetapan
            </a>
            @endif
        </nav>
    </aside>

    {{-- Main Content --}}
    <div class="flex-1 ml-[260px]">
        {{-- Top bar --}}
        <header class="bg-white shadow-sm sticky top-0 z-20 px-6 py-3 flex items-center justify-between">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" placeholder="Cari tempahan, bilik, atau pengguna..."
                    class="pl-10 pr-4 py-2 bg-gray-100 rounded-lg text-sm w-80 focus:outline-none focus:bg-white focus:ring-2 focus:ring-amber-400">
            </div>
            <div class="flex items-center gap-4">
                <div class="relative cursor-pointer">
                    <i class="fa-solid fa-bell text-gray-500 text-lg"></i>
                    @php $notif = \App\Models\Tempahan::where('status','menunggu')->count(); @endphp
                    @if($notif > 0)
                    <span class="notification-badge">{{ $notif }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <div class="font-semibold text-sm text-gray-800">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-gray-500">{{ auth()->user()->label_peranan }}</div>
                    </div>
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold" style="background:#f59e0b">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-red-500 ml-2" title="Log Keluar">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="p-6">
            @if(session('success'))
            <div class="alert-success flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="alert-error flex items-center gap-2">
                <i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}
            </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
