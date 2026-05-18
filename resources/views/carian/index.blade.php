@extends('layouts.app')

@section('title', 'Carian')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Keputusan Carian</h1>
    @if(strlen(trim($q)) >= 2)
    <p class="text-gray-500 mt-1">Menunjukkan keputusan untuk: <strong class="text-gray-800">"{{ $q }}"</strong></p>
    @else
    <p class="text-gray-500 mt-1">Masukkan sekurang-kurangnya 2 aksara untuk mencari.</p>
    @endif
</div>

{{-- Search bar (larger, focused) --}}
<form method="GET" action="{{ route('carian') }}" class="mb-8" role="search">
    <div class="flex gap-3 max-w-xl">
        <div class="relative flex-1">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" aria-hidden="true"></i>
            <input type="search" name="q" value="{{ $q }}"
                autofocus
                placeholder="Cari tempahan, bilik atau pengguna..."
                class="form-input pl-10"
                aria-label="Kata carian">
        </div>
        <button type="submit" class="btn-primary">Cari</button>
    </div>
</form>

@if(strlen(trim($q)) >= 2)

@php
    $adaHasil = $tempahan->count() || $bilik->count() || $pengguna->count();
@endphp

@if(!$adaHasil)
<div class="bg-white rounded-xl shadow-sm p-12 text-center text-gray-400">
    <i class="fa-solid fa-magnifying-glass text-4xl mb-3" aria-hidden="true"></i>
    <p class="text-lg font-medium">Tiada keputusan dijumpai</p>
    <p class="text-sm mt-1">Cuba kata carian lain atau semak ejaan.</p>
</div>
@endif

{{-- ── Keputusan: Tempahan ─────────────────────────────────────── --}}
@if($tempahan->count())
<section class="mb-8" aria-labelledby="heading-carian-tempahan">
    <h2 id="heading-carian-tempahan" class="text-base font-bold text-gray-600 uppercase tracking-wide mb-3 flex items-center gap-2">
        <i class="fa-solid fa-calendar-check text-amber-400" aria-hidden="true"></i>
        Tempahan
        <span class="text-xs font-normal text-gray-400 normal-case tracking-normal">({{ $tempahan->count() }} keputusan)</span>
    </h2>
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="table w-full">
            <thead class="table-header">
                <tr>
                    <th scope="col">Nama Mesyuarat</th>
                    <th scope="col">Bilik</th>
                    <th scope="col">Tarikh & Sesi</th>
                    <th scope="col">Status</th>
                    <th scope="col"><span class="sr-only">Tindakan</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach($tempahan as $t)
                <tr>
                    <td>
                        <div class="font-semibold text-gray-800">{{ $t->nama_mesyuarat }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $t->nama_pengerusi }}</div>
                    </td>
                    <td>{{ $t->bilik->nama ?? '—' }}</td>
                    <td>
                        <div>{{ \Carbon\Carbon::parse($t->tarikh)->format('d M Y') }}</div>
                        <div class="text-xs text-gray-500">
                            {{ $t->sesi === 'pagi' ? 'Sesi Pagi' : 'Sesi Petang' }}
                            · {{ substr($t->masa_mula, 0, 5) }}–{{ substr($t->masa_tamat, 0, 5) }}
                        </div>
                    </td>
                    <td>
                        @if($t->status === 'diluluskan')
                            <span class="badge-lulus">Diluluskan</span>
                        @else
                            <span class="badge-tolak">Ditolak</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('tempahan.show', $t) }}"
                           class="text-amber-500 hover:text-amber-600 text-sm font-medium"
                           aria-label="Lihat butiran tempahan {{ $t->nama_mesyuarat }}">
                            Lihat &rarr;
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif

{{-- ── Keputusan: Bilik (Pentadbir sahaja) ────────────────────── --}}
@if($bilik->count())
<section class="mb-8" aria-labelledby="heading-carian-bilik">
    <h2 id="heading-carian-bilik" class="text-base font-bold text-gray-600 uppercase tracking-wide mb-3 flex items-center gap-2">
        <i class="fa-solid fa-door-open text-amber-400" aria-hidden="true"></i>
        Bilik Mesyuarat
        <span class="text-xs font-normal text-gray-400 normal-case tracking-normal">({{ $bilik->count() }} keputusan)</span>
    </h2>
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="table w-full">
            <thead class="table-header">
                <tr>
                    <th scope="col">Nama Bilik</th>
                    <th scope="col">Lokasi</th>
                    <th scope="col">Kapasiti</th>
                    <th scope="col">Status</th>
                    <th scope="col"><span class="sr-only">Tindakan</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach($bilik as $b)
                <tr>
                    <td class="font-semibold text-gray-800">{{ $b->nama }}</td>
                    <td class="text-gray-500">{{ $b->lokasi }}</td>
                    <td>{{ $b->kapasiti }} orang</td>
                    <td>
                        @if($b->status === 'aktif')
                            <span class="badge-lulus">Aktif</span>
                        @else
                            <span class="badge-tolak">Tidak Aktif</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('bilik.edit', $b) }}"
                           class="text-amber-500 hover:text-amber-600 text-sm font-medium"
                           aria-label="Edit bilik {{ $b->nama }}">
                            Edit &rarr;
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif

{{-- ── Keputusan: Pengguna (Pentadbir sahaja) ─────────────────── --}}
@if($pengguna->count())
<section class="mb-8" aria-labelledby="heading-carian-pengguna">
    <h2 id="heading-carian-pengguna" class="text-base font-bold text-gray-600 uppercase tracking-wide mb-3 flex items-center gap-2">
        <i class="fa-solid fa-users text-amber-400" aria-hidden="true"></i>
        Pengguna
        <span class="text-xs font-normal text-gray-400 normal-case tracking-normal">({{ $pengguna->count() }} keputusan)</span>
    </h2>
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="table w-full">
            <thead class="table-header">
                <tr>
                    <th scope="col">Nama</th>
                    <th scope="col">E-mel</th>
                    <th scope="col">Jabatan / Unit</th>
                    <th scope="col">Peranan</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pengguna as $p)
                <tr>
                    <td>
                        <div class="font-semibold text-gray-800 flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                 style="background:var(--accent)" aria-hidden="true">
                                {{ strtoupper(substr($p->name, 0, 1)) }}
                            </div>
                            {{ $p->name }}
                        </div>
                    </td>
                    <td class="text-gray-500">{{ $p->email }}</td>
                    <td class="text-gray-500">{{ $p->jabatan ?? '—' }}</td>
                    <td>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full
                            @if($p->peranan === 'pentadbir_sistem') bg-purple-100 text-purple-700
                            @elseif($p->peranan === 'urus_setia') bg-blue-100 text-blue-700
                            @else bg-gray-100 text-gray-600 @endif">
                            {{ $p->label_peranan }}
                        </span>
                    </td>
                    <td>
                        @if($p->is_aktif)
                            <span class="badge-lulus">Aktif</span>
                        @else
                            <span class="badge-tolak">Tidak Aktif</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif

@endif {{-- end: strlen >= 2 --}}

@endsection
