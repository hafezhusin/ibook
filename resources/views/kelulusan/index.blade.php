@extends('layouts.app')

@section('title', 'Kelulusan')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Kelulusan</h1>
    <p class="text-gray-500 mt-1">{{ $menunggu->count() }} permohonan menunggu kelulusan</p>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="p-6 border-b border-gray-100">
        <h2 class="font-bold text-gray-800">Menunggu Kelulusan</h2>
    </div>

    @forelse($menunggu as $t)
    <div class="p-6 border-b border-gray-100 last:border-0">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <h3 class="font-bold text-gray-800 text-base">{{ $t->nama_mesyuarat }}</h3>
                <div class="text-sm text-gray-500 mt-1 flex flex-wrap gap-x-4 gap-y-1">
                    <span><i class="fa-solid fa-calendar text-amber-400 mr-1"></i>{{ $t->tarikh->format('d/m/Y') }}</span>
                    <span><i class="fa-solid fa-clock text-amber-400 mr-1"></i>{{ $t->masa_label }}</span>
                    <span><i class="fa-solid fa-door-open text-amber-400 mr-1"></i>{{ $t->bilik->nama ?? '-' }}</span>
                </div>
                <div class="text-sm text-gray-500 mt-1">
                    <span><i class="fa-solid fa-user text-amber-400 mr-1"></i>Pemohon: {{ $t->pengguna->name ?? '-' }}</span>
                    &middot;
                    <span>{{ $t->bilangan_peserta }} peserta</span>
                    @if($t->tujuan)
                    &middot;
                    <span>{{ Str::limit($t->tujuan, 60) }}</span>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
                <form method="POST" action="{{ route('kelulusan.lulus', $t) }}">
                    @csrf
                    <button type="submit" class="btn-success flex items-center gap-2"
                        onclick="return confirm('Luluskan tempahan ini?')">
                        <i class="fa-solid fa-circle-check"></i> Lulus
                    </button>
                </form>

                <button onclick="openTolak({{ $t->id }}, '{{ addslashes($t->nama_mesyuarat) }}')"
                    class="btn-danger flex items-center gap-2">
                    <i class="fa-solid fa-circle-xmark"></i> Tolak
                </button>

                <a href="{{ route('tempahan.show', $t) }}" class="text-amber-500 text-sm hover:underline">
                    <i class="fa-solid fa-eye"></i> Semak
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-16 text-gray-400">
        <i class="fa-solid fa-circle-check text-5xl mb-4 text-green-300"></i>
        <p class="font-semibold">Tiada permohonan menunggu kelulusan</p>
        <p class="text-sm">Semua permohonan telah diproses</p>
    </div>
    @endforelse
</div>

{{-- Modal Tolak --}}
<div id="modal-tolak" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <h3 class="font-bold text-gray-800 text-lg mb-1">Tolak Permohonan</h3>
        <p id="modal-nama" class="text-gray-500 text-sm mb-4"></p>
        <form id="form-tolak" method="POST">
            @csrf
            <div class="mb-4">
                <label class="form-label">Catatan Penolakan (Pilihan)</label>
                <textarea name="catatan_penolakan" rows="3" class="form-input"
                    placeholder="Nyatakan sebab penolakan..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-danger flex-1 py-2.5 rounded-lg">
                    <i class="fa-solid fa-circle-xmark mr-2"></i>Tolak Permohonan
                </button>
                <button type="button" onclick="closeTolak()" class="btn-secondary flex-1 py-2.5 rounded-lg justify-center">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openTolak(id, nama) {
    document.getElementById('modal-nama').textContent = nama;
    document.getElementById('form-tolak').action = '/kelulusan/' + id + '/tolak';
    document.getElementById('modal-tolak').classList.remove('hidden');
}
function closeTolak() {
    document.getElementById('modal-tolak').classList.add('hidden');
}
</script>
@endpush
@endsection
