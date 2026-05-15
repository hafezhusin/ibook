@extends('layouts.app')

@section('title', 'Papan Pemuka')

@section('content')
@php
    $namaBulan = ['', 'Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'];
@endphp

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Selamat Datang, {{ auth()->user()->name }}</h1>
    @if(auth()->user()->bolehLuluskan() && $menungguKelulusan > 0)
    <p class="text-gray-500 mt-1">Anda mempunyai <span class="font-semibold text-amber-600">{{ $menungguKelulusan }} permohonan</span> menunggu kelulusan.</p>
    @else
    <p class="text-gray-500 mt-1">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}</p>
    @endif
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-7">
    <div class="stat-card">
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:#fef3c7">
                <i class="fa-solid fa-calendar-check text-amber-500 text-lg"></i>
            </div>
            <span class="text-green-500 text-sm font-semibold">↑ Aktif</span>
        </div>
        <div class="mt-4">
            <div class="text-3xl font-bold text-gray-800">{{ $jumlahTempahan }}</div>
            <div class="text-sm text-gray-500 mt-1">Jumlah Tempahan Bulan Ini</div>
            <div class="text-xs text-gray-400">{{ $namaBulan[$bulanIni] }} {{ $tahunIni }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:#fef3c7">
                <i class="fa-solid fa-clock text-amber-500 text-lg"></i>
            </div>
            @if($menungguKelulusan > 0)
            <span class="text-amber-500 text-sm font-semibold">{{ $menungguKelulusan }} baru</span>
            @endif
        </div>
        <div class="mt-4">
            <div class="text-3xl font-bold text-gray-800">{{ $menungguKelulusan }}</div>
            <div class="text-sm text-gray-500 mt-1">Menunggu Kelulusan</div>
            <div class="text-xs text-gray-400">Perlu tindakan</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:#fef3c7">
                <i class="fa-solid fa-users text-amber-500 text-lg"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="text-3xl font-bold text-gray-800">{{ $mesyuaratHariIni }}</div>
            <div class="text-sm text-gray-500 mt-1">Mesyuarat Hari Ini</div>
            <div class="text-xs text-gray-400">{{ \Carbon\Carbon::today()->isoFormat('D MMMM YYYY') }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:#fef3c7">
                <i class="fa-solid fa-chart-bar text-amber-500 text-lg"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="text-3xl font-bold text-gray-800">{{ $kadarPenggunaan }}%</div>
            <div class="text-sm text-gray-500 mt-1">Kadar Penggunaan Bilik</div>
            <div class="text-xs text-gray-400">Purata semua bilik</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Mesyuarat Akan Datang --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="font-bold text-gray-800 text-lg">Mesyuarat Akan Datang</h2>
                <p class="text-gray-400 text-sm">7 hari hadapan</p>
            </div>
            <a href="{{ route('kalendar') }}" class="text-amber-500 text-sm font-semibold hover:underline">Lihat Kalendar</a>
        </div>

        @forelse($mesyuaratAkanDatang as $m)
        <div class="flex gap-4 py-3 border-b border-gray-100 last:border-0">
            <div class="text-center min-w-[48px]">
                <div class="text-xs text-gray-400">{{ $m->tarikh->isoFormat('ddd') }}</div>
                <div class="text-2xl font-bold text-gray-700">{{ $m->tarikh->format('d') }}</div>
            </div>
            <div class="flex-1">
                <div class="font-semibold text-gray-800">{{ $m->nama_mesyuarat }}</div>
                <div class="text-sm text-gray-500">{{ $m->masa_label }} &middot; {{ $m->bilik->nama ?? '-' }}</div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500">{{ $m->bilangan_peserta }} peserta</span>
                @if($m->status === 'diluluskan')
                    <span class="badge-lulus">Diluluskan</span>
                @elseif($m->status === 'menunggu')
                    <span class="badge-menunggu">Menunggu</span>
                @else
                    <span class="badge-tolak">Ditolak</span>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-10 text-gray-400">
            <i class="fa-solid fa-calendar-xmark text-3xl mb-3"></i>
            <p>Tiada mesyuarat akan datang</p>
        </div>
        @endforelse
    </div>

    {{-- Right column --}}
    <div class="space-y-6">
        {{-- Menunggu Kelulusan --}}
        @if(auth()->user()->bolehLuluskan())
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-800">Menunggu Kelulusan</h2>
                <a href="{{ route('kelulusan') }}" class="text-amber-500 text-sm font-semibold hover:underline">Semua</a>
            </div>
            @forelse($menungguList as $m)
            <div class="py-2 border-b border-gray-100 last:border-0">
                <div class="font-semibold text-sm text-gray-800">{{ $m->nama_mesyuarat }}</div>
                <div class="text-xs text-gray-500">{{ $m->tarikh->format('d/m/Y') }} &middot; {{ $m->masa_label }}</div>
                <a href="{{ route('kelulusan') }}" class="text-xs text-amber-500 hover:underline">
                    <i class="fa-solid fa-eye mr-1"></i>Semak
                </a>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-4">Tiada permohonan menunggu</p>
            @endforelse
        </div>
        @endif

        {{-- Penggunaan Bilik --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="font-bold text-gray-800 mb-4">Penggunaan Bilik</h2>
            @foreach($penggunaanBilik as $b)
            <div class="mb-3">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-700">{{ $b['nama'] }}</span>
                    <span class="font-semibold text-gray-800">{{ $b['peratusan'] }}%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width:{{ $b['peratusan'] }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
