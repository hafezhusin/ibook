@extends('layouts.app')

@section('title', 'Senarai Tempahan')

@push('styles')
<style>
/* ── Worklist cards ──────────────────────────────────────── */
.wl-card {
    background:#fff; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.08);
    padding:14px 18px; display:flex; align-items:center; gap:12px;
    text-decoration:none; color:inherit;
    transition:box-shadow .18s, transform .13s;
    border:2px solid #e5e7eb; cursor:pointer;
}
.wl-card:hover { box-shadow:0 4px 14px rgba(0,0,0,.12); transform:translateY(-2px); }
.wl-card.aktif {
    border-color:var(--wl-color,#f59e0b);
    background: color-mix(in srgb, var(--wl-color,#f59e0b) 6%, white);
    box-shadow:0 0 0 3px color-mix(in srgb, var(--wl-color,#f59e0b) 18%, transparent);
}
.wl-card.aktif .wl-lbl { color: var(--wl-color, #d97706); font-weight:700; }
.wl-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0; }
.wl-num  { font-size:1.8rem; font-weight:800; line-height:1; }
.wl-lbl  { font-size:11px; color:#6b7280; font-weight:500; margin-top:2px; }

/* ── Chip tarikh ─────────────────────────────────────────── */
.tapis-chip {
    display:inline-flex; align-items:center; gap:5px;
    padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600;
    border:1.5px solid #e5e7eb; background:#fff; color:#6b7280;
    cursor:pointer; text-decoration:none; transition:all .15s; white-space:nowrap;
    user-select:none;
}
.tapis-chip:hover { border-color:#f59e0b; color:#d97706; background:#fef3c7; }
.tapis-chip.aktif {
    background:#1a1a2e; border-color:#1a1a2e; color:#f59e0b;
    cursor:default; pointer-events:none;
}
.chip-x {
    display:inline-flex; align-items:center; justify-content:center;
    width:16px; height:16px; border-radius:50%;
    background:rgba(255,255,255,.2); color:#f59e0b;
    font-size:10px; cursor:pointer; text-decoration:none;
    pointer-events:all; transition:background .12s;
    margin-left:2px;
}
.chip-x:hover { background:rgba(255,255,255,.35); }

/* ── Status badges ───────────────────────────────────────── */
.st-badge {
    display:inline-flex; align-items:center; gap:5px;
    padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;
}
.st-sah     { background:#dcfce7; color:#15803d; }
.st-ditolak { background:#fee2e2; color:#b91c1c; }
.st-tunggu  { background:#fef3c7; color:#92400e; }

/* ── Action dropdown ─────────────────────────────────────── */
.action-wrap { position:relative; display:inline-block; }
.action-trigger {
    display:inline-flex; align-items:center; gap:5px;
    padding:5px 10px; border-radius:7px; font-size:12px; font-weight:600;
    background:#f3f4f6; border:1.5px solid #e5e7eb; color:#374151;
    cursor:pointer; text-decoration:none; transition:all .13s;
    white-space:nowrap;
}
.action-trigger:hover { background:#e5e7eb; border-color:#d1d5db; }
.action-dd {
    position:absolute; right:0; top:calc(100% + 4px); z-index:40;
    background:#fff; border:1.5px solid #e5e7eb; border-radius:10px;
    box-shadow:0 8px 24px rgba(0,0,0,.12); min-width:160px; overflow:hidden;
}
.action-dd a, .action-dd button {
    display:flex; align-items:center; gap:9px; width:100%;
    padding:9px 14px; font-size:13px; font-weight:500; color:#374151;
    text-decoration:none; background:none; border:none; cursor:pointer;
    transition:background .12s;
}
.action-dd a:hover, .action-dd button:hover { background:#f9fafb; }
.action-dd .dd-divider { height:1px; background:#f3f4f6; margin:3px 0; }

/* ── Relative time ───────────────────────────────────────── */
.rel-time { font-size:11px; color:#9ca3af; }
.rel-edit  { font-size:10px; color:#d97706; }

/* ── Dark mode ───────────────────────────────────────────── */
@media (prefers-color-scheme:dark) {
    .wl-card { background:#1e293b !important; border-color:#334155 !important; }
    .wl-card.aktif { background:color-mix(in srgb, var(--wl-color,#f59e0b) 10%, #1e293b) !important; }
    .wl-lbl { color:#94a3b8 !important; }
    .tapis-chip { background:#1e293b !important; border-color:#334155 !important; color:#94a3b8 !important; }
    .tapis-chip:hover { border-color:#f59e0b !important; color:#fbbf24 !important; background:#1c1200 !important; }
    .tapis-chip.aktif { background:#0f172a !important; border-color:#f59e0b !important; color:#f59e0b !important; }
    .st-sah     { background:#14532d !important; color:#86efac !important; }
    .st-ditolak { background:#7f1d1d !important; color:#fca5a5 !important; }
    .st-tunggu  { background:#451a03 !important; color:#fcd34d !important; }
    .action-trigger { background:#334155 !important; border-color:#475569 !important; color:#e2e8f0 !important; }
    .action-trigger:hover { background:#475569 !important; }
    .action-dd { background:#1e293b !important; border-color:#334155 !important; }
    .action-dd a, .action-dd button { color:#e2e8f0 !important; }
    .action-dd a:hover, .action-dd button:hover { background:#273447 !important; }
    .action-dd .dd-divider { background:#334155 !important; }
    .rel-time { color:#64748b !important; }
    #modal-pindah > div { background:#1e293b !important; }
}
</style>
@endpush

@section('content')

@php
    $tf         = request('tarikh_filter', '');
    $sf         = request('status', '');
    $advancedKeys = ['tarikh_dari','tarikh_hingga','kategori','jabatan'];
    $hasAdvanced  = request()->hasAny($advancedKeys);
    $hasFilter    = request()->hasAny(['bilik_id','carian','status','tarikh_filter', ...$advancedKeys]);
    $baseParams   = request()->except(['tarikh_filter','page']); // param tanpa tarikh & page

    $chipLabel = [
        'hari_ini'    => 'Hari Ini',
        'esok'        => 'Esok',
        '7_hari'      => '7 Hari',
        'bulan_ini'   => 'Bulan Ini',
        'akan_datang' => 'Akan Datang',
    ];
@endphp

{{-- ══ Pengepala ════════════════════════════════════════════════════ --}}
<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Senarai Tempahan</h1>
        <p class="text-gray-500 text-sm mt-1">
            @if($hasFilter)
                <strong class="text-gray-700">{{ $tempahan->total() }}</strong> rekod sepadan tapisan
            @else
                {{ $tempahan->total() }} rekod keseluruhan
            @endif
        </p>
    </div>
    <div class="flex items-center gap-3">
        {{-- Eksport: subtle, tidak bersaing dengan CTA utama --}}
        <div class="flex items-center gap-0.5 border border-gray-200 rounded-lg px-1 py-1" aria-label="Eksport data">
            <a href="{{ route('tempahan.pdf', request()->query()) }}"
               class="flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-md transition-colors"
               title="Muat turun senarai dalam format PDF">
                <i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
                <span class="hidden sm:inline">PDF</span>
            </a>
            <div class="w-px h-4 bg-gray-200" aria-hidden="true"></div>
            <a href="{{ route('tempahan.excel', request()->query()) }}"
               class="flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-md transition-colors"
               title="Muat turun senarai dalam format Excel">
                <i class="fa-solid fa-file-excel" aria-hidden="true"></i>
                <span class="hidden sm:inline">Excel</span>
            </a>
        </div>

        {{-- CTA Utama — Tempahan Baru: lebih menonjol --}}
        <a href="{{ route('tempahan.create') }}"
           class="btn-primary font-bold text-sm"
           style="box-shadow: 0 4px 14px rgba(245,158,11,0.4); padding: 10px 22px;">
            <i class="fa-solid fa-plus" aria-hidden="true"></i> Tempahan Baru
        </a>
    </div>
</div>

{{-- ══ Worklist Ringkasan ═══════════════════════════════════════════ --}}
@if($ringkasan !== null)
<section aria-labelledby="heading-ringkasan" class="mb-5">
    <h2 id="heading-ringkasan" class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">
        Pintasan Penapis
        @if(auth()->user()->isStaf() && auth()->user()->jabatan)
        <span class="normal-case font-normal text-gray-400 ml-1">— {{ auth()->user()->jabatan }}</span>
        @endif
    </h2>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">

        @php
            $cards = [
                ['filter'=>'hari_ini',  'label'=>'Hari Ini',    'count'=>$ringkasan['hari_ini'],  'color'=>'#2563eb','bg'=>'#eff6ff','icon'=>'fa-calendar-day'],
                ['filter'=>'baharu',    'label'=>'Baharu 24j',  'count'=>$ringkasan['baharu'],    'color'=>'#d97706','bg'=>'#fef3c7','icon'=>'fa-bell'],
                ['filter'=>'esok',      'label'=>'Esok',        'count'=>$ringkasan['esok'],      'color'=>'#10b981','bg'=>'#ecfdf5','icon'=>'fa-calendar-plus'],
                ['filter'=>'bulan_ini', 'label'=>'Bulan Ini',   'count'=>$ringkasan['bulan_ini'], 'color'=>'#8b5cf6','bg'=>'#f5f3ff','icon'=>'fa-chart-bar'],
            ];
        @endphp

        @foreach($cards as $c)
        @php $aktif = $tf === $c['filter']; @endphp
        @if($aktif)
        {{-- Kad AKTIF: bukan pautan, tunjuk semua rekod yang sedang dipapar --}}
        <div class="wl-card aktif" style="--wl-color:{{ $c['color'] }}"
             aria-current="true"
             aria-label="{{ $c['label'] }}: menunjukkan {{ $tempahan->total() }} rekod (aktif)">
            <div class="wl-icon" style="background:{{ $c['bg'] }}">
                <i class="fa-solid {{ $c['icon'] }}" style="color:{{ $c['color'] }}" aria-hidden="true"></i>
            </div>
            <div class="flex-1">
                <div class="flex items-baseline gap-2">
                    <div class="wl-num" style="color:{{ $c['color'] }}">{{ $tempahan->total() }}</div>
                    @if($c['count'] !== $tempahan->total())
                    <span class="text-xs text-gray-400" title="Jumlah global: {{ $c['count'] }}">/ {{ $c['count'] }}</span>
                    @endif
                </div>
                <div class="wl-lbl">{{ $c['label'] }} ✓</div>
            </div>
            <a href="{{ route('tempahan.index', $baseParams) }}"
               class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs hover:bg-red-100 hover:text-red-500 text-gray-400 transition-colors"
               title="Padam tapisan {{ $c['label'] }}"
               aria-label="Padam tapisan {{ $c['label'] }}">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </a>
        </div>
        @else
        {{-- Kad TIDAK AKTIF: pautan --}}
        <a href="{{ route('tempahan.index', array_merge($baseParams, ['tarikh_filter' => $c['filter']])) }}"
           class="wl-card" style="--wl-color:{{ $c['color'] }}"
           aria-label="Tapis {{ $c['label'] }}: {{ $c['count'] }} rekod">
            <div class="wl-icon" style="background:{{ $c['bg'] }}">
                <i class="fa-solid {{ $c['icon'] }}" style="color:{{ $c['color'] }}" aria-hidden="true"></i>
            </div>
            <div>
                <div class="wl-num" style="color:{{ $c['color'] }}">{{ $c['count'] }}</div>
                <div class="wl-lbl">{{ $c['label'] }}</div>
            </div>
        </a>
        @endif
        @endforeach

    </div>
    <p class="text-xs text-gray-400 mt-2 pl-1">
        <i class="fa-solid fa-circle-info mr-1" aria-hidden="true"></i>
        Angka pintasan = kiraan global. Kad aktif (✓) menunjukkan kiraan sebenar selepas tapisan lain digunakan.
    </p>
</section>
@endif

{{-- ══ Bar Tapis ════════════════════════════════════════════════════ --}}
<section class="bg-white rounded-xl shadow-sm p-4 mb-5" aria-labelledby="heading-filter">
    <h2 id="heading-filter" class="sr-only">Tapis Senarai Tempahan</h2>

    <form method="GET" role="search" aria-label="Cari dan tapis tempahan">
        {{-- Baris 1: carian + bilik + status --}}
        <div class="flex gap-3 flex-wrap mb-3">
            <div class="flex-1 min-w-[180px]">
                <label for="carian-tempahan" class="sr-only">Cari nama mesyuarat</label>
                <div class="relative">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs" aria-hidden="true"></i>
                    <input type="search" id="carian-tempahan" name="carian"
                        value="{{ request('carian') }}"
                        placeholder="Cari nama mesyuarat..."
                        class="form-input text-sm pl-9 w-full">
                </div>
            </div>

            <div>
                <label for="filter-bilik" class="sr-only">Tapis bilik</label>
                <select id="filter-bilik" name="bilik_id" class="form-input w-auto text-sm">
                    <option value="">Semua Bilik</option>
                    @foreach($bilik as $b)
                    <option value="{{ $b->id }}" {{ request('bilik_id') == $b->id ? 'selected' : '' }}>{{ $b->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filter-status" class="sr-only">Tapis status</label>
                <select id="filter-status" name="status" class="form-input w-auto text-sm">
                    <option value="">Semua Status</option>
                    <option value="diluluskan" {{ $sf === 'diluluskan' ? 'selected' : '' }}>✓ Sah</option>
                    <option value="menunggu"   {{ $sf === 'menunggu'   ? 'selected' : '' }}>⏳ Menunggu</option>
                    <option value="ditolak"    {{ $sf === 'ditolak'    ? 'selected' : '' }}>✕ Ditolak</option>
                </select>
            </div>

            @if($tf)<input type="hidden" name="tarikh_filter" value="{{ $tf }}">@endif

            <button type="submit" class="btn-primary text-sm">
                <i class="fa-solid fa-search" aria-hidden="true"></i> Cari
            </button>
            @if($hasFilter)
            <a href="{{ route('tempahan.index') }}" class="btn-secondary text-sm">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i> Reset
            </a>
            @endif
        </div>

        {{-- Tapis Lanjutan (admin/urus setia sahaja) --}}
        @if(!auth()->user()->isStaf())
        <div class="mb-3">
            <button type="button" id="btn-lanjutan"
                onclick="toggleLanjutan()"
                class="flex items-center gap-2 text-xs font-semibold text-gray-500 hover:text-amber-600 transition-colors"
                aria-expanded="{{ $hasAdvanced ? 'true' : 'false' }}"
                aria-controls="panel-lanjutan">
                <i class="fa-solid fa-sliders text-amber-400" aria-hidden="true"></i>
                Tapis Lanjutan
                <i id="arrow-lanjutan" class="fa-solid fa-chevron-down text-xs transition-transform duration-200 {{ $hasAdvanced ? 'rotate-180' : '' }}" aria-hidden="true"></i>
                @if($hasAdvanced)
                <span class="inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-amber-500 rounded-full">
                    {{ collect($advancedKeys)->filter(fn($k) => request()->filled($k))->count() }}
                </span>
                @endif
            </button>

            <div id="panel-lanjutan" class="{{ $hasAdvanced ? '' : 'hidden' }} mt-3 pt-3 border-t border-gray-100">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div>
                        <label for="f-tarikh-dari" class="text-xs font-semibold text-gray-500 block mb-1">Tarikh Dari</label>
                        <input type="date" id="f-tarikh-dari" name="tarikh_dari"
                            value="{{ request('tarikh_dari') }}"
                            class="form-input text-sm"
                            aria-label="Tapis dari tarikh">
                    </div>
                    <div>
                        <label for="f-tarikh-hingga" class="text-xs font-semibold text-gray-500 block mb-1">Tarikh Hingga</label>
                        <input type="date" id="f-tarikh-hingga" name="tarikh_hingga"
                            value="{{ request('tarikh_hingga') }}"
                            class="form-input text-sm"
                            aria-label="Tapis hingga tarikh">
                    </div>
                    <div>
                        <label for="f-kategori" class="text-xs font-semibold text-gray-500 block mb-1">Kategori Mesyuarat</label>
                        <select id="f-kategori" name="kategori" class="form-input text-sm" aria-label="Tapis kategori">
                            <option value="">Semua Kategori</option>
                            @foreach($kategori as $key => $label)
                            <option value="{{ $key }}" {{ request('kategori') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="f-jabatan" class="text-xs font-semibold text-gray-500 block mb-1">Unit / Jabatan</label>
                        <input type="text" id="f-jabatan" name="jabatan"
                            value="{{ request('jabatan') }}"
                            placeholder="Cth: Unit Khidmat Pelanggan"
                            class="form-input text-sm"
                            aria-label="Tapis mengikut unit atau jabatan">
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Baris 2: chip tarikh --}}
        <div class="flex flex-wrap gap-2 items-center" role="group" aria-label="Tapis tarikh pantas">
            <span class="text-xs text-gray-400 font-semibold uppercase tracking-wider" aria-hidden="true">Tarikh:</span>

            @php
                $chips = [
                    'hari_ini'    => ['label'=>'Hari Ini',    'icon'=>'fa-calendar-day'],
                    'esok'        => ['label'=>'Esok',        'icon'=>'fa-calendar-plus'],
                    '7_hari'      => ['label'=>'7 Hari',      'icon'=>'fa-calendar-week'],
                    'bulan_ini'   => ['label'=>'Bulan Ini',   'icon'=>'fa-calendar'],
                    'akan_datang' => ['label'=>'Akan Datang', 'icon'=>'fa-forward'],
                ];
            @endphp

            @foreach($chips as $val => $chip)
            @if($tf === $val)
                {{-- AKTIF: bukan pautan, tunjuk dengan X untuk buang --}}
                <span class="tapis-chip aktif" aria-current="true" aria-label="{{ $chip['label'] }} aktif">
                    <i class="fa-solid {{ $chip['icon'] }}" aria-hidden="true"></i>
                    {{ $chip['label'] }}
                    <a href="{{ route('tempahan.index', $baseParams) }}"
                       class="chip-x" aria-label="Padam tapisan {{ $chip['label'] }}">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </a>
                </span>
            @else
                {{-- TIDAK AKTIF: pautan biasa --}}
                <a href="{{ route('tempahan.index', array_merge($baseParams, ['tarikh_filter' => $val])) }}"
                   class="tapis-chip"
                   aria-label="Tapis: {{ $chip['label'] }}">
                    <i class="fa-solid {{ $chip['icon'] }}" aria-hidden="true"></i>
                    {{ $chip['label'] }}
                </a>
            @endif
            @endforeach
        </div>
    </form>
</section>

{{-- ══ Ringkasan Tapisan Aktif ══════════════════════════════════════ --}}
@if($hasFilter)
@php
    $bilikNama    = request('bilik_id') ? ($bilik->firstWhere('id', request('bilik_id'))?->nama ?? '—') : null;
    $statusLabel  = ['diluluskan'=>'Sah ✓','menunggu'=>'Menunggu ⏳','ditolak'=>'Ditolak ✕'][request('status')] ?? null;
    $tarikhLabel  = $chipLabel[$tf] ?? null;
    $kategoriLbl  = $kategori[request('kategori')] ?? null;
    $dariLabel    = request('tarikh_dari') ? \Carbon\Carbon::parse(request('tarikh_dari'))->format('d/m/Y') : null;
    $hinggaLabel  = request('tarikh_hingga') ? \Carbon\Carbon::parse(request('tarikh_hingga'))->format('d/m/Y') : null;
    $jabatanLbl   = request('jabatan') ?: null;
    $carianLbl    = request('carian') ?: null;
@endphp
<div class="flex flex-wrap items-center gap-2 mb-4 px-1"
     aria-label="Tapisan aktif" role="region">
    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex-shrink-0">Ditapis:</span>

    @if($bilikNama)
    <span class="inline-flex items-center gap-1.5 text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 rounded-full px-3 py-1">
        <i class="fa-solid fa-door-open text-blue-400" aria-hidden="true"></i> {{ $bilikNama }}
        <a href="{{ route('tempahan.index', request()->except(['bilik_id','page'])) }}" class="text-blue-400 hover:text-red-400 ml-0.5" aria-label="Buang tapisan bilik">×</a>
    </span>
    @endif

    @if($statusLabel)
    <span class="inline-flex items-center gap-1.5 text-xs font-medium bg-violet-50 text-violet-700 border border-violet-200 rounded-full px-3 py-1">
        <i class="fa-solid fa-circle-dot text-violet-400" aria-hidden="true"></i> Status: {{ $statusLabel }}
        <a href="{{ route('tempahan.index', request()->except(['status','page'])) }}" class="text-violet-400 hover:text-red-400 ml-0.5" aria-label="Buang tapisan status">×</a>
    </span>
    @endif

    @if($tarikhLabel)
    <span class="inline-flex items-center gap-1.5 text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200 rounded-full px-3 py-1">
        <i class="fa-solid fa-calendar text-amber-400" aria-hidden="true"></i> {{ $tarikhLabel }}
        <a href="{{ route('tempahan.index', request()->except(['tarikh_filter','page'])) }}" class="text-amber-400 hover:text-red-400 ml-0.5" aria-label="Buang tapisan tarikh">×</a>
    </span>
    @endif

    @if($dariLabel || $hinggaLabel)
    <span class="inline-flex items-center gap-1.5 text-xs font-medium bg-teal-50 text-teal-700 border border-teal-200 rounded-full px-3 py-1">
        <i class="fa-solid fa-calendar-range text-teal-400" aria-hidden="true"></i>
        {{ $dariLabel ?? '…' }} → {{ $hinggaLabel ?? '…' }}
        <a href="{{ route('tempahan.index', request()->except(['tarikh_dari','tarikh_hingga','page'])) }}" class="text-teal-400 hover:text-red-400 ml-0.5" aria-label="Buang tapisan julat tarikh">×</a>
    </span>
    @endif

    @if($kategoriLbl)
    <span class="inline-flex items-center gap-1.5 text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full px-3 py-1">
        <i class="fa-solid fa-tag text-emerald-400" aria-hidden="true"></i> {{ $kategoriLbl }}
        <a href="{{ route('tempahan.index', request()->except(['kategori','page'])) }}" class="text-emerald-400 hover:text-red-400 ml-0.5" aria-label="Buang tapisan kategori">×</a>
    </span>
    @endif

    @if($jabatanLbl)
    <span class="inline-flex items-center gap-1.5 text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-full px-3 py-1">
        <i class="fa-solid fa-building text-indigo-400" aria-hidden="true"></i> {{ Str::limit($jabatanLbl, 30) }}
        <a href="{{ route('tempahan.index', request()->except(['jabatan','page'])) }}" class="text-indigo-400 hover:text-red-400 ml-0.5" aria-label="Buang tapisan unit">×</a>
    </span>
    @endif

    @if($carianLbl)
    <span class="inline-flex items-center gap-1.5 text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200 rounded-full px-3 py-1">
        <i class="fa-solid fa-search text-gray-400" aria-hidden="true"></i> "{{ $carianLbl }}"
        <a href="{{ route('tempahan.index', request()->except(['carian','page'])) }}" class="text-gray-400 hover:text-red-400 ml-0.5" aria-label="Buang carian">×</a>
    </span>
    @endif

    <a href="{{ route('tempahan.index') }}"
       class="text-xs text-gray-400 hover:text-red-400 underline ml-1 flex-shrink-0"
       aria-label="Padam semua tapisan">
        Padam semua
    </a>
</div>
@endif

{{-- ══ Jadual ═══════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table w-full" style="min-width:820px">
            <thead class="table-header">
                <tr>
                    <th scope="col" class="w-[30%]">Mesyuarat</th>
                    <th scope="col">Tarikh &amp; Masa</th>
                    <th scope="col">Bilik</th>
                    <th scope="col">Pemohon</th>
                    <th scope="col">Status</th>
                    <th scope="col">Dicipta</th>
                    <th scope="col" class="text-center w-24">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tempahan as $t)
                <tr>
                    <td>
                        <div class="font-semibold text-gray-800 leading-snug">{{ $t->nama_mesyuarat }}</div>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-xs text-gray-400">{{ $t->kategori_label }}</span>
                            <span class="text-[10px] font-mono text-gray-300 border border-gray-100 rounded px-1 leading-snug select-all"
                                  title="Nombor Rujukan Tempahan">{{ $t->no_rujukan }}</span>
                        </div>
                    </td>
                    <td class="whitespace-nowrap">
                        <time datetime="{{ $t->tarikh->format('Y-m-d') }}" class="font-medium text-gray-700 text-sm">
                            {{ $t->tarikh->format('d/m/Y') }}
                        </time>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $t->masa_label }}</div>
                    </td>
                    <td>
                        <div class="text-sm font-medium text-gray-700 leading-snug">{{ $t->bilik->nama ?? '—' }}</div>
                        <div class="text-xs text-gray-400">{{ $t->bilangan_peserta }} peserta</div>
                    </td>
                    <td class="text-sm text-gray-600">{{ $t->pengguna->name ?? '—' }}</td>
                    <td>
                        @if($t->status === 'diluluskan')
                            <span class="st-badge st-sah" role="status">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block" aria-hidden="true"></span>Sah
                            </span>
                        @elseif($t->status === 'ditolak')
                            <span class="st-badge st-ditolak" role="status">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block" aria-hidden="true"></span>Ditolak
                            </span>
                        @else
                            <span class="st-badge st-tunggu" role="status">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 inline-block" aria-hidden="true"></span>Menunggu
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="rel-time"
                             title="Dicipta: {{ $t->created_at->format('d/m/Y, h:i A') }}">
                            {{ $t->created_at->diffForHumans() }}
                        </div>
                        @if($t->dikemaskini_oleh && $t->dikemaskini_oleh !== $t->user_id)
                        {{-- Dikemaskini oleh orang lain (rakan seunit / urus setia) --}}
                        <div class="rel-edit"
                             title="Dikemaskini oleh: {{ $t->pengubah->name ?? '—' }}&#10;Tarikh: {{ $t->dikemaskini_pada?->format('d/m/Y, h:i A') ?? '—' }}">
                            ✎ {{ $t->pengubah->name ?? '—' }}
                        </div>
                        @elseif($t->updated_at->gt($t->created_at->addMinutes(2)))
                        <div class="rel-edit"
                             title="Kemaskini: {{ $t->updated_at->format('d/m/Y, h:i A') }}">
                            ✎ {{ $t->updated_at->diffForHumans() }}
                        </div>
                        @endif
                    </td>

                    {{-- Dropdown Tindakan --}}
                    <td class="text-center">
                        <div class="action-wrap" id="wrap-{{ $t->id }}">
                            <button type="button"
                                onclick="toggleDd({{ $t->id }})"
                                class="action-trigger"
                                aria-haspopup="true"
                                aria-expanded="false"
                                aria-controls="dd-{{ $t->id }}"
                                aria-label="Tindakan untuk {{ $t->nama_mesyuarat }}">
                                Tindakan <i class="fa-solid fa-chevron-down text-xs" aria-hidden="true"></i>
                            </button>
                            <div id="dd-{{ $t->id }}" class="action-dd hidden" role="menu">
                                <a href="{{ route('tempahan.show', $t) }}" role="menuitem">
                                    <i class="fa-solid fa-eye w-4 text-amber-500" aria-hidden="true"></i> Lihat Butiran
                                </a>
                                @if($t->bolehDiEditOleh(auth()->user()))
                                <a href="{{ route('tempahan.edit', $t) }}" role="menuitem">
                                    <i class="fa-solid fa-pen w-4 text-blue-500" aria-hidden="true"></i> Edit
                                </a>
                                <button type="button"
                                    onclick="bukaPindahBilik({{ $t->id }}, '{{ addslashes($t->nama_mesyuarat) }}', {{ $t->bilik_id ?? 'null' }}); closeDd({{ $t->id }})"
                                    role="menuitem">
                                    <i class="fa-solid fa-right-left w-4 text-violet-500" aria-hidden="true"></i> Pindah Bilik
                                </button>
                                @endif
                                <div class="dd-divider" role="separator"></div>
                                <a href="{{ route('tempahan.create', ['duplikat_id' => $t->id]) }}" role="menuitem">
                                    <i class="fa-solid fa-copy w-4 text-gray-400" aria-hidden="true"></i> Salin Tempahan
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-14 text-center">
                        @if($hasFilter)
                            <i class="fa-solid fa-filter-circle-xmark text-4xl mb-4 block text-gray-300" aria-hidden="true"></i>
                            <p class="font-semibold text-gray-500 text-base mb-1">Tiada rekod sepadan</p>
                            <p class="text-sm text-gray-400 mb-5">Cuba ubah kata carian, status, atau pilih tarikh berbeza</p>
                            <a href="{{ route('tempahan.index') }}" class="btn-secondary text-sm">
                                <i class="fa-solid fa-xmark" aria-hidden="true"></i> Padam Semua Tapisan
                            </a>
                        @else
                            <i class="fa-solid fa-calendar-plus text-5xl mb-4 block text-amber-300" aria-hidden="true"></i>
                            <p class="font-bold text-gray-600 text-lg mb-1">Belum ada tempahan</p>
                            <p class="text-sm text-gray-400 mb-6 max-w-sm mx-auto">
                                Mulakan dengan membuat tempahan baharu, atau semak bilik yang masih kosong dahulu.
                            </p>
                            <div class="flex justify-center gap-3 flex-wrap">
                                <a href="{{ route('tempahan.create') }}" class="btn-primary">
                                    <i class="fa-solid fa-plus" aria-hidden="true"></i> Tempahan Baru
                                </a>
                                <a href="{{ route('ketersediaan') }}" class="btn-secondary">
                                    <i class="fa-solid fa-magnifying-glass-location" aria-hidden="true"></i> Semak Bilik Kosong
                                </a>
                            </div>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tempahan->hasPages())
    <nav class="px-6 py-4 border-t border-gray-100 flex items-center justify-between flex-wrap gap-3"
         aria-label="Navigasi halaman">
        <p class="text-sm text-gray-500">
            Rekod <strong>{{ $tempahan->firstItem() }}</strong>–<strong>{{ $tempahan->lastItem() }}</strong>
            daripada <strong>{{ $tempahan->total() }}</strong>
        </p>
        {{ $tempahan->withQueryString()->links() }}
    </nav>
    @endif
</div>

{{-- ══ Modal Pindah Bilik ════════════════════════════════════════════ --}}
<div id="modal-pindah"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    role="dialog" aria-modal="true" aria-labelledby="pindah-heading">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm mx-4 overflow-hidden">
        <div class="px-6 pt-5 pb-4" style="background:#1a1a2e">
            <h2 id="pindah-heading" class="text-white font-bold">
                <i class="fa-solid fa-right-left text-amber-400 mr-2" aria-hidden="true"></i>
                Pindah Bilik
            </h2>
            <p id="pindah-nama" class="text-slate-400 text-sm mt-1 truncate"></p>
        </div>
        <form id="form-pindah" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="pindah-bilik-select" class="form-label">Pilih Bilik Baharu</label>
                <select id="pindah-bilik-select" name="bilik_id" class="form-input" required>
                    @foreach($bilik as $b)
                    <option value="{{ $b->id }}" data-kapasiti="{{ $b->kapasiti }}">
                        {{ $b->nama }} ({{ $b->kapasiti }} orang)
                    </option>
                    @endforeach
                </select>
            </div>
            <p class="text-xs text-gray-400">
                <i class="fa-solid fa-info-circle text-amber-400 mr-1" aria-hidden="true"></i>
                Maklumat lain (tarikh, sesi, peserta) tidak berubah.
            </p>
            <div class="flex gap-3 pt-1">
                <button type="submit" class="btn-primary flex-1 justify-center">
                    <i class="fa-solid fa-check" aria-hidden="true"></i> Pindah
                </button>
                <button type="button"
                    onclick="document.getElementById('modal-pindah').classList.add('hidden')"
                    class="btn-secondary flex-1 justify-center">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ── Tapis Lanjutan toggle ─────────────────────────────────────────
function toggleLanjutan() {
    const panel = document.getElementById('panel-lanjutan');
    const arrow = document.getElementById('arrow-lanjutan');
    const btn   = document.getElementById('btn-lanjutan');
    const isHidden = panel.classList.contains('hidden');
    panel.classList.toggle('hidden', !isHidden);
    arrow.classList.toggle('rotate-180', isHidden);
    btn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
}

// ── Dropdown tindakan ────────────────────────────────────────────
function toggleDd(id) {
    const dd  = document.getElementById('dd-' + id);
    const btn = dd.previousElementSibling;
    const open = !dd.classList.contains('hidden');
    // Tutup semua
    document.querySelectorAll('.action-dd').forEach(d => d.classList.add('hidden'));
    document.querySelectorAll('.action-trigger').forEach(b => b.setAttribute('aria-expanded','false'));
    // Buka yang dipilih (jika sebelumnya tutup)
    if (!open) {
        dd.classList.remove('hidden');
        btn.setAttribute('aria-expanded','true');
    }
}

function closeDd(id) {
    document.getElementById('dd-' + id)?.classList.add('hidden');
}

// Tutup dropdown bila klik luar
document.addEventListener('click', function(e) {
    if (!e.target.closest('.action-wrap')) {
        document.querySelectorAll('.action-dd').forEach(d => d.classList.add('hidden'));
        document.querySelectorAll('.action-trigger').forEach(b => b.setAttribute('aria-expanded','false'));
    }
});

// Tutup dengan Esc
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.action-dd').forEach(d => d.classList.add('hidden'));
        document.getElementById('modal-pindah')?.classList.add('hidden');
    }
});

// ── Modal Pindah Bilik ───────────────────────────────────────────
function bukaPindahBilik(id, nama, bilikId) {
    document.getElementById('pindah-nama').textContent = nama;
    document.getElementById('form-pindah').action = '/tempahan/' + id;
    const sel = document.getElementById('pindah-bilik-select');
    if (bilikId) sel.value = bilikId;
    document.getElementById('modal-pindah').classList.remove('hidden');
    setTimeout(() => sel.focus(), 50);
}

document.getElementById('modal-pindah').addEventListener('click', function(e) {
    if (e.target === this) this.classList.add('hidden');
});
</script>
@endpush
