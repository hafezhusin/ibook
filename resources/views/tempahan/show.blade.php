@extends('layouts.app')

@section('title', 'Butiran Tempahan')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('tempahan.index') }}" class="text-gray-400 hover:text-gray-600">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Butiran Tempahan</h1>
        <p class="text-gray-500 text-sm">{{ $tempahan->nama_mesyuarat }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-bold text-gray-800 text-lg">Maklumat Tempahan</h2>
            @if($tempahan->status === 'diluluskan')
                <span class="badge-lulus text-sm">✓ Diluluskan</span>
            @elseif($tempahan->status === 'menunggu')
                <span class="badge-menunggu text-sm">⏳ Menunggu Kelulusan</span>
            @else
                <span class="badge-tolak text-sm">✗ Ditolak</span>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-x-8 gap-y-5">
            <div>
                <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Nama Mesyuarat</div>
                <div class="font-semibold text-gray-800">{{ $tempahan->nama_mesyuarat }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Kategori</div>
                <div class="font-semibold text-gray-800">{{ $tempahan->kategori_label }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Tarikh</div>
                <div class="font-semibold text-gray-800">{{ $tempahan->tarikh->isoFormat('dddd, D MMMM YYYY') }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Masa</div>
                <div class="font-semibold text-gray-800">{{ $tempahan->masa_label }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Bilik Mesyuarat</div>
                <div class="font-semibold text-gray-800">{{ $tempahan->bilik->nama ?? '-' }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Bilangan Peserta</div>
                <div class="font-semibold text-gray-800">{{ $tempahan->bilangan_peserta }} orang</div>
            </div>
            <div>
                <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Nama Pengerusi</div>
                <div class="font-semibold text-gray-800">{{ $tempahan->nama_pengerusi }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Pemohon</div>
                <div class="font-semibold text-gray-800">{{ $tempahan->pengguna->name ?? '-' }}</div>
            </div>
            @if($tempahan->tujuan)
            <div class="col-span-2">
                <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Tujuan / Agenda</div>
                <div class="text-gray-700">{{ $tempahan->tujuan }}</div>
            </div>
            @endif
        </div>

        @if($tempahan->status === 'ditolak' && $tempahan->catatan_penolakan)
        <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="text-sm font-semibold text-red-700 mb-1">Sebab Penolakan:</div>
            <div class="text-sm text-red-600">{{ $tempahan->catatan_penolakan }}</div>
        </div>
        @endif
    </div>

    <div class="space-y-5">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-bold text-gray-800 mb-4">Status Permohonan</h3>
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                        <i class="fa-solid fa-check text-green-600 text-xs"></i>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-gray-700">Permohonan Dihantar</div>
                        <div class="text-xs text-gray-400">{{ $tempahan->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full {{ $tempahan->status !== 'menunggu' ? 'bg-green-100' : 'bg-gray-100' }} flex items-center justify-center">
                        <i class="fa-solid fa-{{ $tempahan->status !== 'menunggu' ? 'check text-green-600' : 'clock text-gray-400' }} text-xs"></i>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-gray-700">
                            @if($tempahan->status === 'diluluskan') Diluluskan
                            @elseif($tempahan->status === 'ditolak') Ditolak
                            @else Menunggu Kelulusan
                            @endif
                        </div>
                        @if($tempahan->diluluskan_pada)
                        <div class="text-xs text-gray-400">{{ $tempahan->diluluskan_pada->format('d/m/Y H:i') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->bolehLuluskan() && $tempahan->isMenunggu())
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-bold text-gray-800 mb-4">Tindakan</h3>
            <form method="POST" action="{{ route('kelulusan.lulus', $tempahan) }}" class="mb-3">
                @csrf
                <button type="submit" class="w-full btn-success py-2 rounded-lg">
                    <i class="fa-solid fa-circle-check mr-2"></i> Lulus Permohonan
                </button>
            </form>
            <button onclick="document.getElementById('tolak-modal').classList.remove('hidden')"
                class="w-full btn-danger py-2 rounded-lg">
                <i class="fa-solid fa-circle-xmark mr-2"></i> Tolak Permohonan
            </button>
        </div>
        @endif

        <a href="{{ route('tempahan.index') }}" class="btn-secondary w-full justify-center">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

{{-- Modal Tolak --}}
<div id="tolak-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <h3 class="font-bold text-gray-800 text-lg mb-4">Tolak Permohonan</h3>
        <form method="POST" action="{{ route('kelulusan.tolak', $tempahan) }}">
            @csrf
            <div class="mb-4">
                <label class="form-label">Catatan Penolakan (Pilihan)</label>
                <textarea name="catatan_penolakan" rows="3" class="form-input"
                    placeholder="Nyatakan sebab penolakan..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-danger flex-1 py-2 rounded-lg">Tolak</button>
                <button type="button" onclick="document.getElementById('tolak-modal').classList.add('hidden')"
                    class="btn-secondary flex-1 py-2 rounded-lg justify-center">Batal</button>
            </div>
        </form>
    </div>
</div>
@endsection
