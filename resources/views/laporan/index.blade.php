@extends('layouts.app')

@section('title', 'Laporan')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

@if($isStaf)
{{-- ============================================================ --}}
{{-- PAPARAN STAF — Statistik Unit Sendiri Sahaja                 --}}
{{-- ============================================================ --}}

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Laporan Tempahan Unit</h1>
        <p class="text-gray-500 text-sm mt-1">
            <i class="fa-solid fa-building text-amber-400 mr-1" aria-hidden="true"></i>
            {{ $jabatan ?? 'Unit Anda' }} &mdash; {{ $tahun }}
        </p>
    </div>
    <form method="GET" aria-label="Tapis laporan mengikut tahun">
        <label for="pilih-tahun-staf" class="sr-only">Pilih tahun laporan</label>
        <select id="pilih-tahun-staf" name="tahun" class="form-input w-auto text-sm">
            @foreach($senaraiTahun as $t)
            <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
    </form>
</div>

{{-- Kad Ringkasan Unit --}}
<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 bg-green-100">
            <i class="fa-solid fa-circle-check text-xl text-green-600" aria-hidden="true"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $totalDiluluskan }}</p>
            <p class="text-sm text-gray-500">Jumlah Tempahan</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 bg-red-100">
            <i class="fa-solid fa-circle-xmark text-xl text-red-500" aria-hidden="true"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $totalDitolak }}</p>
            <p class="text-sm text-gray-500">Dibatalkan / Ditolak</p>
        </div>
    </div>
</div>

