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
        <label for="pilih-tahun" class="sr-only">Pilih tahun laporan</label>
        <select id="pilih-tahun" name="tahun" class="form-input w-auto text-sm"
            onchange="this.form.submit()" aria-label="Tahun laporan">
            @foreach($senaraiTahun as $t)
            <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
    </form>
</div>

{{-- Kad Ringkasan Unit --}}
<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
            style="background:#dcfce7">
            <i class="fa-solid fa-circle-check text-xl" style="color:#16a34a" aria-hidden="true"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $totalDiluluskan }}</p>
            <p class="text-sm text-gray-500">Jumlah Tempahan</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
            style="background:#fee2e2">
            <i class="fa-solid fa-circle-xmark text-xl" style="color:#dc2626" aria-hidden="true"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $totalDitolak }}</p>
            <p class="text-sm text-gray-500">Dibatalkan</p>
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

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Laporan</h1>
        <p class="text-gray-500 text-sm mt-1">Ringkasan penggunaan bilik dan tempahan</p>
    </div>
    <form method="GET" aria-label="Tapis laporan mengikut tahun">
        <label for="pilih-tahun" class="sr-only">Pilih tahun laporan</label>
        <select id="pilih-tahun" name="tahun" class="form-input w-auto text-sm"
            onchange="this.form.submit()" aria-label="Tahun laporan">
            @foreach($senaraiTahun as $t)
            <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    {{-- Graf Tempahan Mengikut Bulan --}}
    <section class="bg-white rounded-xl shadow-sm p-6" aria-labelledby="heading-graf-bulan">
        <h2 id="heading-graf-bulan" class="font-bold text-gray-800 mb-5">
            Tempahan Mengikut Bulan — {{ $tahun }}
        </h2>
        <canvas id="chartBulan" height="200"
            role="img"
            aria-label="Graf bar: bilangan tempahan diluluskan bagi setiap bulan dalam tahun {{ $tahun }}. Lihat jadual di bawah untuk data lengkap.">
        </canvas>
    </section>

    {{-- Graf Tempahan Mengikut Kategori --}}
    <section class="bg-white rounded-xl shadow-sm p-6" aria-labelledby="heading-graf-kategori">
        <h2 id="heading-graf-kategori" class="font-bold text-gray-800 mb-5">
            Tempahan Mengikut Kategori — {{ $tahun }}
        </h2>
        <canvas id="chartKategori" height="200"
            role="img"
            aria-label="Graf donat: taburan tempahan mengikut kategori mesyuarat bagi tahun {{ $tahun }}. Lihat jadual di bawah untuk data lengkap.">
        </canvas>
    </section>
</div>

{{-- Statistik Mengikut Unit --}}
@if(isset($mengikutUnit) && $mengikutUnit->count() > 0)
<section class="bg-white rounded-xl shadow-sm overflow-hidden mb-6" aria-labelledby="heading-unit">
    <div class="p-6 border-b border-gray-100">
        <h2 id="heading-unit" class="font-bold text-gray-800">
            Tempahan Diluluskan Mengikut Unit ({{ $tahun }})
        </h2>
    </div>
    <div class="p-6">
        @php $maxUnit = $mengikutUnit->max('jumlah') ?: 1; @endphp
        <div class="space-y-3">
            @foreach($mengikutUnit as $u)
            <div class="flex items-center gap-3">
                <div class="w-48 text-sm text-gray-700 truncate flex-shrink-0" title="{{ $u->unit }}">
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
                <div class="w-8 text-sm font-bold text-gray-700 text-right flex-shrink-0">
                    {{ $u->jumlah }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Top 10 Pemohon Terbanyak --}}
