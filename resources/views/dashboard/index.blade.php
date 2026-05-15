@extends('layouts.app')

@section('title', 'Papan Pemuka')

@section('content')
@php
    $namaBulan = ['', 'Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'];
@endphp

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Selamat Datang, {{ auth()->user()->name }}</h1>
    @if(auth()->user()->bolehLuluskan() && $menungguKelulusan > 0)
    <p class="text-gray-500 mt-1">Anda mempunyai <strong class="font-semibold text-amber-600">{{ $menungguKelulusan }} permohonan</strong> menunggu kelulusan.</p>
    @else
    <p class="text-gray-500 mt-1">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}</p>
    @endif
</div>

{{-- Kad Statistik --}}
<section aria-labelledby="heading-statistik">
    <h2 id="heading-statistik" class="sr-only">Statistik Bulanan</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-7">

        <article class="stat-card" aria-label="Jumlah Tempahan Bulan Ini: {{ $jumlahTempahan }}">
            <div class="flex items-start justify-between">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:#fef3c7" aria-hidden="true">
                    <i class="fa-solid fa-calendar-check text-amber-500 text-lg" aria-hidden="true"></i>
                </div>
                <span class="text-green-500 text-sm font-semibold" aria-hidden="true">↑ Aktif</span>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-bold text-gray-800" aria-hidden="true">{{ $jumlahTempahan }}</div>
                <div class="text-sm text-gray-500 mt-1">Jumlah Tempahan Bulan Ini</div>
                <div class="text-xs text-gray-400">{{ $namaBulan[$bulanIni] }} {{ $tahunIni }}</div>
            </div>
        </article>

        <article class="stat-card" aria-label="Menunggu Kelulusan: {{ $menungguKelulusan }} permohonan">
            <div class="flex items-start justify-between">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:#fef3c7" aria-hidden="true">
                    <i class="fa-solid fa-clock text-amber-500 text-lg" aria-hidden="true"></i>
                </div>
                @if($menungguKelulusan > 0)
                <span class="text-amber-500 text-sm font-semibold" aria-hidden="true">{{ $menungguKelulusan }} baru</span>
                @endif
            </div>
            <div class="mt-4">
                <div class="text-3xl font-bold text-gray-800" aria-hidden="true">{{ $menungguKelulusan }}</div>
                <div class="text-sm text-gray-500 mt-1">Menunggu Kelulusan</div>
                <div class="text-xs text-gray-400">Perlu tindakan</div>
            </div>
        </article>

        <article class="stat-card" aria-label="Mesyuarat Hari Ini: {{ $mesyuaratHariIni }}">
            <div class="flex items-start justify-between">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:#fef3c7" aria-hidden="true">
                    <i class="fa-solid fa-users text-amber-500 text-lg" aria-hidden="true"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-bold text-gray-800" aria-hidden="true">{{ $mesyuaratHariIni }}</div>
                <div class="text-sm text-gray-500 mt-1">Mesyuarat Hari Ini</div>
                <div class="text-xs text-gray-400">{{ \Carbon\Carbon::today()->isoFormat('D MMMM YYYY') }}</div>
            </div>
        </article>

        <article class="stat-card" aria-label="Kadar Penggunaan Bilik: {{ $kadarPenggunaan }} peratus">
            <div class="flex items-start justify-between">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:#fef3c7" aria-hidden="true">
                    <i class="fa-solid fa-chart-bar text-amber-500 text-lg" aria-hidden="true"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-bold text-gray-800" aria-hidden="true">{{ $kadarPenggunaan }}%</div>
                <div class="text-sm text-gray-500 mt-1">Kadar Penggunaan Bilik</div>
                <div class="text-xs text-gray-400">Purata semua bilik</div>
            </div>
        </article>
    </div>
