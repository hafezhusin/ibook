@extends('layouts.app')

@section('title', 'Papan Pemuka')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ms.js"></script>
<style>
    .stat-card-v2 {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 1px 4px rgba(0,0,0,.08);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: box-shadow .2s, transform .15s;
        text-decoration: none;
        color: inherit;
    }
    .stat-card-v2:hover { box-shadow: 0 4px 16px rgba(0,0,0,.13); transform: translateY(-2px); }
    .stat-accent-bar { height: 4px; width: 100%; }
    .stat-body { padding: 20px 20px 14px; flex: 1; }
    .stat-icon-wrap {
        width: 48px; height: 48px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; flex-shrink: 0;
    }
    .stat-number { font-size: 2.6rem; font-weight: 800; line-height: 1; margin: 10px 0 3px; }
    .stat-label  { font-size: 13px; color: #6b7280; font-weight: 500; }
    .stat-sub    { font-size: 11px; color: #9ca3af; margin-top: 2px; }
    .stat-action {
        display: flex; align-items: center; justify-content: center; gap: 6px;
        padding: 10px 20px;
        font-size: 13px; font-weight: 700;
        border-top: 1px solid #f3f4f6;
        text-decoration: none; color: inherit;
        transition: background .15s;
    }
    .stat-action:hover { background: #f9fafb; }
    .trend-badge {
        display: inline-flex; align-items: center; gap: 3px;
        font-size: 11px; font-weight: 700;
        padding: 2px 7px; border-radius: 20px;
    }

    /* Widget Semak Pantas */
    .quick-check-panel {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        border-radius: 16px;
        padding: 24px 28px;
        margin-bottom: 24px;
    }
</style>
@endpush

@section('content')
@php
    $namaBulan = ['','Januari','Februari','Mac','April','Mei','Jun','Julai','Ogos','September','Oktober','November','Disember'];
@endphp

{{-- ══ Intelligent Hero Banner ════════════════════════════════════ --}}
@php
    $namaFirst = Str::before(auth()->user()->name, ' ') ?: auth()->user()->name;
    $bannerState = $mesyuaratSeterusnya
        ? ($mesyuaratSeterusnya->tarikh->isToday() ? 'today' : 'upcoming')
        : 'empty';
    $tarikhLabel = match(true) {
        $mesyuaratSeterusnya?->tarikh->isToday()    => 'Hari Ini',
        $mesyuaratSeterusnya?->tarikh->isTomorrow() => 'Esok',
        default => $mesyuaratSeterusnya?->tarikh->isoFormat('D MMM') ?? '',
    };
@endphp
<div class="rounded-2xl mb-6 overflow-hidden"
     style="background:linear-gradient(135deg,#1a1a2e 0%,#16213e 60%,#0f3460 100%)">
    <div class="px-6 py-5 flex flex-col md:flex-row md:items-center gap-5">

        {{-- Kiri: Greeting + mesyuarat seterusnya --}}
        <div class="flex-1 min-w-0">
            <p class="text-slate-500 text-xs font-medium mb-1">
                {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}
            </p>
            <h1 class="text-white font-bold text-xl mb-3" style="letter-spacing:-0.02em">
                Selamat Datang, {{ $namaFirst }}
            </h1>

            {{-- State: ada mesyuarat --}}
            @if($mesyuaratSeterusnya)
            <div class="flex items-start gap-3 p-3 rounded-xl"
                 style="background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.2)">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
                     style="background:rgba(245,158,11,0.18)">
                    <i class="fa-solid fa-calendar-check text-amber-400" aria-hidden="true"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-amber-400 text-xs font-bold uppercase tracking-wider mb-0.5">
                        Mesyuarat Seterusnya &bull; {{ $tarikhLabel }}
                    </p>
                    <p class="text-white font-semibold text-sm truncate">
                        {{ $mesyuaratSeterusnya->nama_mesyuarat }}
                    </p>
                    <p class="text-slate-400 text-xs mt-0.5">
                        <i class="fa-solid fa-door-open mr-1 text-amber-500/60" aria-hidden="true"></i>
                        {{ $mesyuaratSeterusnya->bilik->nama ?? '—' }}
                        &middot; {{ $mesyuaratSeterusnya->masa_label }}
                    </p>
                </div>
                <a href="{{ $mesyuaratSeterusnya->ulid ? route('tempahan.show', $mesyuaratSeterusnya) : '#' }}"
                   class="text-amber-400 hover:text-amber-300 text-xs font-semibold flex-shrink-0 flex items-center gap-1 mt-0.5 transition-colors">
                    Lihat <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
                </a>
            </div>

            {{-- State: tiada mesyuarat --}}
            @else
            <div class="flex items-center gap-3 p-3 rounded-xl"
                 style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08)">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
                     style="background:rgba(255,255,255,0.07)">
                    <i class="fa-solid fa-calendar-plus text-slate-400" aria-hidden="true"></i>
                </div>
                <div>
                    <p class="text-slate-300 text-sm font-medium">Tiada mesyuarat akan datang</p>
                    <p class="text-slate-500 text-xs">Jadualkan mesyuarat anda sekarang</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Kanan: Availability esok + CTA --}}
        <div class="flex flex-col gap-3 md:items-end flex-shrink-0">
            <div class="flex flex-wrap gap-2">
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold"
                     style="background:rgba(255,255,255,0.07);color:#94a3b8">
                    <i class="fa-solid fa-sun text-amber-400 text-xs" aria-hidden="true"></i>
                    Esok Pagi:
                    <span class="ml-1 font-bold {{ $bilikKosongEsokPagi > 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ $bilikKosongEsokPagi }} kosong
                    </span>
                </div>
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold"
                     style="background:rgba(255,255,255,0.07);color:#94a3b8">
                    <i class="fa-solid fa-moon text-blue-400 text-xs" aria-hidden="true"></i>
                    Esok Petang:
                    <span class="ml-1 font-bold {{ $bilikKosongEsokPetang > 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ $bilikKosongEsokPetang }} kosong
                    </span>
                </div>
            </div>
            <a href="{{ route('tempahan.create') }}" class="btn-primary whitespace-nowrap">
                <i class="fa-solid fa-plus" aria-hidden="true"></i> Tempahan Baru
            </a>
        </div>

    </div>
