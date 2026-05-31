@extends('layouts.app')

@section('title', 'Butiran Tempahan')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('tempahan.index') }}" class="text-gray-400 hover:text-gray-600" aria-label="Kembali ke senarai tempahan">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Butiran Tempahan</h1>
        <p class="text-gray-500 text-sm">{{ $tempahan->nama_mesyuarat }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Maklumat Utama --}}
    <article class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="font-bold text-gray-800 text-lg">Maklumat Tempahan</h2>
                <p class="text-xs text-gray-400 font-mono mt-0.5" title="Nombor Rujukan Unik Tempahan">
                    <i class="fa-solid fa-hashtag text-[9px] mr-0.5" aria-hidden="true"></i>{{ $tempahan->no_rujukan }}
                </p>
            </div>
            @if($tempahan->status === 'diluluskan')
                <span class="badge-lulus text-sm" role="status">
                    <span aria-hidden="true">✓</span> Diluluskan
                </span>
            @elseif($tempahan->status === 'dibatalkan')
                <span class="badge-batal text-sm" role="status">
                    <span aria-hidden="true">⊘</span> Dibatalkan
                </span>
            @elseif($tempahan->status === 'menunggu')
                <span class="badge-tunggu text-sm" role="status">
                    <span aria-hidden="true">◔</span> Menunggu
                </span>
            @else
                <span class="badge-tolak text-sm" role="status">
                    <span aria-hidden="true">✗</span> Ditolak
                </span>
            @endif
        </div>

        <dl class="grid grid-cols-2 gap-x-8 gap-y-5">
            <div>
                <dt class="text-xs text-gray-400 uppercase tracking-wider mb-1">Nama Mesyuarat</dt>
                <dd class="font-semibold text-gray-800">{{ $tempahan->nama_mesyuarat }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400 uppercase tracking-wider mb-1">Kategori</dt>
                <dd class="font-semibold text-gray-800">{{ $tempahan->kategori_label }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400 uppercase tracking-wider mb-1">Tarikh</dt>
                <dd class="font-semibold text-gray-800">
                    <time datetime="{{ $tempahan->tarikh->format('Y-m-d') }}">
                        {{ $tempahan->tarikh->isoFormat('dddd, D MMMM YYYY') }}
                    </time>
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400 uppercase tracking-wider mb-1">Masa</dt>
                <dd class="font-semibold text-gray-800">{{ $tempahan->masa_label }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400 uppercase tracking-wider mb-1">Bilik Mesyuarat</dt>
                <dd class="font-semibold text-gray-800">{{ $tempahan->bilik->nama ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400 uppercase tracking-wider mb-1">Bilangan Peserta</dt>
                <dd class="font-semibold text-gray-800">{{ $tempahan->bilangan_peserta }} orang</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400 uppercase tracking-wider mb-1">Nama Pengerusi</dt>
                <dd class="font-semibold text-gray-800">{{ $tempahan->nama_pengerusi }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400 uppercase tracking-wider mb-1">Pemohon</dt>
                <dd class="font-semibold text-gray-800">
                    {{ $tempahan->pengguna->name ?? '-' }}
                    @if($tempahan->pengguna?->jabatan)
                    <span class="block text-xs text-gray-400 font-normal mt-0.5">{{ $tempahan->pengguna->jabatan }}</span>
                    @endif
                </dd>
            </div>
            @if($tempahan->tujuan)
            <div class="col-span-2">
                <dt class="text-xs text-gray-400 uppercase tracking-wider mb-1">Tujuan / Agenda</dt>
                <dd class="text-gray-700">{{ $tempahan->tujuan }}</dd>
            </div>
            @endif
        </dl>

        @if($tempahan->status === 'ditolak' && $tempahan->catatan_penolakan)
        <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg" role="note" aria-label="Sebab penolakan">
            <div class="text-sm font-semibold text-red-700 mb-1">Sebab Penolakan:</div>
            <div class="text-sm text-red-600">{{ $tempahan->catatan_penolakan }}</div>
        </div>
        @elseif($tempahan->status === 'dibatalkan' && $tempahan->catatan_penolakan)
        <div class="mt-6 p-4 bg-violet-50 border border-violet-200 rounded-lg" role="note" aria-label="Sebab pembatalan">
            <div class="text-sm font-semibold text-violet-700 mb-1">Sebab Pembatalan:</div>
            <div class="text-sm text-violet-600">{{ $tempahan->catatan_penolakan }}</div>
        </div>
        @endif
    </article>

    {{-- Sidebar kanan --}}
    <div class="space-y-5">

        {{-- Status Timeline --}}
        <section class="bg-white rounded-xl shadow-sm p-6" aria-labelledby="heading-status-permohonan">
            <h2 id="heading-status-permohonan" class="font-bold text-gray-800 mb-4">Status Permohonan</h2>
            <ol class="space-y-3">
                <li class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0" aria-hidden="true">
                        <i class="fa-solid fa-check text-green-600 text-xs" aria-hidden="true"></i>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-gray-700">Permohonan Dihantar</div>
                        <div class="text-xs text-gray-400">
                            oleh <strong>{{ $tempahan->pengguna->name ?? '—' }}</strong>
                        </div>
                        <div class="text-xs text-gray-400">
                            <time datetime="{{ $tempahan->created_at->format('Y-m-d\TH:i') }}">
                                {{ $tempahan->created_at->format('d/m/Y H:i') }}
                            </time>
                        </div>
                    </div>
                </li>

                @if($tempahan->dikemaskini_oleh && $tempahan->dikemaskini_oleh !== $tempahan->user_id)
                {{-- Pindaan oleh orang lain --}}
                <li class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0" aria-hidden="true">
                        <i class="fa-solid fa-pen text-amber-600 text-xs" aria-hidden="true"></i>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-gray-700">Dikemaskini</div>
                        <div class="text-xs text-gray-400">
                            oleh <strong>{{ $tempahan->pengubah->name ?? '—' }}</strong>
                            @if($tempahan->pengubah?->jabatan)
                            <span class="text-gray-400">({{ $tempahan->pengubah->jabatan }})</span>
                            @endif
                        </div>
                        @if($tempahan->dikemaskini_pada)
                        <div class="text-xs text-gray-400">
                            <time datetime="{{ $tempahan->dikemaskini_pada->format('Y-m-d\TH:i') }}">
                                {{ $tempahan->dikemaskini_pada->format('d/m/Y H:i') }}
                            </time>
                        </div>
                        @endif
                    </div>
                </li>
                @elseif($tempahan->dikemaskini_oleh)
                {{-- Kemaskini oleh diri sendiri --}}
                <li class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0" aria-hidden="true">
                        <i class="fa-solid fa-pen text-blue-400 text-xs" aria-hidden="true"></i>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-gray-700">Dikemaskini</div>
                        @if($tempahan->dikemaskini_pada)
                        <div class="text-xs text-gray-400">
                            <time datetime="{{ $tempahan->dikemaskini_pada->format('Y-m-d\TH:i') }}">
                                {{ $tempahan->dikemaskini_pada->format('d/m/Y H:i') }}
                            </time>
                        </div>
                        @endif
                    </div>
                </li>
                @endif

                <li class="flex items-center gap-3">
                    @if($tempahan->status === 'ditolak')
                    <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0" aria-hidden="true">
                        <i class="fa-solid fa-xmark text-red-600 text-xs" aria-hidden="true"></i>
                    </div>
                    @elseif($tempahan->status === 'dibatalkan')
                    <div class="w-8 h-8 rounded-full bg-violet-100 flex items-center justify-center flex-shrink-0" aria-hidden="true">
                        <i class="fa-solid fa-ban text-violet-600 text-xs" aria-hidden="true"></i>
                    </div>
                    @elseif($tempahan->status === 'menunggu')
                    <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center flex-shrink-0" aria-hidden="true">
                        <i class="fa-solid fa-clock text-yellow-600 text-xs" aria-hidden="true"></i>
                    </div>
                    @else
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0" aria-hidden="true">
                        <i class="fa-solid fa-check text-green-600 text-xs" aria-hidden="true"></i>
                    </div>
                    @endif
                    <div>
                        <div class="text-sm font-semibold text-gray-700">
                            @if($tempahan->status === 'diluluskan') Diluluskan
                            @elseif($tempahan->status === 'dibatalkan') Dibatalkan
                            @elseif($tempahan->status === 'menunggu') Menunggu Kelulusan
                            @else Ditolak
                            @endif
                        </div>
                        @if($tempahan->pelulus)
                        <div class="text-xs text-gray-400">oleh <strong>{{ $tempahan->pelulus->name }}</strong></div>
                        @endif
                        @if($tempahan->diluluskan_pada)
                        <div class="text-xs text-gray-400">
                            <time datetime="{{ $tempahan->diluluskan_pada->format('Y-m-d\TH:i') }}">
                                {{ $tempahan->diluluskan_pada->format('d/m/Y H:i') }}
                            </time>
                        </div>
                        @elseif($tempahan->dikemaskini_pada && in_array($tempahan->status, ['dibatalkan', 'ditolak']))
                        <div class="text-xs text-gray-400">
                            <time datetime="{{ $tempahan->dikemaskini_pada->format('Y-m-d\TH:i') }}">
                                {{ $tempahan->dikemaskini_pada->format('d/m/Y H:i') }}
                            </time>
                        </div>
                        @endif
                    </div>
                </li>
            </ol>
        </section>

        {{-- Panel Kelulusan — hanya untuk Pentadbir / Urus Setia apabila status menunggu --}}
        @if($tempahan->status === 'menunggu' && (auth()->user()->isPentadbir() || auth()->user()->isUrusSetia()))
        <section class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-amber-400" aria-labelledby="heading-tindakan-lulus">
            <h2 id="heading-tindakan-lulus" class="font-bold text-gray-800 mb-1 flex items-center gap-2 text-sm">
                <i class="fa-solid fa-triangle-exclamation text-amber-500" aria-hidden="true"></i>
                Menunggu Kelulusan
            </h2>
            <p class="text-xs text-gray-400 mb-4">Tempahan ini perlu kelulusan sebelum disahkan.</p>

            {{-- Luluskan --}}
            <form method="POST" action="{{ route('tempahan.luluskan', $tempahan) }}" class="mb-2">
                @csrf
                <button type="submit"
                        onclick="return confirm('Luluskan tempahan \'{{ addslashes($tempahan->nama_mesyuarat) }}\'?')"
                        class="w-full inline-flex items-center justify-center gap-2 text-sm font-semibold px-4 py-2.5 rounded-lg bg-green-600 text-white hover:bg-green-700 transition-colors">
                    <i class="fa-solid fa-check" aria-hidden="true"></i> Luluskan
                </button>
            </form>

            {{-- Toggle butang Tolak --}}
            <button type="button" id="btn-tunjuk-tolak"
                    class="w-full inline-flex items-center justify-center gap-2 text-sm font-semibold px-4 py-2.5 rounded-lg border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 transition-colors">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i> Tolak Tempahan
            </button>

            {{-- Form tolak (tersembunyi) --}}
            <div id="panel-tolak" style="display:none" class="mt-3 pt-3 border-t border-gray-100">
                <form method="POST" action="{{ route('tempahan.tolak', $tempahan) }}">
                    @csrf
                    <label for="catatan_penolakan" class="block text-xs font-semibold text-gray-600 mb-1">
                        Sebab Penolakan <span class="text-gray-400 font-normal">(pilihan)</span>
                    </label>
                    <textarea id="catatan_penolakan" name="catatan_penolakan"
                              rows="3" maxlength="500"
                              placeholder="Nyatakan sebab penolakan..."
                              class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-300 resize-none text-gray-700">{{ old('catatan_penolakan') }}</textarea>
                    <div class="flex gap-2 mt-2">
                        <button type="submit"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 text-sm font-semibold px-3 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors">
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i> Sahkan Tolak
                        </button>
                        <button type="button" id="btn-batal-tolak"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 text-sm font-semibold px-3 py-2 rounded-lg border border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100 transition-colors">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <script nonce="{{ $cspNonce ?? '' }}">
        (function () {
            function initTolakPanel() {
                var btnTunjuk = document.getElementById('btn-tunjuk-tolak');
                var btnBatal  = document.getElementById('btn-batal-tolak');
                var panel     = document.getElementById('panel-tolak');
                if (!btnTunjuk || !panel) return;

                btnTunjuk.addEventListener('click', function () {
                    panel.style.display = '';
                    btnTunjuk.style.display = 'none';
                });
                if (btnBatal) {
                    btnBatal.addEventListener('click', function () {
                        panel.style.display = 'none';
                        btnTunjuk.style.display = '';
                    });
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initTolakPanel);
            } else {
                initTolakPanel();
            }
        })();
        </script>
        @endif

        <a href="{{ route('tempahan.index') }}" class="btn-secondary w-full justify-center">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Kembali
        </a>
    </div>
</div>
@endsection