@if(isset($top10Pengguna) && $top10Pengguna->count() > 0)
<section class="bg-white rounded-xl shadow-sm overflow-hidden mb-6" aria-labelledby="heading-top10">

    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 id="heading-top10" class="font-bold text-gray-800 flex items-center gap-2">
            <i class="fa-solid fa-trophy text-amber-400" aria-hidden="true"></i>
            Top 10 Pemohon Terbanyak
            <span class="text-sm font-normal text-gray-400 ml-1">({{ $tahun }})</span>
        </h2>
        <span class="text-xs text-gray-400 bg-gray-100 px-3 py-1 rounded-full">Semua status tempahan</span>
    </div>

    @php
        $avatarColors = ['#f59e0b','#3b82f6','#10b981','#8b5cf6','#ef4444','#06b6d4','#ec4899','#84cc16','#f97316','#6366f1'];
        $maxJumlah    = $top10Pengguna->first()->jumlah ?: 1;

        $medalConfig = [
            1 => ['bg' => '#fef3c7', 'border' => '#fcd34d', 'text' => '#b45309', 'icon' => 'fa-trophy',  'iconColor' => '#f59e0b'],
            2 => ['bg' => '#f1f5f9', 'border' => '#cbd5e1', 'text' => '#475569', 'icon' => 'fa-medal',   'iconColor' => '#94a3b8'],
            3 => ['bg' => '#fff7ed', 'border' => '#fdba74', 'text' => '#c2410c', 'icon' => 'fa-medal',   'iconColor' => '#f97316'],
        ];
    @endphp

    <div class="divide-y divide-gray-50">
        @foreach($top10Pengguna as $i => $p)
        @php
            $rank = $i + 1;
            $pct  = round(($p->jumlah / $maxJumlah) * 100);
            $med  = $medalConfig[$rank] ?? null;
            $initials = collect(explode(' ', $p->name))
                          ->filter()->take(2)
                          ->map(fn($w) => strtoupper($w[0]))
                          ->implode('');
        @endphp
        <div class="flex items-center gap-4 px-6 py-3 {{ $rank <= 3 ? 'hover:bg-amber-50/40' : 'hover:bg-gray-50' }} transition-colors">

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

            {{-- Nama + unit --}}
            <div class="flex-1 min-w-0">
                <div class="font-semibold text-gray-800 text-sm leading-snug truncate">{{ $p->name }}</div>
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
                @if($p->jumlah_menunggu > 0)
                <div>
                    <div class="text-xs text-gray-400">Menunggu</div>
                    <div class="text-sm font-semibold text-amber-500">{{ $p->jumlah_menunggu }}</div>
                </div>
                @endif
                @if($p->jumlah_ditolak > 0)
                <div>
                    <div class="text-xs text-gray-400">Ditolak</div>
                    <div class="text-sm font-semibold text-red-400">{{ $p->jumlah_ditolak }}</div>
                </div>
                @endif
            </div>

            {{-- Jumlah besar --}}
            <div class="flex-shrink-0 text-right w-16">
                <div class="text-xl font-extrabold {{ $rank === 1 ? 'text-amber-500' : 'text-gray-700' }} tabular-nums leading-none">
                    {{ $p->jumlah }}
                </div>
                <div class="text-xs text-gray-400">tempahan</div>
            </div>

            {{-- Bar relatif --}}
            <div class="w-28 flex-shrink-0 hidden md:block">
                <div class="h-2 bg-gray-100 rounded-full overflow-hidden"
                     role="progressbar"
                     aria-valuenow="{{ $p->jumlah }}"
                     aria-valuemin="0"
                     aria-valuemax="{{ $maxJumlah }}"
                     aria-label="{{ $p->name }}: {{ $p->jumlah }} tempahan">
                    <div class="h-full rounded-full transition-all duration-500"
                         style="width:{{ $pct }}%; background:{{ $rank === 1 ? '#f59e0b' : ($rank === 2 ? '#94a3b8' : ($rank === 3 ? '#f97316' : '#3b82f6')) }}">
                    </div>
                </div>
            </div>

        </div>
        @endforeach
    </div>

    <div class="px-6 py-3 bg-gray-50 border-t border-gray-100">
        <p class="text-xs text-gray-400">
            <i class="fa-solid fa-circle-info mr-1" aria-hidden="true"></i>
            Kiraan berdasarkan semua tempahan (diluluskan + menunggu + ditolak) bagi tahun {{ $tahun }}.
        </p>
    </div>
</section>
@endif

{{-- Ringkasan Penggunaan Bilik --}}
<section class="bg-white rounded-xl shadow-sm overflow-hidden" aria-labelledby="heading-ringkasan-bilik">
    <div class="p-6 border-b border-gray-100">
        <h2 id="heading-ringkasan-bilik" class="font-bold text-gray-800">
            Ringkasan Penggunaan Bilik ({{ $tahun }})
        </h2>
    </div>
    <table class="table w-full" aria-describedby="keterangan-jadual-bilik">
        <caption id="keterangan-jadual-bilik" class="sr-only">
            Ringkasan statistik penggunaan setiap bilik mesyuarat bagi tahun {{ $tahun }}
        </caption>
        <thead class="table-header">
            <tr>
                <th scope="col">Bilik</th>
                <th scope="col">Kapasiti</th>
                <th scope="col">Jumlah Tempahan</th>
                <th scope="col">% Penggunaan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bilik as $b)
            <tr>
                <td class="font-semibold">{{ $b['nama'] }}</td>
                <td>{{ $b['kapasiti'] }} orang</td>
                <td>{{ $b['jumlah_tempahan'] }}</td>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="progress-bar flex-1"
                            role="progressbar"
                            aria-valuenow="{{ min($b['peratusan'], 100) }}"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-label="{{ $b['nama'] }}: {{ $b['peratusan'] }}% digunakan">
                            <div class="progress-fill" style="width:{{ min($b['peratusan'], 100) }}%"></div>
                        </div>
                        <span class="text-sm font-semibold w-10 text-right">{{ $b['peratusan'] }}%</span>
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

@endif
@endsection

@push('scripts')
<script>
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
        plugins: {
            legend: { display: false },
            tooltip: { enabled: true }
        },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});

@if(!$mengikutKategori->isEmpty())
const kategoriLabel = @json($mengikutKategori->pluck('kategori'));
const kategoriData  = @json($mengikutKategori->pluck('jumlah'));
const colors = ['#f59e0b','#3b82f6','#10b981','#8b5cf6','#ef4444','#06b6d4'];

new Chart(document.getElementById('chartKategori'), {
    type: 'doughnut',
    data: {
        labels: kategoriLabel,
        datasets: [{
            data: kategoriData,
            backgroundColor: colors,
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            tooltip: { enabled: true }
        }
    }
});
@endif
</script>
@endpush