</div>

{{-- ══ Kad Statistik ══════════════════════════════════════════════ --}}
<section aria-labelledby="heading-statistik" class="mb-6">
    <h2 id="heading-statistik" class="sr-only">Statistik</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

        {{-- Kad 1 — Jumlah Tempahan Bulan Ini --}}
        @php
            $trendColor = $trendNaik ? '#15803d' : '#b91c1c';
            $trendBg    = $trendNaik ? '#dcfce7' : '#fee2e2';
            $trendIcon  = $trendNaik ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';
        @endphp
        <a href="{{ route('tempahan.index') }}" class="stat-card-v2" aria-label="Jumlah Tempahan Bulan Ini: {{ $jumlahTempahan }}. Klik untuk lihat semua.">
            <div class="stat-accent-bar" style="background:#f59e0b"></div>
            <div class="stat-body">
                <div class="flex items-center justify-between">
                    <div class="stat-icon-wrap" style="background:#fef3c7">
                        <i class="fa-solid fa-calendar-check" style="color:#d97706" aria-hidden="true"></i>
                    </div>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full" style="background:#fef3c7;color:#92400e">
                        {{ $namaBulan[$bulanIni] }}
                    </span>
                </div>
                <div class="stat-number" style="color:#d97706">{{ $jumlahTempahan }}</div>
                <div class="stat-label">Jumlah Tempahan</div>
                <div class="flex items-center gap-2 mt-1">
                    <span class="stat-sub">Bulan {{ $namaBulan[$bulanIni] }}</span>
                    @if($jumlahTempahanLepas > 0 || $jumlahTempahan > 0)
                    <span class="trend-badge" style="background:{{ $trendBg }};color:{{ $trendColor }}">
                        <i class="fa-solid {{ $trendIcon }}" aria-hidden="true"></i>
                        {{ $trend >= 0 ? '+' : '' }}{{ $trend }}% vs {{ $namaBulan[$bulanLepas] }}
                    </span>
                    @endif
                </div>
            </div>
            <div class="stat-action" style="color:#d97706">
                <i class="fa-solid fa-list text-xs" aria-hidden="true"></i> Lihat Semua Tempahan
            </div>
        </a>

        {{-- Kad 2 — Bilik Tersedia Hari Ini --}}
        @php $adaBilikTersedia = $jumlahBilikTersedia > 0; @endphp
        <a href="{{ route('ketersediaan') }}?tarikh={{ today()->format('Y-m-d') }}&sesi=semua&peserta=1"
           class="stat-card-v2"
           aria-label="Bilik Tersedia Hari Ini: {{ $jumlahBilikTersedia }} daripada {{ $jumlahBilikAktif }}. Klik untuk semak.">
            <div class="stat-accent-bar" style="background:{{ $adaBilikTersedia ? '#16a34a' : '#dc2626' }}"></div>
            <div class="stat-body">
                <div class="flex items-center justify-between">
                    <div class="stat-icon-wrap" style="background:{{ $adaBilikTersedia ? '#dcfce7' : '#fee2e2' }}">
                        <i class="fa-solid fa-door-open" style="color:{{ $adaBilikTersedia ? '#16a34a' : '#dc2626' }}" aria-hidden="true"></i>
                    </div>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full"
                          style="background:{{ $adaBilikTersedia ? '#dcfce7' : '#fee2e2' }};color:{{ $adaBilikTersedia ? '#166534' : '#991b1b' }}">
                        {{ $adaBilikTersedia ? 'Ada Bilik' : 'Semua Penuh' }}
                    </span>
                </div>
                <div class="stat-number" style="color:{{ $adaBilikTersedia ? '#16a34a' : '#dc2626' }}">
                    {{ $jumlahBilikTersedia }}
                </div>
                <div class="stat-label">Bilik Tersedia Hari Ini</div>
                <div class="stat-sub">{{ $jumlahBilikAktif - $jumlahBilikTersedia }} bilik penuh kedua-dua sesi</div>
            </div>
            <div class="stat-action" style="color:{{ $adaBilikTersedia ? '#16a34a' : '#6b7280' }}">
                <i class="fa-solid fa-magnifying-glass-location text-xs" aria-hidden="true"></i>
                Semak Ketersediaan
            </div>
        </a>

        {{-- Kad 3 — Mesyuarat Hari Ini --}}
        <a href="{{ route('kalendar') }}" class="stat-card-v2" aria-label="Mesyuarat Hari Ini: {{ $mesyuaratHariIni }}. Klik untuk lihat kalendar.">
            <div class="stat-accent-bar" style="background:#2563eb"></div>
            <div class="stat-body">
                <div class="flex items-center justify-between">
                    <div class="stat-icon-wrap" style="background:#eff6ff">
                        <i class="fa-solid fa-users" style="color:#2563eb" aria-hidden="true"></i>
                    </div>
                    @if($mesyuaratHariIni > 0)
                    <span class="text-xs font-semibold px-2 py-1 rounded-full" style="background:#eff6ff;color:#1e40af">
                        <i class="fa-solid fa-circle-dot mr-1" aria-hidden="true" style="color:#22c55e"></i>Sedang Aktif
                    </span>
                    @else
                    <span class="text-xs text-gray-400 font-medium">Tiada Hari Ini</span>
                    @endif
                </div>
                <div class="stat-number" style="color:#2563eb">{{ $mesyuaratHariIni }}</div>
                <div class="stat-label">Mesyuarat Hari Ini</div>
                <div class="stat-sub">{{ \Carbon\Carbon::today()->isoFormat('D MMMM YYYY') }}</div>
            </div>
            <div class="stat-action" style="color:#2563eb">
                <i class="fa-solid fa-calendar-days text-xs" aria-hidden="true"></i> Lihat Kalendar
            </div>
        </a>

        {{-- Kad 4 — Kadar Penggunaan Bilik --}}
        @php
            $warnaGrad  = $kadarPenggunaan >= 80 ? '#dc2626' : ($kadarPenggunaan >= 50 ? '#d97706' : '#16a34a');
            $julat      = $kadarPenggunaan >= 80 ? '≥ 80%' : ($kadarPenggunaan >= 50 ? '50–79%' : 'Bawah 50%');
            $julatBg    = $kadarPenggunaan >= 80 ? '#fee2e2' : ($kadarPenggunaan >= 50 ? '#fef3c7' : '#dcfce7');
            $julatClr   = $kadarPenggunaan >= 80 ? '#991b1b' : ($kadarPenggunaan >= 50 ? '#92400e' : '#166534');
        @endphp
        <a href="{{ route('laporan') }}" class="stat-card-v2" aria-label="Kadar Penggunaan Bilik: {{ $kadarPenggunaan }} peratus. Klik untuk lihat laporan.">
            <div class="stat-accent-bar" style="background:{{ $warnaGrad }}"></div>
            <div class="stat-body">
                <div class="flex items-center justify-between">
                    <div class="stat-icon-wrap" style="background:#f0fdf4">
                        <i class="fa-solid fa-chart-bar" style="color:{{ $warnaGrad }}" aria-hidden="true"></i>
                    </div>
                    <span class="text-xs font-mono font-semibold px-2 py-1 rounded-full"
                          style="background:{{ $julatBg }};color:{{ $julatClr }}"
                          title="Julat kadar penggunaan">
                        {{ $julat }}
                    </span>
                </div>
                <div class="stat-number" style="color:{{ $warnaGrad }}">{{ $kadarPenggunaan }}%</div>
                <div class="stat-label">Kadar Penggunaan Bilik</div>
                <div class="stat-sub">Purata semua bilik, {{ $namaBulan[$bulanIni] }} {{ $tahunIni }}</div>
            </div>
            <div class="stat-action" style="color:{{ $warnaGrad }}">
                <i class="fa-solid fa-chart-bar text-xs" aria-hidden="true"></i> Lihat Laporan Penuh
            </div>
        </a>

    </div>
