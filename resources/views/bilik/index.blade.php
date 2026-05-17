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

<div class="mb-4">
    <div class="relative">
        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm" aria-hidden="true"></i>
        <input type="search" id="carian-bilik"
            placeholder="Cari nama bilik..."
            oninput="cariBilik(this.value)"
            class="form-input pl-9 text-sm w-full md:w-72"
            aria-label="Cari bilik mesyuarat">
    </div>
</div>

<section aria-labelledby="heading-bilik-senarai">
    <h2 id="heading-bilik-senarai" class="sr-only">Senarai Bilik Mesyuarat</h2>
    <div id="grid-bilik" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($bilik as $b)
        <article class="bg-white rounded-xl shadow-sm overflow-hidden kad-bilik"
            data-nama="{{ strtolower($b->nama) }}"
            data-lokasi="{{ strtolower($b->lokasi ?? '') }}"
            aria-labelledby="bilik-{{ $b->id }}">
            <div class="h-40 bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center" aria-hidden="true">
                <i class="fa-solid fa-door-open text-slate-400 text-5xl" aria-hidden="true"></i>
            </div>
            <div class="p-5">
                <div class="flex items-start justify-between mb-3">
                    <h3 id="bilik-{{ $b->id }}" class="font-bold text-gray-800">{{ $b->nama }}</h3>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $b->isAktif() ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}"
                        role="status">
                        {{ $b->isAktif() ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </div>

                <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                    <i class="fa-solid fa-users text-amber-400" aria-hidden="true"></i>
                    <span>{{ $b->kapasiti }} orang</span>
                    @if($b->lokasi)
                    <span aria-hidden="true">&middot;</span>
                    <i class="fa-solid fa-location-dot text-amber-400" aria-hidden="true"></i>
                    <span>{{ $b->lokasi }}</span>
                    @endif
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

                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-500">Penggunaan bulan ini</span>
                        <span class="font-semibold text-gray-700">{{ $b->penggunaan_bulan_ini }}%</span>
                    </div>
                    <div class="progress-bar"
                        role="progressbar"
                        aria-valuenow="{{ $b->penggunaan_bulan_ini }}"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        aria-label="{{ $b->nama }}: {{ $b->penggunaan_bulan_ini }}% digunakan bulan ini">
                        <div class="progress-fill" style="width:{{ $b->penggunaan_bulan_ini }}%"></div>
                    </div>
                </div>

                <div class="flex gap-2 pt-3 border-t border-gray-100">
                    <a href="{{ route('bilik.edit', $b) }}"
                        class="text-amber-500 text-sm font-semibold hover:underline flex items-center gap-1"
                        aria-label="Edit bilik — {{ $b->nama }}">
                        <i class="fa-solid fa-pen" aria-hidden="true"></i> Edit
                    </a>
                    <form method="POST" action="{{ route('bilik.destroy', $b) }}" class="ml-auto"
                        onsubmit="return confirm('Padam bilik {{ addslashes($b->nama) }}?')">
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
<script>
function cariBilik(kata) {
    const carian = kata.trim().toLowerCase();
    document.querySelectorAll('.kad-bilik').forEach(kad => {
        const nama   = kad.dataset.nama || '';
        const lokasi = kad.dataset.lokasi || '';
        const match  = !carian || nama.includes(carian) || lokasi.includes(carian);
        kad.style.display = match ? '' : 'none';
    });
}
</script>
@endpush
@endsection
