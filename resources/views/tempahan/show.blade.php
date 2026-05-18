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
                    <div class="w-8 h-8 rounded-full {{ $tempahan->status === 'ditolak' ? 'bg-red-100' : 'bg-green-100' }} flex items-center justify-center flex-shrink-0" aria-hidden="true">
                        <i class="fa-solid fa-{{ $tempahan->status === 'ditolak' ? 'xmark text-red-600' : 'check text-green-600' }} text-xs" aria-hidden="true"></i>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-gray-700">
                            @if($tempahan->status === 'diluluskan') Diluluskan
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
                        @endif
                    </div>
                </li>
            </ol>
        </section>

        <a href="{{ route('tempahan.index') }}" class="btn-secondary w-full justify-center">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Kembali
        </a>
    </div>
</div>
@endsection
