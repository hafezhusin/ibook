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

<div class="mb-6 flex items-start justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Selamat Datang, {{ auth()->user()->name }}</h1>
        <p class="text-gray-500 mt-1">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}</p>
    </div>
    <a href="{{ route('tempahan.create') }}" class="btn-primary">
        <i class="fa-solid fa-plus" aria-hidden="true"></i> Tempahan Baru
    </a>
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

            <div class="flex-1 min-w-[150px]">
                <label for="quick-tarikh" class="sr-only">Tarikh</label>
                <div class="relative">
                    <input type="text" id="quick-tarikh" name="tarikh"
                        placeholder="Pilih tarikh..."
                        class="form-input pr-10" readonly
                        required autocomplete="off">
                    <i class="fa-solid fa-calendar absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" aria-hidden="true"></i>
                </div>
            </div>

            <div class="min-w-[180px]">
                <label for="quick-sesi" class="sr-only">Sesi</label>
                <select id="quick-sesi" name="sesi" class="form-input">
                    <option value="semua">Kedua-dua Sesi</option>
                    <option value="pagi">Pagi (9AM–1PM)</option>
                    <option value="petang">Petang (2PM–6PM)</option>
                </select>
            </div>

            <div class="min-w-[130px]">
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

        @if($mesyuaratAkanDatang->isEmpty())
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
        <ul role="list" class="divide-y divide-gray-50">
            @foreach($mesyuaratAkanDatang as $m)
            <li class="flex gap-4 px-6 py-4 hover:bg-gray-50 transition-colors">
                <div class="text-center min-w-[44px] flex-shrink-0" aria-hidden="true">
                    <div class="text-xs text-gray-400 uppercase font-semibold">{{ $m->tarikh->isoFormat('ddd') }}</div>
                    <div class="text-xl font-extrabold leading-tight"
                         style="color:{{ $m->tarikh->isToday() ? '#d97706' : '#374151' }}">
                        {{ $m->tarikh->format('d') }}
                    </div>
                    <div class="text-xs text-gray-400">{{ $m->tarikh->isoFormat('MMM') }}</div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-gray-800 truncate">{{ $m->nama_mesyuarat }}</div>
                    <div class="text-xs text-gray-500 mt-0.5 flex flex-wrap gap-x-2">
                        <span><i class="fa-solid fa-door-open text-amber-400 mr-1" aria-hidden="true"></i>{{ $m->bilik->nama ?? '—' }}</span>
                        <span>&middot; {{ $m->masa_label }}</span>
                        <span>&middot; {{ $m->bilangan_peserta }} peserta</span>
                    </div>
                    @if($m->tarikh->isToday())
                    <span class="inline-block mt-1 text-xs font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">
                        Hari Ini
                    </span>
                    @endif
                </div>
                <div class="flex flex-col items-end gap-2 flex-shrink-0">
                    <span class="badge-lulus">Disahkan</span>
                    <a href="{{ route('tempahan.show', $m) }}"
                       class="text-xs text-gray-400 hover:text-amber-500"
                       aria-label="Butiran: {{ $m->nama_mesyuarat }}">
                        Butiran →
                    </a>
                </div>
            </li>
            @endforeach
        </ul>
        @endif
    </section>

    {{-- Penggunaan Bilik --}}
    <section class="bg-white rounded-xl shadow-sm overflow-hidden" aria-labelledby="heading-penggunaan">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 id="heading-penggunaan" class="font-bold text-gray-800">Penggunaan Bilik</h2>
            <p class="text-xs text-gray-400 mt-0.5">{{ $namaBulan[$bulanIni] }} {{ $tahunIni }}</p>
        </div>
        <div class="px-5 py-4">
            <ul role="list" class="space-y-4">
                @foreach($penggunaanBilik as $b)
                <li>
                    <div class="flex justify-between items-center text-sm mb-1.5">
                        <span class="text-gray-700 font-medium truncate max-w-[140px]"
                              title="{{ $b['nama'] }}">{{ $b['nama'] }}</span>
                        <span class="font-bold text-sm ml-2 flex-shrink-0"
                              style="color:{{ $b['peratusan'] >= 80 ? '#dc2626' : ($b['peratusan'] >= 50 ? '#d97706' : '#16a34a') }}">
                            {{ $b['peratusan'] }}%
                        </span>
                    </div>
                    <div class="progress-bar" role="progressbar"
                        aria-valuenow="{{ $b['peratusan'] }}" aria-valuemin="0" aria-valuemax="100"
                        aria-label="{{ $b['nama'] }}: {{ $b['peratusan'] }}% digunakan">
                        <div class="progress-fill" style="width:{{ $b['peratusan'] }}%;
                            background:{{ $b['peratusan'] >= 80 ? '#dc2626' : ($b['peratusan'] >= 50 ? '#f59e0b' : '#16a34a') }}">
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
            <a href="{{ route('laporan') }}"
               class="mt-5 flex items-center justify-center gap-1 text-xs text-amber-500 hover:text-amber-600 font-semibold">
                <i class="fa-solid fa-chart-bar" aria-hidden="true"></i> Laporan Lengkap
            </a>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script nonce="{{ $cspNonce }}">
flatpickr('#quick-tarikh', {
    locale: 'ms',
    dateFormat: 'Y-m-d',
    minDate: 'today',
    disableMobile: true,
    defaultDate: new Date()
});
</script>
@endpush