</section>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Mesyuarat Akan Datang --}}
    <section class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6" aria-labelledby="heading-akan-datang">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 id="heading-akan-datang" class="font-bold text-gray-800 text-lg">Mesyuarat Akan Datang</h2>
                <p class="text-gray-400 text-sm">7 hari hadapan</p>
            </div>
            <a href="{{ route('kalendar') }}" class="text-amber-500 text-sm font-semibold hover:underline">
                Lihat Kalendar<span class="sr-only"> — paparan penuh kalendar</span>
            </a>
        </div>

        @if($mesyuaratAkanDatang->isEmpty())
        <div class="text-center py-10 text-gray-400">
            <i class="fa-solid fa-calendar-xmark text-3xl mb-3" aria-hidden="true"></i>
            <p>Tiada mesyuarat akan datang</p>
        </div>
        @else
        <ul role="list" class="divide-y divide-gray-100">
            @foreach($mesyuaratAkanDatang as $m)
            <li class="flex gap-4 py-3">
                <div class="text-center min-w-[48px]" aria-hidden="true">
                    <div class="text-xs text-gray-400">{{ $m->tarikh->isoFormat('ddd') }}</div>
                    <div class="text-2xl font-bold text-gray-700">{{ $m->tarikh->format('d') }}</div>
                </div>
                <div class="flex-1">
                    <div class="font-semibold text-gray-800">{{ $m->nama_mesyuarat }}</div>
                    <div class="text-sm text-gray-500">
                        <time datetime="{{ $m->tarikh->format('Y-m-d') }}">{{ $m->tarikh->isoFormat('D MMMM YYYY') }}</time>
                        &middot; {{ $m->masa_label }} &middot; {{ $m->bilik->nama ?? '-' }}
                    </div>
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
            </li>
            @endforeach
        </ul>
        @endif
    </section>

    {{-- Right column --}}
    <div class="space-y-6">

        {{-- Menunggu Kelulusan --}}
        @if(auth()->user()->bolehLuluskan())
        <section class="bg-white rounded-xl shadow-sm p-6" aria-labelledby="heading-menunggu">
            <div class="flex items-center justify-between mb-4">
                <h2 id="heading-menunggu" class="font-bold text-gray-800">Menunggu Kelulusan</h2>
                <a href="{{ route('kelulusan') }}" class="text-amber-500 text-sm font-semibold hover:underline">
                    Semua<span class="sr-only"> permohonan menunggu kelulusan</span>
                </a>
            </div>
            @if($menungguList->isEmpty())
            <p class="text-sm text-gray-400 text-center py-4">Tiada permohonan menunggu</p>
            @else
            <ul role="list" class="divide-y divide-gray-100">
                @foreach($menungguList as $m)
                <li class="py-2">
                    <div class="font-semibold text-sm text-gray-800">{{ $m->nama_mesyuarat }}</div>
                    <div class="text-xs text-gray-500">
                        <time datetime="{{ $m->tarikh->format('Y-m-d') }}">{{ $m->tarikh->format('d/m/Y') }}</time>
                        &middot; {{ $m->masa_label }}
                    </div>
                    <a href="{{ route('kelulusan') }}" class="text-xs text-amber-500 hover:underline">
                        <i class="fa-solid fa-eye mr-1" aria-hidden="true"></i>Semak<span class="sr-only"> — {{ $m->nama_mesyuarat }}</span>
                    </a>
                </li>
                @endforeach
            </ul>
            @endif
        </section>
        @endif

        {{-- Penggunaan Bilik --}}
        <section class="bg-white rounded-xl shadow-sm p-6" aria-labelledby="heading-penggunaan">
            <h2 id="heading-penggunaan" class="font-bold text-gray-800 mb-4">Penggunaan Bilik</h2>
            <ul role="list" class="space-y-3">
                @foreach($penggunaanBilik as $b)
                <li>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-700">{{ $b['nama'] }}</span>
                        <span class="font-semibold text-gray-800">{{ $b['peratusan'] }}%</span>
                    </div>
                    <div class="progress-bar" role="progressbar"
                        aria-valuenow="{{ $b['peratusan'] }}"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        aria-label="{{ $b['nama'] }}: {{ $b['peratusan'] }}% digunakan">
                        <div class="progress-fill" style="width:{{ $b['peratusan'] }}%"></div>
                    </div>
                </li>
                @endforeach
            </ul>
        </section>
    </div>
</div>
@endsection
