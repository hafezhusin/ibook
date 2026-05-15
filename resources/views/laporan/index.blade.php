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
    <form method="GET" class="flex items-center gap-3">
        <select name="tahun" class="form-input w-auto text-sm" onchange="this.form.submit()">
            @foreach($senaraiTahun as $t)
            <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Chart Tempahan Mengikut Bulan --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="font-bold text-gray-800 mb-5">Tempahan Mengikut Bulan</h2>
        <canvas id="chartBulan" height="200"></canvas>
    </div>

    {{-- Chart Tempahan Mengikut Kategori --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="font-bold text-gray-800 mb-5">Tempahan Mengikut Kategori</h2>
        <canvas id="chartKategori" height="200"></canvas>
    </div>
</div>

{{-- Ringkasan Penggunaan Bilik --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="p-6 border-b border-gray-100">
        <h2 class="font-bold text-gray-800">Ringkasan Penggunaan Bilik ({{ $tahun }})</h2>
    </div>
    <table class="table w-full">
        <thead class="table-header">
            <tr>
                <th>Bilik</th>
                <th>Kapasiti</th>
                <th>Jumlah Tempahan</th>
                <th>Jam Digunakan</th>
                <th>% Penggunaan</th>
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
                        <div class="progress-bar flex-1">
                            <div class="progress-fill" style="width:{{ min($b['peratusan'], 100) }}%"></div>
                        </div>
                        <span class="text-sm font-semibold w-10 text-right">{{ $b['peratusan'] }}%</span>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-8 text-gray-400">Tiada data</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
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
        plugins: { legend: { display: false } },
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
            legend: { position: 'bottom' }
        }
    }
});
</script>
@endpush
