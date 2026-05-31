{{-- ── Sidebar / Navigasi Utama ──────────────────────────────────── --}}
<aside id="sidebar-utama" class="sidebar fixed top-0 left-0 z-30" aria-label="Bar sisi navigasi">

    {{-- Strip jabatan di bahagian atas sidebar --}}
    @if($namaJabatan || $logoJabatan)
    <div class="px-4 py-3 border-b border-slate-700/60" style="background:rgba(245,158,11,0.06)">
        <div class="flex items-center gap-3">
            @if($logoJabatan)
            <img src="{{ $logoJabatan }}" alt="Logo {{ $namaJabatan }}"
                 class="object-contain flex-shrink-0" style="height:48px; width:auto">
            @else
            <div class="rounded flex items-center justify-center flex-shrink-0"
                 style="width:48px;height:48px;background:rgba(245,158,11,0.15)">
                <i class="fa-solid fa-landmark text-amber-400 text-lg" aria-hidden="true"></i>
            </div>
            @endif
            <span class="text-slate-300 leading-tight font-medium" style="font-size:11px; line-height:1.3">
                {{ $namaJabatan ?: 'Bahagian Pengurusan Teknologi Maklumat' }}
            </span>
        </div>
    </div>
    @endif

    {{-- Nama sistem --}}
    <div class="p-5 border-b border-slate-700">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3"
           aria-label="{{ $namaSistem }} — Halaman Utama">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--accent)" aria-hidden="true">
                <i class="fa-solid fa-book-open text-white text-sm"></i>
            </div>
            <div>
                <span class="text-white font-bold block" style="font-size:14px; letter-spacing:-0.01em">{{ $namaSistem }}</span>
                <span class="text-slate-400 block" style="font-size:10px; margin-top:1px">Sistem Tempahan Bilik Mesyuarat</span>
            </div>
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
                <a href="{{ route('bahagian.index') }}"
                   class="sidebar-link"
                   {{ request()->routeIs('bahagian*') ? 'aria-current=page' : '' }}>
                    <i class="fa-solid fa-building-columns w-5" aria-hidden="true"></i>
                    <span>Bahagian</span>
                </a>
            </li>
            <li>
                <a href="{{ route('audit.index') }}"
                   class="sidebar-link"
                   {{ request()->routeIs('audit*') ? 'aria-current=page' : '' }}>
                    <i class="fa-solid fa-shield-halved w-5" aria-hidden="true"></i>
                    <span>Log Audit</span>
                </a>
            </li>
            <li>
                <a href="{{ route('sesi-aktif.index') }}"
                   class="sidebar-link"
                   {{ request()->routeIs('sesi-aktif*') ? 'aria-current=page' : '' }}>
                    <i class="fa-solid fa-users-rectangle w-5" aria-hidden="true"></i>
                    <span>Sesi Aktif</span>
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
            <li>
                <a href="{{ route('backup.index') }}"
                   class="sidebar-link"
                   {{ request()->routeIs('backup*') ? 'aria-current=page' : '' }}>
                    <i class="fa-solid fa-database w-5" aria-hidden="true"></i>
                    <span>Backup</span>
                    @php
                        try {
                            $backupSvc = app(\App\Services\BackupService::class);
                            if ($backupSvc->adaBackupTertunggak()):
                    @endphp
                    <span class="ml-auto w-2 h-2 rounded-full bg-red-500 animate-pulse flex-shrink-0" aria-label="Backup tertunggak" title="Backup tertunggak"></span>
                    @php endif; } catch (\Throwable $e) {} @endphp
                </a>
            </li>
            @endif
            @endif
        </ul>
    </nav>
</aside>
