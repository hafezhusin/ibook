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
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
            style="background:#dcfce7">
            <i class="fa-solid fa-circle-check text-xl" style="color:#16a34a" aria-hidden="true"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $totalDiluluskan }}</p>
            <p class="text-sm text-gray-500">Diluluskan</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
            style="background:#fef3c7">
            <i class="fa-solid fa-clock text-xl" style="color:#d97706" aria-hidden="true"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $totalMenunggu }}</p>
            <p class="text-sm text-gray-500">Menunggu Kelulusan</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
            style="background:#fee2e2">
            <i class="fa-solid fa-circle-xmark text-xl" style="color:#dc2626" aria-hidden="true"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $totalDitolak }}</p>
            <p class="text-sm text-gray-500">Ditolak</p>
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
                <th scope="col">Jam Digunakan</th>
                <th scope="col">% Penggunaan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bilik as $b)
            <tr>
                <td class="font-semibold">{{ $b['nama'] }}</td>
                <td>{{ $b['kapasiti'] }} orang</td>
                <td>{{ $b['jumlah_tempahan'] }}</td>
                <td>{{ $b['jam_digunakan'] }} jam</td>
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
                <td colspan="5" class="text-center py-8 text-gray-400">Tiada data</td>
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
