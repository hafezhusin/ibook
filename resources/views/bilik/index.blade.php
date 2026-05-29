@extends('layouts.app')

@section('title', 'Bilik Mesyuarat')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Bilik Mesyuarat</h1>
        <p class="text-gray-500 text-sm mt-1">{{ $bilik->count() }} bilik berdaftar</p>
    </div>
    <a href="{{ route('bilik.create') }}" class="btn-primary">
        <i class="fa-solid fa-plus" aria-hidden="true"></i> Tambah Bilik
    </a>
</div>

<div class="mb-4 flex flex-wrap gap-3 items-center">
    {{-- Carian teks --}}
    <div class="relative">
        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm" aria-hidden="true"></i>
        <input type="search" id="carian-bilik"
            placeholder="Cari nama bilik..."
            class="form-input pl-9 text-sm w-full md:w-64"
            aria-label="Cari bilik mesyuarat">
    </div>

    {{-- Filter Bahagian --}}
    <div class="relative">
        <i class="fa-solid fa-building-columns absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none" aria-hidden="true"></i>
        <select id="filter-bahagian"
            class="form-input pl-9 text-sm pr-8 w-full md:w-56"
            aria-label="Tapis mengikut bahagian">
            <option value="">— Semua Bahagian —</option>
            @foreach($bahagian as $b)
            <option value="{{ $b->id }}">{{ $b->kod }} — {{ $b->nama }}</option>
            @endforeach
        </select>
    </div>

    {{-- Kiraan hasil --}}
    <span id="kiraan-bilik" class="text-sm text-gray-400 ml-auto"></span>
</div>