{{-- Graf unit --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <section class="bg-white rounded-xl shadow-sm p-6" aria-labelledby="heading-graf-bulan-unit">
        <h2 id="heading-graf-bulan-unit" class="font-bold text-gray-800 mb-5">
            Tempahan Diluluskan Mengikut Bulan — {{ $tahun }}
        </h2>
        <canvas id="chartBulan" height="220"
            role="img"
            aria-label="Graf bar: bilangan tempahan diluluskan bagi unit {{ $jabatan ?? '' }} mengikut bulan tahun {{ $tahun }}">
        </canvas>
    </section>
    <section class="bg-white rounded-xl shadow-sm p-6" aria-labelledby="heading-graf-kategori-unit">
        <h2 id="heading-graf-kategori-unit" class="font-bold text-gray-800 mb-5">
            Tempahan Mengikut Kategori — {{ $tahun }}
        </h2>
        @if($mengikutKategori->isEmpty())
        <div class="flex items-center justify-center h-48 text-gray-400">
            <div class="text-center">
                <i class="fa-solid fa-chart-pie text-3xl mb-2" aria-hidden="true"></i>
                <p class="text-sm">Tiada data bagi tahun {{ $tahun }}</p>
            </div>
        </div>
        @else
        <canvas id="chartKategori" height="220"
            role="img"
            aria-label="Graf donat: taburan tempahan unit mengikut kategori mesyuarat bagi tahun {{ $tahun }}">
        </canvas>
        @endif
    </section>
</div>

@else
{{-- ============================================================ --}}
{{-- PAPARAN PENTADBIR / URUS SETIA — Semua Statistik             --}}
{{-- ============================================================ --}}

{{-- Header + pemilih tahun --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Laporan</h1>
        <p class="text-gray-500 text-sm mt-1">Ringkasan penggunaan bilik dan tempahan</p>
    </div>
    <form method="GET" aria-label="Tapis laporan mengikut tahun">
        {{-- Item 3 (Finding 3): Buang aria-label redundan — label[for] sudah cukup --}}
        <label for="pilih-tahun" class="sr-only">Pilih tahun laporan</label>
        <select id="pilih-tahun" name="tahun" class="form-input w-auto text-sm">
            @foreach($senaraiTahun as $t)
            <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
    </form>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- Item 2: KPI SUMMARY CARDS (eksekutif)     --}}
{{-- ══════════════════════════════════════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">

    {{-- KPI 1: Jumlah Diluluskan --}}
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-circle-check text-green-600" aria-hidden="true"></i>
        </div>
        <div>
            <p class="text-2xl font-extrabold text-gray-800 tabular-nums">{{ number_format($totalDiluluskan) }}</p>
            <p class="text-xs text-gray-500 leading-snug">Tempahan<br>Diluluskan</p>
        </div>
    </div>

    {{-- KPI 2: Unit Paling Aktif --}}
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-building text-blue-600" aria-hidden="true"></i>
        </div>
        <div class="min-w-0">
            <p class="text-sm font-extrabold text-gray-800 leading-tight truncate"
               title="{{ $unitPalingAktif?->unit ?? '—' }}">
                {{ $unitPalingAktif ? \Illuminate\Support\Str::limit($unitPalingAktif->unit, 22) : '—' }}
            </p>
            <p class="text-xs text-gray-500">Unit Paling Aktif
                @if($unitPalingAktif)
                <span class="font-semibold text-blue-600">({{ $unitPalingAktif->jumlah }})</span>
                @endif
            </p>
        </div>
    </div>

    {{-- KPI 3: Bilik Paling Digunakan --}}
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-door-open text-amber-500" aria-hidden="true"></i>
        </div>
        <div class="min-w-0">
            <p class="text-sm font-extrabold text-gray-800 leading-tight truncate"
               title="{{ $bilikPalingGuna['nama'] ?? '—' }}">
                {{ isset($bilikPalingGuna['nama']) ? \Illuminate\Support\Str::limit($bilikPalingGuna['nama'], 22) : '—' }}
            </p>
            <p class="text-xs text-gray-500">Bilik Paling Digunakan
                @if(isset($bilikPalingGuna['jumlah_tempahan']))
                <span class="font-semibold text-amber-600">({{ $bilikPalingGuna['jumlah_tempahan'] }} sesi)</span>
                @endif
            </p>
        </div>
    </div>

    {{-- KPI 4: Purata Penggunaan --}}
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0
            {{ $purataPenggunaan >= 70 ? 'bg-red-100' : ($purataPenggunaan >= 40 ? 'bg-amber-100' : 'bg-green-100') }}">
            <i class="fa-solid fa-chart-simple text-lg
                {{ $purataPenggunaan >= 70 ? 'text-red-500' : ($purataPenggunaan >= 40 ? 'text-amber-500' : 'text-green-600') }}"
                aria-hidden="true"></i>
        </div>
        <div>
            <p class="text-2xl font-extrabold tabular-nums
                {{ $purataPenggunaan >= 70 ? 'text-red-500' : ($purataPenggunaan >= 40 ? 'text-amber-500' : 'text-green-600') }}">
                {{ $purataPenggunaan }}%
            </p>
            <p class="text-xs text-gray-500">Purata Penggunaan<br>Bilik</p>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════ --}}
{{-- Item 7: INSIGHT SENTENCES                 --}}
{{-- ══════════════════════════════════════════ --}}
@if($insightUnit || $insightBilik)
<div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5">
    <div class="flex items-start gap-2">
        <i class="fa-solid fa-lightbulb text-amber-500 mt-0.5 flex-shrink-0" aria-hidden="true"></i>
        <div class="space-y-1">
            <p class="text-xs font-semibold text-amber-700 uppercase tracking-wider mb-1">Ringkasan Eksekutif {{ $tahun }}</p>
            @if($insightUnit)
            <p class="text-sm text-amber-900">&#x2022; {{ $insightUnit }}</p>
            @endif
            @if($insightBilik)
            <p class="text-sm text-amber-900">&#x2022; {{ $insightBilik }}</p>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════ --}}
{{-- GRAF: BULAN + KATEGORI                    --}}
{{-- ══════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <section class="bg-white rounded-xl shadow-sm p-6" aria-labelledby="heading-graf-bulan">
        <h2 id="heading-graf-bulan" class="font-bold text-gray-800 mb-5">
            Tempahan Mengikut Bulan — {{ $tahun }}
        </h2>
        <canvas id="chartBulan" height="200"
            role="img"
            aria-label="Graf bar: bilangan tempahan diluluskan bagi setiap bulan dalam tahun {{ $tahun }}. Lihat jadual di bawah untuk data lengkap.">
        </canvas>
    </section>
    <section class="bg-white rounded-xl shadow-sm p-6" aria-labelledby="heading-graf-kategori">
        <h2 id="heading-graf-kategori" class="font-bold text-gray-800 mb-5">
            Tempahan Mengikut Kategori — {{ $tahun }}
        </h2>
        @if($mengikutKategori->isEmpty())
        <div class="flex items-center justify-center h-48 text-gray-400">
            <div class="text-center">
                <i class="fa-solid fa-chart-pie text-3xl mb-2" aria-hidden="true"></i>
                <p class="text-sm">Tiada data bagi tahun {{ $tahun }}</p>
            </div>
        </div>
        @else
        <canvas id="chartKategori" height="200"
            role="img"
            aria-label="Graf donat: taburan tempahan mengikut kategori mesyuarat bagi tahun {{ $tahun }}. Lihat jadual di bawah untuk data lengkap.">
        </canvas>
        @endif
    </section>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- Item 3: UNIT DENGAN COLLAPSIBLE            --}}
{{-- ══════════════════════════════════════════ --}}
@if(isset($mengikutUnit) && $mengikutUnit->count() > 0)
<section class="bg-white rounded-xl shadow-sm overflow-hidden mb-6" aria-labelledby="heading-unit">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 id="heading-unit" class="font-bold text-gray-800">
            Tempahan Diluluskan Mengikut Unit
            <span class="text-sm font-normal text-gray-400 ml-1">({{ $tahun }})</span>
        </h2>
        <span class="text-xs text-gray-400">{{ $mengikutUnit->count() }} unit</span>
    </div>
    <div class="p-6">
        @php $maxUnit = $mengikutUnit->max('jumlah') ?: 1; @endphp

        {{-- Top 5 sentiasa kelihatan --}}
        <div class="space-y-3" id="unit-top5">
            @foreach($mengikutUnit->take(5) as $u)
            <div class="flex items-center gap-3">
                <div class="w-52 text-sm text-gray-700 truncate flex-shrink-0" title="{{ $u->unit }}">
                    {{ $u->unit }}
                </div>
                <div class="flex-1">
                    <div class="progress-bar"
                        role="progressbar"
                        aria-valuenow="{{ $u->jumlah }}"
                        aria-valuemin="0"
                        aria-valuemax="{{ $maxUnit }}"
                        aria-label="{{ $u->unit }}: {{ $u->jumlah }} tempahan">
                        <div class="progress-fill" style="width:{{ round(($u->jumlah / $maxUnit) * 100) }}%"></div>
                    </div>
                </div>
                <div class="w-10 text-sm font-bold text-gray-700 text-right flex-shrink-0">
                    {{ $u->jumlah }}
                </div>
            </div>
            @endforeach
        </div>

        {{-- Baki unit (tersembunyi secara lalai) --}}
        @if($mengikutUnit->count() > 5)
        <div class="space-y-3 mt-3 hidden" id="unit-selebihnya">
            @foreach($mengikutUnit->skip(5) as $u)
            <div class="flex items-center gap-3">
                <div class="w-52 text-sm text-gray-600 truncate flex-shrink-0" title="{{ $u->unit }}">
                    {{ $u->unit }}
                </div>
                <div class="flex-1">
                    <div class="progress-bar"
                        role="progressbar"
                        aria-valuenow="{{ $u->jumlah }}"
                        aria-valuemin="0"
                        aria-valuemax="{{ $maxUnit }}"
                        aria-label="{{ $u->unit }}: {{ $u->jumlah }} tempahan">
                        <div class="progress-fill bg-gray-300" style="width:{{ round(($u->jumlah / $maxUnit) * 100) }}%"></div>
                    </div>
                </div>
                <div class="w-10 text-sm font-semibold text-gray-500 text-right flex-shrink-0">
                    {{ $u->jumlah }}
                </div>
            </div>
            @endforeach
        </div>

        <button type="button" id="btn-lihat-unit"
            class="mt-4 text-xs font-semibold text-amber-600 hover:text-amber-700 flex items-center gap-1 transition"
            aria-expanded="false"
            aria-controls="unit-selebihnya"
            <i class="fa-solid fa-chevron-down text-xs" id="icon-chevron-unit" aria-hidden="true"></i>
            <span id="teks-btn-unit">Lihat {{ $mengikutUnit->count() - 5 }} unit lagi</span>
        </button>
        @endif
    </div>
</section>
@endif

{{-- ══════════════════════════════════════════ --}}
{{-- Item 1: TOP 10 PEMOHON (privacy-aware)    --}}
{{-- ══════════════════════════════════════════ --}}
@if(isset($top10Pengguna) && $top10Pengguna->count() > 0)
<section class="bg-white rounded-xl shadow-sm overflow-hidden mb-6" aria-labelledby="heading-top10">

    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3 flex-wrap">
        <h2 id="heading-top10" class="font-bold text-gray-800 flex items-center gap-2">
            <i class="fa-solid fa-trophy text-amber-400" aria-hidden="true"></i>
            Top 10 Pemohon Terbanyak
            <span class="text-sm font-normal text-gray-400 ml-1">({{ $tahun }})</span>
        </h2>
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400 bg-gray-100 px-3 py-1 rounded-full">Semua status tempahan</span>
            {{-- Item 1: Badge privasi untuk Urus Setia --}}
            @if(!$isPentadbir)
            <span class="text-xs bg-blue-50 text-blue-600 border border-blue-200 px-2 py-0.5 rounded-full flex items-center gap-1">
                <i class="fa-solid fa-eye-slash text-xs" aria-hidden="true"></i> Nama disamarkan
            </span>
            @endif
        </div>
    </div>

    @php
        $avatarColors = ['#f59e0b','#3b82f6','#10b981','#8b5cf6','#ef4444','#06b6d4','#ec4899','#84cc16','#f97316','#6366f1'];
        $maxJumlah    = $top10Pengguna->first()->jumlah ?: 1;
        $medalConfig  = [
            1 => ['bg' => '#fef3c7', 'border' => '#fcd34d', 'text' => '#b45309'],
            2 => ['bg' => '#f1f5f9', 'border' => '#cbd5e1', 'text' => '#475569'],
            3 => ['bg' => '#fff7ed', 'border' => '#fdba74', 'text' => '#c2410c'],
        ];
    @endphp

    <div class="divide-y divide-gray-50">
        @foreach($top10Pengguna as $i => $p)
        @php
            $rank = $i + 1;
            $pct  = round(($p->jumlah / $maxJumlah) * 100);
            $med  = $medalConfig[$rank] ?? null;

            // Item 1: Kira inisial untuk paparan privasi (Urus Setia)
            $perkataanNama = collect(explode(' ', trim($p->name)))->filter();
            $initials = $perkataanNama->take(2)->map(fn($w) => strtoupper($w[0]))->implode('');

            // Format nama disamarkan: "S*** B*** A***" (huruf pertama + bintang)
            $namaDisamarkan = $perkataanNama
                ->map(fn($w) => strtoupper($w[0]) . str_repeat('*', min(3, strlen($w) - 1)))
                ->implode(' ');
        @endphp
        <div class="flex items-center gap-4 px-6 py-3
            {{ $rank <= 3 ? 'hover:bg-amber-50/40' : 'hover:bg-gray-50' }} transition-colors">

            {{-- Ranking --}}
            <div class="w-9 flex-shrink-0 flex justify-center">
                @if($med)
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2"
                     style="background:{{ $med['bg'] }}; border-color:{{ $med['border'] }}; color:{{ $med['text'] }}">
                    {{ $rank }}
                </div>
                @else
                <span class="text-sm font-bold text-gray-300 tabular-nums">{{ $rank }}</span>
                @endif
            </div>

            {{-- Avatar --}}
            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-sm"
                 style="background:{{ $avatarColors[$i % count($avatarColors)] }}"
                 aria-hidden="true">
                {{ $initials }}
            </div>

            {{-- Item 1: Nama — penuh untuk Pentadbir, disamarkan untuk Urus Setia --}}
            <div class="flex-1 min-w-0">
                <div class="font-semibold text-gray-800 text-sm leading-snug truncate">
                    @if($isPentadbir)
                        {{ $p->name }}
                    @else
                        {{ $namaDisamarkan }}
                    @endif
                </div>
                <div class="text-xs text-gray-400 truncate" title="{{ $p->jabatan }}">
                    {{ $p->jabatan ?? '—' }}
                </div>
            </div>

            {{-- Breakdown status --}}
            <div class="hidden sm:flex items-center gap-4 flex-shrink-0 text-right">
                <div>
                    <div class="text-xs text-gray-400">Diluluskan</div>
                    <div class="text-sm font-semibold text-green-600">{{ $p->jumlah_diluluskan }}</div>
                </div>
                @if($p->jumlah_ditolak > 0)
                <div>
                    <div class="text-xs text-gray-400">Ditolak</div>
                    <div class="text-sm font-semibold text-red-400">{{ $p->jumlah_ditolak }}</div>
                </div>
                @endif
            </div>

            {{-- Jumlah --}}
            <div class="flex-shrink-0 text-right w-16">
                <div class="text-xl font-extrabold {{ $rank === 1 ? 'text-amber-500' : 'text-gray-700' }} tabular-nums leading-none">
                    {{ $p->jumlah }}
                </div>
                <div class="text-xs text-gray-400">tempahan</div>
            </div>

            {{-- Bar relatif --}}
            <div class="w-24 flex-shrink-0 hidden md:block">
                <div class="h-2 bg-gray-100 rounded-full overflow-hidden"
                     role="progressbar"
                     aria-valuenow="{{ $p->jumlah }}"
                     aria-valuemin="0"
                     aria-valuemax="{{ $maxJumlah }}"
                     aria-label="{{ $rank }}. {{ $isPentadbir ? $p->name : $namaDisamarkan }}: {{ $p->jumlah }} tempahan">
                    <div class="h-full rounded-full transition-all duration-500"
                         style="width:{{ $pct }}%; background:{{ $rank === 1 ? '#f59e0b' : ($rank === 2 ? '#94a3b8' : ($rank === 3 ? '#f97316' : '#3b82f6')) }}">
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between flex-wrap gap-2">
        <p class="text-xs text-gray-400">
            <i class="fa-solid fa-circle-info mr-1" aria-hidden="true"></i>
            Kiraan berdasarkan semua tempahan (diluluskan + ditolak) bagi tahun {{ $tahun }}.
        </p>
        @if(!$isPentadbir)
        <p class="text-xs text-blue-500">
            <i class="fa-solid fa-lock mr-1" aria-hidden="true"></i>
            Nama penuh hanya kelihatan kepada Pentadbir Sistem.
        </p>
        @endif
    </div>
</section>
@endif

{{-- ══════════════════════════════════════════ --}}
{{-- RINGKASAN PENGGUNAAN BILIK                 --}}
{{-- ══════════════════════════════════════════ --}}
<section class="bg-white rounded-xl shadow-sm overflow-hidden" aria-labelledby="heading-ringkasan-bilik">
    <div class="p-6 border-b border-gray-100">
        <h2 id="heading-ringkasan-bilik" class="font-bold text-gray-800">
            Ringkasan Penggunaan Bilik
            <span class="text-sm font-normal text-gray-400 ml-1">({{ $tahun }})</span>
        </h2>
        <p class="text-xs text-gray-400 mt-0.5">
            Peratus dikira berdasarkan jumlah sesi maksimum dalam tahun {{ $tahun }}
            ({{ \Carbon\Carbon::createFromDate($tahun, 1, 1)->isLeapYear() ? 366 : 365 }} hari × 2 sesi sehari)
        </p>
    </div>
    <table class="table w-full" aria-describedby="keterangan-jadual-bilik">
        <caption id="keterangan-jadual-bilik" class="sr-only">
            Ringkasan statistik penggunaan setiap bilik mesyuarat bagi tahun {{ $tahun }}
        </caption>
        <thead class="table-header">
            <tr>
                <th scope="col">Bilik</th>
                <th scope="col">Kapasiti</th>
                <th scope="col">Jumlah Sesi</th>
                <th scope="col">Kadar Penggunaan ({{ $tahun }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bilik as $b)
            @php
                $pct = min($b['peratusan'], 100);
                $barClr = $pct >= 70 ? 'bg-red-500' : ($pct >= 40 ? 'bg-amber-400' : 'bg-green-500');
                $labelGuna = $pct >= 70 ? 'Tinggi' : ($pct >= 40 ? 'Sederhana' : 'Rendah');
                $labelClr  = $pct >= 70 ? 'bg-red-100 text-red-700' : ($pct >= 40 ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700');
            @endphp
            <tr>
                <td class="font-semibold">{{ $b['nama'] }}</td>
                <td>{{ $b['kapasiti'] }} orang</td>
                <td class="tabular-nums">{{ $b['jumlah_tempahan'] }}</td>
                <td>
                    <div class="flex items-center gap-2">
                        <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden"
                            role="progressbar"
                            aria-valuenow="{{ $pct }}"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-label="{{ $b['nama'] }}: {{ $pct }}% digunakan">
                            <div class="{{ $barClr }} h-2 rounded-full" style="width:{{ $pct }}%"></div>
                        </div>
                        <span class="text-sm font-semibold w-9 text-right tabular-nums">{{ $pct }}%</span>
                        <span class="text-xs font-semibold px-1.5 py-0.5 rounded {{ $labelClr }}">{{ $labelGuna }}</span>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center py-8 text-gray-400">Tiada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</section>

@endif {{-- end @else (bukan staf) --}}

@endsection

@push('scripts')
<script nonce="{{ $cspNonce }}">
const bulanLabel = ['Jan','Feb','Mac','Apr','Mei','Jun','Jul','Ogos','Sep','Okt','Nov','Dis'];
const dataBulan  = @json($dataBulan);

new Chart(document.getElementById('chartBulan'), {
    type: 'bar',
    data: {
        labels: bulanLabel,
        datasets: [{
            label: 'Tempahan Diluluskan',
            data: dataBulan,
            backgroundColor: 'rgba(245,158,11,0.8)',
            borderColor: '#f59e0b',
            borderWidth: 1,
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false }, tooltip: { enabled: true } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});

@if(!$mengikutKategori->isEmpty())
new Chart(document.getElementById('chartKategori'), {
    type: 'doughnut',
    data: {
        labels: @json($mengikutKategori->pluck('kategori')),
        datasets: [{
            data: @json($mengikutKategori->pluck('jumlah')),
            backgroundColor: ['#f59e0b','#3b82f6','#10b981','#8b5cf6','#ef4444','#06b6d4'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' }, tooltip: { enabled: true } }
    }
});
@endif

// ── Wire event listeners (CSP-safe) ──────────────────────────────
document.getElementById('pilih-tahun-staf')?.addEventListener('change', function() { this.form.submit(); });
document.getElementById('pilih-tahun')?.addEventListener('change', function() { this.form.submit(); });
document.getElementById('btn-lihat-unit')?.addEventListener('click', toggleUnit);

// Item 3: Toggle collapsible unit
function toggleUnit() {
    const selebihnya = document.getElementById('unit-selebihnya');
    const btn        = document.getElementById('btn-lihat-unit');
    const icon       = document.getElementById('icon-chevron-unit');
    const teks       = document.getElementById('teks-btn-unit');
    const jumlahBaki = {{ isset($mengikutUnit) ? max(0, $mengikutUnit->count() - 5) : 0 }};

    const terbuka = !selebihnya.classList.contains('hidden');
    selebihnya.classList.toggle('hidden', terbuka);
    icon.classList.toggle('fa-chevron-down', terbuka);
    icon.classList.toggle('fa-chevron-up', !terbuka);
    btn.setAttribute('aria-expanded', !terbuka);
    teks.textContent = terbuka
        ? `Lihat ${jumlahBaki} unit lagi`
        : 'Sembunyikan';
}
</script>
@endpush
