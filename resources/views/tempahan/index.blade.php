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
            <i class="fa-solid fa-file-pdf text-red-500"></i> Eksport PDF
        </a>
        <a href="{{ route('tempahan.excel', request()->query()) }}" class="btn-secondary text-sm">
            <i class="fa-solid fa-file-excel text-green-600"></i> Eksport Excel
        </a>
        <a href="{{ route('tempahan.create') }}" class="btn-primary">
            <i class="fa-solid fa-plus"></i> Tempahan Baru
        </a>
    </div>
</div>

{{-- Filter --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-5">
    <form method="GET" class="flex gap-3 flex-wrap">
        <select name="status" class="form-input w-auto text-sm">
            <option value="">Semua Status</option>
            <option value="menunggu" {{ request('status') === 'menunggu' ? 'selected' : '' }}>Menunggu Kelulusan</option>
            <option value="diluluskan" {{ request('status') === 'diluluskan' ? 'selected' : '' }}>Diluluskan</option>
            <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
        </select>
        <select name="bilik_id" class="form-input w-auto text-sm">
            <option value="">Semua Bilik</option>
            @foreach($bilik as $b)
            <option value="{{ $b->id }}" {{ request('bilik_id') == $b->id ? 'selected' : '' }}>{{ $b->nama }}</option>
            @endforeach
        </select>
        <input type="text" name="carian" value="{{ request('carian') }}" placeholder="Cari nama mesyuarat..."
            class="form-input flex-1 min-w-[200px] text-sm">
        <button type="submit" class="btn-primary text-sm">
            <i class="fa-solid fa-search"></i> Cari
        </button>
        @if(request()->hasAny(['status','bilik_id','carian']))
        <a href="{{ route('tempahan.index') }}" class="btn-secondary text-sm">Reset</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="table w-full">
        <thead class="table-header">
            <tr>
                <th>Mesyuarat</th>
                <th>Tarikh</th>
                <th>Masa</th>
                <th>Bilik</th>
                <th>Pemohon</th>
                <th>Status</th>
                <th>Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tempahan as $t)
            <tr>
                <td class="font-semibold">{{ $t->nama_mesyuarat }}</td>
                <td>{{ $t->tarikh->format('d/m/Y') }}</td>
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
                    <a href="{{ route('tempahan.show', $t) }}" class="text-amber-500 hover:text-amber-700 text-sm font-semibold">
                        <i class="fa-solid fa-eye mr-1"></i>Lihat
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-12 text-gray-400">
                    <i class="fa-solid fa-inbox text-3xl mb-3 block"></i>
                    Tiada rekod tempahan ditemui
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($tempahan->hasPages())
    <div class="px-6 py-4 border-t border-gray-100">
        {{ $tempahan->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
