@extends('layouts.app')

@section('title', 'Kelulusan')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Kelulusan</h1>
    <p class="text-gray-500 mt-1">
        <span aria-live="polite">{{ $menunggu->count() }} permohonan menunggu kelulusan</span>
    </p>
</div>

<section class="bg-white rounded-xl shadow-sm overflow-hidden" aria-labelledby="heading-menunggu-kelulusan">
    <div class="p-6 border-b border-gray-100">
        <h2 id="heading-menunggu-kelulusan" class="font-bold text-gray-800">Menunggu Kelulusan</h2>
    </div>

    @forelse($menunggu as $t)
    <article class="p-6 border-b border-gray-100 last:border-0" aria-labelledby="mesyuarat-{{ $t->id }}">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <h3 id="mesyuarat-{{ $t->id }}" class="font-bold text-gray-800 text-base">{{ $t->nama_mesyuarat }}</h3>
                <div class="text-sm text-gray-500 mt-1 flex flex-wrap gap-x-4 gap-y-1">
                    <span>
                        <i class="fa-solid fa-calendar text-amber-400 mr-1" aria-hidden="true"></i>
                        <time datetime="{{ $t->tarikh->format('Y-m-d') }}">{{ $t->tarikh->format('d/m/Y') }}</time>
                    </span>
                    <span>
                        <i class="fa-solid fa-clock text-amber-400 mr-1" aria-hidden="true"></i>{{ $t->masa_label }}
                    </span>
                    <span>
                        <i class="fa-solid fa-door-open text-amber-400 mr-1" aria-hidden="true"></i>{{ $t->bilik->nama ?? '-' }}
                    </span>
                </div>
                <div class="text-sm text-gray-500 mt-1">
                    <span><i class="fa-solid fa-user text-amber-400 mr-1" aria-hidden="true"></i>Pemohon: {{ $t->pengguna->name ?? '-' }}</span>
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
                    <button type="submit"
                        class="btn-success flex items-center gap-2"
                        onclick="return confirm('Luluskan tempahan {{ addslashes($t->nama_mesyuarat) }}?')"
                        aria-label="Luluskan — {{ $t->nama_mesyuarat }}">
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i> Lulus
                    </button>
                </form>

                <button type="button"
                    onclick="openTolak({{ $t->id }}, '{{ addslashes($t->nama_mesyuarat) }}')"
                    class="btn-danger flex items-center gap-2"
                    aria-label="Tolak — {{ $t->nama_mesyuarat }}"
                    aria-haspopup="dialog"
                    aria-controls="modal-tolak">
                    <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i> Tolak
                </button>

                <a href="{{ route('tempahan.show', $t) }}"
                    class="text-amber-500 text-sm hover:underline"
                    aria-label="Semak butiran — {{ $t->nama_mesyuarat }}">
                    <i class="fa-solid fa-eye" aria-hidden="true"></i> Semak
                </a>
            </div>
        </div>
    </article>
    @empty
    <div class="text-center py-16 text-gray-400">
        <i class="fa-solid fa-circle-check text-5xl mb-4 text-green-300" aria-hidden="true"></i>
        <p class="font-semibold">Tiada permohonan menunggu kelulusan</p>
        <p class="text-sm">Semua permohonan telah diproses</p>
    </div>
    @endforelse
</section>

{{-- Modal Tolak --}}
<div id="modal-tolak"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-tolak-heading">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <h3 id="modal-tolak-heading" class="font-bold text-gray-800 text-lg mb-1">Tolak Permohonan</h3>
        <p id="modal-nama" class="text-gray-500 text-sm mb-4"></p>
        <form id="form-tolak" method="POST">
            @csrf
            <div class="mb-4">
                <label for="catatan-penolakan" class="form-label">Catatan Penolakan (Pilihan)</label>
                <textarea id="catatan-penolakan" name="catatan_penolakan" rows="3" class="form-input"
                    placeholder="Nyatakan sebab penolakan..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-danger flex-1 py-2.5 rounded-lg">
                    <i class="fa-solid fa-circle-xmark mr-2" aria-hidden="true"></i>Tolak Permohonan
                </button>
                <button type="button"
                    onclick="closeTolak()"
                    class="btn-secondary flex-1 py-2.5 rounded-lg justify-center">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openTolak(id, nama) {
    const modal = document.getElementById('modal-tolak');
    document.getElementById('modal-nama').textContent = nama;
    document.getElementById('form-tolak').action = '/kelulusan/' + id + '/tolak';
    modal.classList.remove('hidden');
    // Focus textarea for screen readers
    setTimeout(() => document.getElementById('catatan-penolakan').focus(), 50);
}
function closeTolak() {
    document.getElementById('modal-tolak').classList.add('hidden');
}
// Esc key closes modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeTolak();
});
</script>
@endpush
@endsection