</section>

{{-- ══ Widget Semak Bilik Pantas ══════════════════════════════════ --}}
<div class="quick-check-panel mb-6">
    <div class="flex flex-col md:flex-row md:items-end gap-4">
        <div class="flex-shrink-0">
            <p class="text-amber-400 font-bold text-sm mb-1 flex items-center gap-2">
                <i class="fa-solid fa-magnifying-glass-location" aria-hidden="true"></i>
                SEMAK BILIK KOSONG
            </p>
            <p class="text-slate-400 text-xs">Terus tahu bilik mana yang tersedia</p>
        </div>

        <form id="quick-form" method="GET" action="{{ route('ketersediaan') }}"
              class="flex flex-1 flex-wrap gap-3 items-end"
              aria-label="Semak ketersediaan bilik pantas">

            <div class="flex-1 min-w-[140px]">
                <label for="quick-tarikh" class="sr-only">Tarikh</label>
                <div class="relative">
                    <input type="text" id="quick-tarikh" name="tarikh"
                        placeholder="Pilih tarikh..."
                        class="form-input pr-10" readonly
                        required autocomplete="off">
                    <i class="fa-solid fa-calendar absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" aria-hidden="true"></i>
                </div>
            </div>

            <div class="flex-1 min-w-[140px]">
                <label for="quick-sesi" class="sr-only">Sesi</label>
                <select id="quick-sesi" name="sesi" class="form-input">
                    <option value="semua">Kedua-dua Sesi</option>
                    <option value="pagi">Pagi (9AM–1PM)</option>
                    <option value="petang">Petang (2PM–6PM)</option>
                </select>
            </div>

            <div class="w-24">
                <label for="quick-peserta" class="sr-only">Bilangan peserta</label>
                <input type="number" id="quick-peserta" name="peserta"
                    min="1" max="500" value="10"
                    placeholder="Peserta"
                    class="form-input"
                    aria-label="Bilangan peserta">
            </div>

            <button type="submit" class="btn-primary whitespace-nowrap">
                <i class="fa-solid fa-search" aria-hidden="true"></i> Semak
            </button>
        </form>
    </div>
