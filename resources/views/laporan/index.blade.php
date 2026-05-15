@extends('layouts.app')

@section('title', 'Laporan')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Laporan</h1>
        <p class="text-gray-500 text-sm mt-1">Ringkasan penggunaan bilik dan tempahan</p>
    </div>
    <form method="GET" aria-label="Tapis laporan mengikut tahun">
        <label for="pilih-tahun" class="sr-only">Pilih tahun laporan</label>
        <select id="pilih-tahun" name="tahun" class="form-input w-auto text-sm" onchange="this.form.submit()" aria-label="Tahun laporan">
            @foreach($senaraiTahun as $t)
            <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    {{-- Graf Tempahan Mengikut Bulan --}}
    <section class="bg-white rounded-xl shadow-sm p-6" aria-labelledby="heading-graf-bulan">
        <h2 id="heading-graf-bulan" class="font-bold text-gray-800 mb-5">Tempahan Mengikut Bulan — {{ $tahun }}</h2>
        <canvas id="chartBulan" height="200"
            role="img"
            aria-label="Graf bar: bilangan tempahan diluluskan bagi setiap bulan dalam tahun {{ $tahun }}. Lihat jadual di bawah untuk data lengkap.">
        </canvas>
    </section>

    {{-- Graf Tempahan Mengikut Kategori --}}
    <section class="bg-white rounded-xl shadow-sm p-6" aria-labelledby="heading-graf-kategori">
        <h2 id="heading-graf-kategori" class="font-bold text-gray-800 mb-5">Tempahan Mengikut Kategori — {{ $tahun }}</h2>
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
@endsection

@push('scripts')
<script>
const bulanLabel = ['Jan','Feb','Mac','Apr','Mei','Jun','Jul','Ogos','Sep','Okt','Nov','Dis'];
const dataBulan = @json($dataBulan);

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

const kategoriLabel = @json($mengikutKategori->pluck('kategori'));
const kategoriData = @json($mengikutKategori->pluck('jumlah'));
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
</script>
@endpush