<section aria-labelledby="heading-bilik-senarai">
    <h2 id="heading-bilik-senarai" class="sr-only">Senarai Bilik Mesyuarat</h2>
    <div id="grid-bilik" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($bilik as $b)
        @php
            // Auto-pilih gambar berdasarkan jenis bilik jika tiada URL dikonfigurasi
            $nm = strtolower($b->nama);
            $gambarUrl = $b->gambar ?: null;
            if (!$gambarUrl) {
                if (str_contains($nm, 'lab') || str_contains($nm, 'ict') || str_contains($nm, 'komputer')) {
                    // Lab ICT / komputer
                    $gambarUrl = asset('images/bilik/lab-ict.jpg');
                } elseif ($b->kapasiti >= 50 || str_contains($nm, 'utama') || str_contains($nm, 'dewan') || str_contains($nm, 'auditorium')) {
                    // Bilik besar / utama
                    $gambarUrl = asset('images/bilik/meeting-besar.jpg');
                } elseif ($b->kapasiti <= 20 || str_contains($nm, 'perbincangan') || str_contains($nm, 'diskusi') || str_contains($nm, 'kecil')) {
                    // Bilik perbincangan / kecil
                    $gambarUrl = asset('images/bilik/meeting-kecil.jpg');
                } else {
                    // Bilik mesyuarat standard — selang-seli 2 gambar berbeza
                    $gambarUrl = ($b->id % 2 === 0)
                        ? asset('images/bilik/meeting-standard-1.jpg')
                        : asset('images/bilik/meeting-standard-2.jpg');
                }
            }
        @endphp
        <article class="bg-white rounded-xl shadow-sm overflow-hidden kad-bilik"
            data-nama="{{ strtolower($b->nama) }}"
            data-lokasi="{{ strtolower($b->lokasi ?? '') }}"
            data-bahagian-id="{{ $b->bahagian_id ?? '' }}"
            aria-labelledby="bilik-{{ $b->id }}">
            {{-- Gambar bilik --}}
            <div class="h-44 overflow-hidden relative group">
                <img src="{{ $gambarUrl }}"
                     alt="Gambar {{ $b->nama }}"
                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                     loading="lazy"
                     onerror="this.closest('.group').innerHTML='<div class=\'h-44 bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center\'><i class=\'fa-solid fa-door-open text-slate-300 text-5xl\'></i></div>'">
                {{-- Overlay lokasi di penjuru bawah --}}
                @if($b->lokasi)
                <div class="absolute bottom-0 left-0 right-0 px-3 py-2"
                     style="background: linear-gradient(to top, rgba(0,0,0,0.55) 0%, transparent 100%)">
                    <span class="text-white text-xs font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-location-dot text-amber-400 text-[10px]" aria-hidden="true"></i>
                        {{ $b->lokasi }}
                    </span>
                </div>
                @endif
            </div>
            <div class="p-5">
                <div class="flex items-start justify-between mb-2">
                    <h3 id="bilik-{{ $b->id }}" class="font-bold text-gray-800">{{ $b->nama }}</h3>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $b->isAktif() ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}"
                        role="status">
                        {{ $b->isAktif() ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </div>
                {{-- Badge bahagian --}}
                @if($b->bahagian)
                <div class="mb-2">
                    <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 font-medium">
                        <i class="fa-solid fa-building-columns text-[10px]" aria-hidden="true"></i>
                        {{ $b->bahagian->kod }}
                    </span>
                </div>
                @endif

                <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                    <i class="fa-solid fa-users text-amber-400" aria-hidden="true"></i>
                    <span>{{ $b->kapasiti }} orang</span>
                </div>

                @if($b->kemudahan && count($b->kemudahan) > 0)
                <ul role="list" class="flex flex-wrap gap-1 mb-3" aria-label="Kemudahan {{ $b->nama }}">
                    @foreach($b->kemudahan as $k)
                    <li class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                        <i class="fa-solid fa-check text-amber-400 mr-1" aria-hidden="true"></i>{{ $k }}
                    </li>
                    @endforeach
                </ul>
                @endif

                @php
                    $pct = $b->peratus_penggunaan ?? $b->penggunaan_bulan_ini;
                    if ($pct >= 80) {
                        $barClr = 'bg-red-500';
                        $badgeCls = 'bg-red-100 text-red-700';
                        $labelGuna = 'Permintaan Tinggi';
                    } elseif ($pct >= 50) {
                        $barClr = 'bg-amber-400';
                        $badgeCls = 'bg-amber-100 text-amber-700';
                        $labelGuna = 'Sederhana';
                    } else {
                        $barClr = 'bg-green-500';
                        $badgeCls = 'bg-green-100 text-green-700';
                        $labelGuna = 'Kurang Guna';
                    }
                @endphp
                <div class="mb-4">
                    <div class="flex justify-between items-center text-sm mb-1">
                        <span class="text-gray-500">Penggunaan bulan ini</span>
                        <span class="flex items-center gap-1.5">
                            <span class="font-semibold text-gray-700">{{ $pct }}%</span>
                            <span class="text-xs font-semibold px-1.5 py-0.5 rounded {{ $badgeCls }}">{{ $labelGuna }}</span>
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden"
                        role="progressbar"
                        aria-valuenow="{{ $pct }}"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        aria-label="{{ $b->nama }}: {{ $pct }}% digunakan bulan ini">
                        <div class="{{ $barClr }} h-2 rounded-full transition-all duration-500" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                @if($b->dikemaskini_oleh)
                <div class="text-xs text-gray-400 mb-3">
                    <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
                    Dikemaskini oleh <span class="font-medium">{{ $b->dikemaskini_oleh }}</span>
                    &middot; {{ $b->dikemaskini_pada?->diffForHumans() }}
                </div>
                @endif

                <div class="flex gap-2 pt-3 border-t border-gray-100">
                    <a href="{{ route('bilik.edit', $b) }}"
                        class="text-amber-500 text-sm font-semibold hover:underline flex items-center gap-1"
                        aria-label="Edit bilik — {{ $b->nama }}">
                        <i class="fa-solid fa-pen" aria-hidden="true"></i> Edit
                    </a>
                    <form method="POST" action="{{ route('bilik.destroy', $b) }}" class="ml-auto padam-bilik-form"
                        data-nama="{{ addslashes($b->nama) }}">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="text-red-400 text-sm hover:text-red-600"
                            aria-label="Padam bilik — {{ $b->nama }}">
                            <i class="fa-solid fa-trash" aria-hidden="true"></i> Padam
                        </button>
                    </form>
                </div>
            </div>
        </article>
        @empty
        <div class="col-span-3 text-center py-16 text-gray-400 bg-white rounded-xl">
            <i class="fa-solid fa-door-open text-5xl mb-4" aria-hidden="true"></i>
            <p>Tiada bilik mesyuarat berdaftar</p>
            <a href="{{ route('bilik.create') }}" class="btn-primary mt-4 inline-flex">Tambah Bilik</a>
        </div>
        @endforelse
    </div>
</section>

@push('scripts')
<script nonce="{{ $cspNonce }}">
function terapiFilter() {
    const carian    = (document.getElementById('carian-bilik').value || '').trim().toLowerCase();
    const bahagian  = (document.getElementById('filter-bahagian').value || '').trim();
    const kiraan    = document.getElementById('kiraan-bilik');
    let nampak = 0;

    document.querySelectorAll('.kad-bilik').forEach(kad => {
        const nama   = kad.dataset.nama || '';
        const lokasi = kad.dataset.lokasi || '';
        const kadBhg = kad.dataset.bahagianId || '';

        const matchCarian  = !carian  || nama.includes(carian) || lokasi.includes(carian);
        const matchBahagian = !bahagian || kadBhg === bahagian;

        const match = matchCarian && matchBahagian;
        kad.style.display = match ? '' : 'none';
        if (match) nampak++;
    });

    if (kiraan) {
        const total = document.querySelectorAll('.kad-bilik').length;
        kiraan.textContent = (carian || bahagian) ? nampak + ' daripada ' + total + ' bilik' : '';
    }
}

document.getElementById('carian-bilik').addEventListener('input', terapiFilter);
document.getElementById('filter-bahagian').addEventListener('change', terapiFilter);

document.getElementById('grid-bilik').addEventListener('submit', function(e) {
    const form = e.target.closest('.padam-bilik-form');
    if (form) {
        const nama = form.dataset.nama || '';
        if (!confirm('Padam bilik ' + nama + '?')) {
            e.preventDefault();
        }
    }
});
</script>
@endpush
@endsection