</div>

{{-- ══ Panel Bawah ════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Mesyuarat Akan Datang --}}
    <section class="lg:col-span-2 bg-white rounded-xl shadow-sm overflow-hidden"
             aria-labelledby="heading-akan-datang">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h2 id="heading-akan-datang" class="font-bold text-gray-800">Mesyuarat Akan Datang</h2>
                <p class="text-gray-400 text-xs mt-0.5">7 hari hadapan</p>
            </div>
            <a href="{{ route('kalendar') }}"
               class="text-sm font-semibold text-amber-500 hover:text-amber-600 flex items-center gap-1">
                <i class="fa-solid fa-calendar-days text-xs" aria-hidden="true"></i>
                Kalendar →
            </a>
        </div>

        @php
            $grupHariIni    = $mesyuaratAkanDatang->filter(fn($m) => $m->tarikh->isToday());
            $grupEsok       = $mesyuaratAkanDatang->filter(fn($m) => $m->tarikh->isTomorrow());
            $grupSeterusnya = $mesyuaratAkanDatang->filter(fn($m) => !$m->tarikh->isToday() && !$m->tarikh->isTomorrow());
            $hadSeterusnya  = 3;
        @endphp

        @if($mesyuaratAkanDatang->isEmpty())
        {{-- Empty state --}}
        <div class="text-center py-10 px-6">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4" style="background:#fef3c7">
                <i class="fa-solid fa-calendar-days text-3xl" style="color:#f59e0b" aria-hidden="true"></i>
            </div>
            <p class="font-bold text-gray-700 text-base mb-1">Tiada mesyuarat minggu ini</p>
            <p class="text-sm text-gray-400 mb-6 max-w-xs mx-auto">
                Jadual 7 hari ke hadapan masih kosong. Jadualkan mesyuarat sekarang atau semak bilik yang tersedia dahulu.
            </p>
            <div class="flex flex-col gap-2">
                <a href="{{ route('tempahan.create') }}" class="btn-primary justify-center text-sm">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i> Jadualkan Mesyuarat
                </a>
                <a href="{{ route('ketersediaan') }}?tarikh={{ today()->format('Y-m-d') }}&sesi=semua&peserta=10"
                   class="btn-secondary justify-center text-sm">
                    <i class="fa-solid fa-magnifying-glass-location" aria-hidden="true"></i> Semak Bilik Kosong
                </a>
            </div>
        </div>

        @else
        <div class="divide-y divide-gray-100">

            {{-- ── KUMPULAN: HARI INI ────────────────────────── --}}
            @if($grupHariIni->isNotEmpty())
            <div>
                <div class="flex items-center gap-2 px-6 py-2 bg-amber-50 border-b border-amber-100">
                    <span class="w-2 h-2 rounded-full bg-amber-500" style="animation:pulse 2s infinite" aria-hidden="true"></span>
                    <span class="text-xs font-bold text-amber-700 uppercase tracking-wider">Hari Ini</span>
                    <span class="text-xs text-amber-500">— {{ $grupHariIni->count() }} mesyuarat</span>
                </div>
                <ul role="list" class="divide-y divide-gray-50">
                    @foreach($grupHariIni as $m)
                    @include('dashboard._item-mesyuarat', ['m' => $m, 'warnaTarikh' => '#d97706'])
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- ── KUMPULAN: ESOK ────────────────────────────── --}}
            @if($grupEsok->isNotEmpty())
            <div>
                <div class="flex items-center gap-2 px-6 py-2 bg-blue-50 border-b border-blue-100">
                    <span class="w-2 h-2 rounded-full bg-blue-400" aria-hidden="true"></span>
                    <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Esok</span>
                    <span class="text-xs text-blue-400">— {{ $grupEsok->count() }} mesyuarat</span>
                </div>
                <ul role="list" class="divide-y divide-gray-50">
                    @foreach($grupEsok as $m)
                    @include('dashboard._item-mesyuarat', ['m' => $m, 'warnaTarikh' => '#2563eb'])
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- ── KUMPULAN: SETERUSNYA ──────────────────────── --}}
            @if($grupSeterusnya->isNotEmpty())
            <div>
                <div class="flex items-center gap-2 px-6 py-2 bg-gray-50 border-b border-gray-100">
                    <span class="w-2 h-2 rounded-full bg-gray-400" aria-hidden="true"></span>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Seterusnya</span>
                    <span class="text-xs text-gray-400">— {{ $grupSeterusnya->count() }} mesyuarat</span>
                </div>
                <ul role="list" class="divide-y divide-gray-50">
                    @foreach($grupSeterusnya->take($hadSeterusnya) as $m)
                    @include('dashboard._item-mesyuarat', ['m' => $m, 'warnaTarikh' => '#374151'])
                    @endforeach
                </ul>
                @if($grupSeterusnya->count() > $hadSeterusnya)
                <ul role="list" class="divide-y divide-gray-50 hidden" id="senarai-seterusnya-baki">
                    @foreach($grupSeterusnya->skip($hadSeterusnya) as $m)
                    @include('dashboard._item-mesyuarat', ['m' => $m, 'warnaTarikh' => '#374151'])
                    @endforeach
                </ul>
                <div class="px-6 py-3">
                    <button id="btn-tunjuk-baki" type="button"
                            class="text-xs text-amber-500 hover:text-amber-600 font-semibold flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-chevron-down text-xs" aria-hidden="true"></i>
                        +{{ $grupSeterusnya->count() - $hadSeterusnya }} mesyuarat lagi
                    </button>
                </div>
                @endif
            </div>
            @endif

        </div>
        @endif
    </section>

    {{-- Ketersediaan Bilik Hari Ini --}}
    <section class="bg-white rounded-xl shadow-sm overflow-hidden" aria-labelledby="heading-ketersediaan">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 id="heading-ketersediaan" class="font-bold text-gray-800">Ketersediaan Hari Ini</h2>
                <p class="text-xs text-gray-400 mt-0.5">{{ \Carbon\Carbon::today()->isoFormat('D MMMM YYYY') }}</p>
            </div>
            <a href="{{ route('ketersediaan') }}?tarikh={{ today()->format('Y-m-d') }}&sesi=semua&peserta=1"
               class="text-xs font-semibold text-amber-500 hover:text-amber-600 flex items-center gap-1"
               aria-label="Lihat ketersediaan penuh">
                Semak Penuh →
            </a>
        </div>

        {{-- Legend --}}
        <div class="px-5 pt-3 pb-1 flex items-center gap-4">
            <div class="flex items-center gap-1.5">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-green-500"></span>
                <span class="text-xs text-gray-500">Kosong</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-400"></span>
                <span class="text-xs text-gray-500">Penuh</span>
            </div>
            <div class="ml-auto flex gap-3 text-xs font-bold text-gray-400">
                <span>PAGI</span>
                <span>PTNG</span>
            </div>
        </div>

        <ul role="list" class="divide-y divide-gray-50 px-5 pb-2">
            @forelse($ketersediaanHariIni as $b)
            <li class="flex items-center py-2.5 gap-3">
                {{-- Nama bilik --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate" title="{{ $b['nama'] }}">
                        {{ $b['nama'] }}
                    </p>
                    <p class="text-xs text-gray-400">{{ $b['kapasiti'] }} orang</p>
                </div>
                {{-- Status Pagi --}}
                <div class="flex-shrink-0 w-14 text-center">
                    @if($b['pagi'])
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-50"
                              title="Pagi: Kosong" aria-label="{{ $b['nama'] }} pagi kosong">
                            <i class="fa-solid fa-check text-green-500 text-xs" aria-hidden="true"></i>
                        </span>
                    @else
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-50"
                              title="Pagi: Ditempah" aria-label="{{ $b['nama'] }} pagi ditempah">
                            <i class="fa-solid fa-xmark text-red-400 text-xs" aria-hidden="true"></i>
                        </span>
                    @endif
                </div>
                {{-- Status Petang --}}
                <div class="flex-shrink-0 w-14 text-center">
                    @if($b['petang'])
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-50"
                              title="Petang: Kosong" aria-label="{{ $b['nama'] }} petang kosong">
                            <i class="fa-solid fa-check text-green-500 text-xs" aria-hidden="true"></i>
                        </span>
                    @else
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-50"
                              title="Petang: Ditempah" aria-label="{{ $b['nama'] }} petang ditempah">
                            <i class="fa-solid fa-xmark text-red-400 text-xs" aria-hidden="true"></i>
                        </span>
                    @endif
                </div>
            </li>
            @empty
            <li class="py-8 text-center">
                <p class="text-sm text-gray-400">Tiada bilik aktif</p>
            </li>
            @endforelse
        </ul>

        <div class="px-5 pb-4">
            <a href="{{ route('tempahan.create') }}"
               class="w-full flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm font-bold text-white transition-colors"
               style="background:#f59e0b"
               onmouseover="this.style.background='#d97706'" onmouseout="this.style.background='#f59e0b'">
                <i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>
                Buat Tempahan Baru
            </a>
        </div>
    </section>

</div>

{{-- ══ Grafik Statistik ════════════════════════════════════════ --}}
@php
    $labelKategoriWarna = [
        '#f59e0b','#2563eb','#16a34a','#dc2626','#7c3aed','#0891b2','#ea580c','#be185d',
    ];
@endphp
<section aria-labelledby="heading-grafik" class="mt-6">
    <div class="flex items-center gap-3 mb-4">
        <h2 id="heading-grafik" class="font-bold text-gray-800 text-lg">Grafik & Statistik</h2>
        <span class="text-xs text-gray-400 font-medium px-2 py-0.5 bg-gray-100 rounded-full">
            {{ $namaBulan[$bulanIni] }} {{ $tahunIni }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Trend 6 Bulan (bar chart — 2 kolum) --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-gray-800 text-sm">Trend Tempahan</h3>
                    <p class="text-xs text-gray-400 mt-0.5">6 bulan ke belakang</p>
                </div>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#fef3c7">
                    <i class="fa-solid fa-chart-line text-amber-500 text-sm" aria-hidden="true"></i>
                </div>
            </div>
            <div class="relative" style="height:220px">
                <canvas id="chart-trend" aria-label="Carta bar trend tempahan 6 bulan" role="img"></canvas>
            </div>
        </div>

        {{-- Kategori Mesyuarat (donut — 1 kolum) --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-gray-800 text-sm">Kategori Mesyuarat</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Bulan {{ $namaBulan[$bulanIni] }}</p>
                </div>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#eff6ff">
                    <i class="fa-solid fa-chart-pie text-blue-500 text-sm" aria-hidden="true"></i>
                </div>
            </div>
            @if(count($statistikKategori) > 0)
            <div class="relative flex items-center justify-center" style="height:160px">
                <canvas id="chart-kategori" aria-label="Carta donut kategori mesyuarat" role="img"></canvas>
            </div>
            {{-- Legend --}}
            <ul class="mt-3 space-y-1.5">
                @foreach(array_slice($statistikKategori, 0, 5) as $i => $kat)
                <li class="flex items-center gap-2 text-xs text-gray-600">
                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                          style="background:{{ $labelKategoriWarna[$i % count($labelKategoriWarna)] }}"></span>
                    <span class="truncate flex-1" title="{{ $kat['label'] }}">{{ $kat['label'] }}</span>
                    <span class="font-bold text-gray-800">{{ $kat['jumlah'] }}</span>
                </li>
                @endforeach
            </ul>
            @else
            <div class="flex flex-col items-center justify-center py-8 text-center">
                <i class="fa-solid fa-chart-pie text-gray-200 text-4xl mb-2" aria-hidden="true"></i>
                <p class="text-xs text-gray-400">Tiada data bulan ini</p>
            </div>
            @endif
        </div>

        {{-- Penggunaan Bilik (horizontal bar — penuh) --}}
        <div class="lg:col-span-3 bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-gray-800 text-sm">Penggunaan Bilik</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Peratusan penggunaan bulan {{ $namaBulan[$bulanIni] }}</p>
                </div>
                <a href="{{ route('laporan') }}"
                   class="text-xs font-semibold text-amber-500 hover:text-amber-600 flex items-center gap-1">
                    Laporan Penuh →
                </a>
            </div>
            @if($penggunaanBilik->isNotEmpty())
            <div class="space-y-3">
                @foreach($penggunaanBilik as $b)
                @php $pct = min(100, max(0, $b['peratusan'])); @endphp
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-medium text-gray-700 truncate max-w-[60%]"
                              title="{{ $b['nama'] }}">{{ $b['nama'] }}</span>
                        <span class="text-xs font-bold ml-2
                            {{ $pct >= 80 ? 'text-red-600' : ($pct >= 50 ? 'text-amber-600' : 'text-green-600') }}">
                            {{ $pct }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2.5" role="progressbar"
                         aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"
                         aria-label="{{ $b['nama'] }}: {{ $pct }}% digunakan">
                        <div class="h-2.5 rounded-full transition-all duration-500"
                             style="width:{{ $pct }}%;background:{{ $pct >= 80 ? '#dc2626' : ($pct >= 50 ? '#f59e0b' : '#16a34a') }}">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-400 text-center py-6">Tiada data penggunaan bilik</p>
            @endif
        </div>

    </div>
</section>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script nonce="{{ $cspNonce }}">
flatpickr('#quick-tarikh', {
    locale: 'ms',
    dateFormat: 'Y-m-d',
    minDate: 'today',
    disableMobile: true,
    defaultDate: new Date()
});

// Toggle "+X mesyuarat lagi"
const btnBaki = document.getElementById('btn-tunjuk-baki');
if (btnBaki) {
    btnBaki.addEventListener('click', function () {
        const baki = document.getElementById('senarai-seterusnya-baki');
        if (baki) baki.classList.remove('hidden');
        this.style.display = 'none';
    });
}

// ── Chart.js — Trend 6 Bulan ──────────────────────────────────
const trendData = @json($trendBulanan);
const ctxTrend  = document.getElementById('chart-trend');
if (ctxTrend && trendData.length) {
    new Chart(ctxTrend, {
        type: 'bar',
        data: {
            labels:   trendData.map(d => d.label),
            datasets: [{
                label:           'Tempahan',
                data:            trendData.map(d => d.jumlah),
                backgroundColor: trendData.map((_, i) =>
                    i === trendData.length - 1 ? '#f59e0b' : '#fde68a'),
                borderColor:     trendData.map((_, i) =>
                    i === trendData.length - 1 ? '#d97706' : '#fbbf24'),
                borderWidth:     1.5,
                borderRadius:    6,
                borderSkipped:   false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + ctx.parsed.y + ' tempahan'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0, font: { size: 11 } },
                    grid:  { color: '#f3f4f6' }
                },
                x: {
                    ticks: { font: { size: 11 } },
                    grid:  { display: false }
                }
            }
        }
    });
}

// ── Chart.js — Kategori Mesyuarat (Donut) ─────────────────────
const kategoriData   = @json($statistikKategori);
const kategoriWarna  = ['#f59e0b','#2563eb','#16a34a','#dc2626','#7c3aed','#0891b2','#ea580c','#be185d'];
const ctxKategori    = document.getElementById('chart-kategori');
if (ctxKategori && kategoriData.length) {
    new Chart(ctxKategori, {
        type: 'doughnut',
        data: {
            labels:   kategoriData.map(d => d.label),
            datasets: [{
                data:            kategoriData.map(d => d.jumlah),
                backgroundColor: kategoriWarna.slice(0, kategoriData.length),
                borderWidth:     2,
                borderColor:     '#ffffff',
                hoverOffset:     6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + ctx.label + ': ' + ctx.parsed + ' tempahan'
                    }
                }
            }
        }
    });
}
</script>
@endpush
