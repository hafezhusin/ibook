@extends('layouts.app')

@section('title', 'Senarai Tempahan')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Senarai Tempahan</h1>
        <p class="text-gray-500 text-sm mt-1">{{ $tempahan->total() }} rekod</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('tempahan.pdf', request()->query()) }}" class="btn-secondary text-sm">
            <i class="fa-solid fa-file-pdf text-red-500" aria-hidden="true"></i>
            <span>Eksport PDF</span>
        </a>
        <a href="{{ route('tempahan.excel', request()->query()) }}" class="btn-secondary text-sm">
            <i class="fa-solid fa-file-excel text-green-600" aria-hidden="true"></i>
            <span>Eksport Excel</span>
        </a>
        <a href="{{ route('tempahan.create') }}" class="btn-primary">
            <i class="fa-solid fa-plus" aria-hidden="true"></i> Tempahan Baru
        </a>
    </div>
</div>

{{-- Filter --}}
<section class="bg-white rounded-xl shadow-sm p-4 mb-5" aria-labelledby="heading-filter">
    <h2 id="heading-filter" class="sr-only">Tapis Senarai Tempahan</h2>
    <form method="GET" role="search" aria-label="Cari dan tapis tempahan" class="flex gap-3 flex-wrap">

        <div>
            <label for="filter-status" class="sr-only">Tapis mengikut status</label>
            <select id="filter-status" name="status" class="form-input w-auto text-sm">
                <option value="">Semua Status</option>
                <option value="menunggu" {{ request('status') === 'menunggu' ? 'selected' : '' }}>Menunggu Kelulusan</option>
                <option value="diluluskan" {{ request('status') === 'diluluskan' ? 'selected' : '' }}>Diluluskan</option>
                <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
            </select>
        </div>

        <div>
            <label for="filter-bilik" class="sr-only">Tapis mengikut bilik</label>
            <select id="filter-bilik" name="bilik_id" class="form-input w-auto text-sm">
                <option value="">Semua Bilik</option>
                @foreach($bilik as $b)
                <option value="{{ $b->id }}" {{ request('bilik_id') == $b->id ? 'selected' : '' }}>{{ $b->nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex-1 min-w-[200px]">
            <label for="carian-tempahan" class="sr-only">Cari nama mesyuarat</label>
            <input type="search" id="carian-tempahan" name="carian"
                value="{{ request('carian') }}"
                placeholder="Cari nama mesyuarat..."
                class="form-input text-sm w-full">
        </div>

        <button type="submit" class="btn-primary text-sm">
            <i class="fa-solid fa-search" aria-hidden="true"></i> Cari
        </button>

        @if(request()->hasAny(['status','bilik_id','carian']))
        <a href="{{ route('tempahan.index') }}" class="btn-secondary text-sm">
            Reset<span class="sr-only"> penapis</span>
        </a>
        @endif
    </form>
</section>

{{-- Jadual --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="table w-full" aria-describedby="perihal-jadual">
        <caption id="perihal-jadual" class="sr-only">
            Senarai tempahan bilik mesyuarat — {{ $tempahan->total() }} rekod
            @if(request()->hasAny(['status','bilik_id','carian'])) (ditapis) @endif
        </caption>
        <thead class="table-header">
            <tr>
                <th scope="col">Mesyuarat</th>
                <th scope="col">Tarikh</th>
                <th scope="col">Masa</th>
                <th scope="col">Bilik</th>
                <th scope="col">Pemohon</th>
                <th scope="col">Status</th>
                <th scope="col">Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tempahan as $t)
            <tr>
                <td class="font-semibold">{{ $t->nama_mesyuarat }}</td>
                <td><time datetime="{{ $t->tarikh->format('Y-m-d') }}">{{ $t->tarikh->format('d/m/Y') }}</time></td>
                <td>{{ $t->masa_label }}</td>
                <td>{{ $t->bilik->nama ?? '-' }}</td>
                <td>{{ $t->pengguna->name ?? '-' }}</td>
                <td>
                    @if($t->status === 'diluluskan')
                        <span class="badge-lulus">Diluluskan</span>
                    @elseif($t->status === 'menunggu')
                        <span class="badge-menunggu">Menunggu Kelulusan</span>
                    @else
                        <span class="badge-tolak">Ditolak</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('tempahan.show', $t) }}"
                        class="text-amber-500 hover:text-amber-700 text-sm font-semibold"
                        aria-label="Lihat butiran — {{ $t->nama_mesyuarat }}">
                        <i class="fa-solid fa-eye mr-1" aria-hidden="true"></i>Lihat
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-12 text-gray-400">
                    <i class="fa-solid fa-inbox text-3xl mb-3 block" aria-hidden="true"></i>
                    Tiada rekod tempahan ditemui
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($tempahan->hasPages())
    <nav class="px-6 py-4 border-t border-gray-100" aria-label="Navigasi halaman">
        {{ $tempahan->withQueryString()->links() }}
    </nav>
    @endif
</div>
@endsection
