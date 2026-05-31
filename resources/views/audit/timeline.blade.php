@extends('layouts.app')

@section('title', 'Timeline — '.$pengguna->name)

@section('content')

{{-- Header --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-4">
    <a href="{{ route('audit.index', ['pengguna_id' => $pengguna->id]) }}"
       class="text-gray-400 hover:text-gray-600 flex-shrink-0"
       aria-label="Kembali ke Log Audit">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
    </a>
    <div class="flex-1 min-w-0">
        <h1 class="text-2xl font-bold text-gray-800 truncate">
            <i class="fa-solid fa-clock-rotate-left text-amber-400 mr-2" aria-hidden="true"></i>
            Timeline: {{ $pengguna->name }}
        </h1>
        <p class="text-gray-500 text-sm mt-0.5">
            {{ $pengguna->email }}
            @if($pengguna->jabatan)
            &mdash; {{ $pengguna->jabatan }}
            @endif
        </p>
    </div>
    <div class="flex-shrink-0 flex items-center gap-2 flex-wrap">
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full
            {{ $pengguna->aktif ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
            <span class="w-1.5 h-1.5 rounded-full {{ $pengguna->aktif ? 'bg-green-500' : 'bg-red-500' }}" aria-hidden="true"></span>
            {{ $pengguna->aktif ? 'Aktif' : 'Tidak Aktif' }}
        </span>
        <span class="inline-block text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">
            {{ ucfirst(str_replace('_', ' ', $pengguna->peranan)) }}
        </span>
        {{-- Butang eksport PDF --}}
        <a href="{{ route('audit.timeline.pdf', array_merge(['pengguna' => $pengguna->id], request()->only(['tarikh_dari','tarikh_hingga']))) }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 transition-colors"
           title="Eksport timeline ini ke PDF">
            <i class="fa-solid fa-file-pdf" aria-hidden="true"></i> Eksport PDF
        </a>
    </div>
</div>

{{-- Kad statistik ringkas --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-amber-400">
        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Jumlah Tindakan</p>
        <p class="text-2xl font-bold text-gray-800">{{ number_format($jumlahKeseluruhan) }}</p>
        <p class="text-xs text-gray-400 mt-1">rekod keseluruhan</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-400">
        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Tindakan Terbanyak</p>
        @if($tindakanPopular)
        <p class="text-sm font-bold text-gray-800 truncate font-mono">{{ $tindakanPopular->tindakan }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ number_format($tindakanPopular->kiraan) }} kali</p>
        @else
        <p class="text-sm text-gray-400">—</p>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-400">
        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Tindakan Terkini</p>
        @if($aktivitiTerkini)
        <p class="text-sm font-bold text-gray-800">{{ \Carbon\Carbon::parse($aktivitiTerkini)->format('d/m/Y') }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::parse($aktivitiTerkini)->diffForHumans() }}</p>
        @else
        <p class="text-sm text-gray-400">—</p>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 {{ $jumlahKeselamatanGagal > 0 ? 'border-red-400' : 'border-gray-200' }}">
        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Log Masuk Gagal</p>
        <p class="text-2xl font-bold {{ $jumlahKeselamatanGagal > 0 ? 'text-red-600' : 'text-gray-800' }}">
            {{ $jumlahKeselamatanGagal }}
        </p>
        <p class="text-xs text-gray-400 mt-1">percubaan gagal</p>
    </div>

</div>

{{-- Filter tarikh --}}
<form method="GET" action="{{ route('audit.timeline', $pengguna) }}"
      class="bg-white rounded-xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end"
      aria-label="Tapis mengikut tarikh">
    <div>
        <label for="tarikh_dari" class="block text-xs text-gray-500 mb-1">Dari</label>
        <input type="date" id="tarikh_dari" name="tarikh_dari"
               value="{{ request('tarikh_dari') }}"
               class="form-input text-sm">
    </div>
    <div>
        <label for="tarikh_hingga" class="block text-xs text-gray-500 mb-1">Hingga</label>
        <input type="date" id="tarikh_hingga" name="tarikh_hingga"
               value="{{ request('tarikh_hingga') }}"
               class="form-input text-sm">
    </div>
    <button type="submit" class="btn-primary text-sm">
        <i class="fa-solid fa-filter" aria-hidden="true"></i> Tapis
    </button>
    @if(request()->hasAny(['tarikh_dari','tarikh_hingga']))
    <a href="{{ route('audit.timeline', $pengguna) }}" class="btn-secondary text-sm">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i> Set Semula
    </a>
    @endif
    <span class="text-xs text-gray-400 ml-auto self-center">
        {{ number_format($logs->total()) }} rekod dijumpai
    </span>
</form>

{{-- Timeline --}}
@if($logs->isEmpty())
<div class="bg-white rounded-xl shadow-sm py-16 text-center text-gray-400">
    <i class="fa-solid fa-clock-rotate-left text-4xl mb-3 block" aria-hidden="true"></i>
    <p class="font-medium">Tiada rekod aktiviti dalam tempoh ini.</p>
</div>
@else

<div class="relative">
    {{-- Garisan menegak timeline --}}
    <div class="absolute left-[7.5rem] top-0 bottom-0 w-px bg-gray-200 hidden sm:block" aria-hidden="true"></div>

    @foreach($logsByTarikh as $tarikh => $logsHariIni)
    @php $tarikhObj = \Carbon\Carbon::parse($tarikh); @endphp

    {{-- Label tarikh --}}
    <div class="flex items-center gap-4 mb-4 {{ !$loop->first ? 'mt-8' : '' }}">
        <div class="hidden sm:flex items-center justify-end w-28 flex-shrink-0">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider text-right leading-tight">
                {{ $tarikhObj->locale('ms')->isoFormat('ddd') }}<br>
                {{ $tarikhObj->format('d M Y') }}
            </span>
        </div>
        <div class="sm:ml-4 sm:hidden text-xs font-bold text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
            {{ $tarikhObj->format('d M Y') }}
        </div>
        {{-- Dot pada garisan --}}
        <div class="hidden sm:flex w-4 h-4 rounded-full bg-amber-400 border-2 border-white shadow flex-shrink-0 z-10" aria-hidden="true"></div>
    </div>

    {{-- Entri hari ini --}}
    <div class="sm:ml-36 space-y-2 mb-2">
        @foreach($logsHariIni as $log)
        @php
            $isBahaya = in_array($log->tindakan, ['log_masuk_gagal', 'percubaan_akaun_nyahaktif']);
            $badgeColor = match(true) {
                $log->tindakan === 'log_masuk_gagal'           => 'bg-red-100 text-red-700 border border-red-200',
                $log->tindakan === 'percubaan_akaun_nyahaktif' => 'bg-red-100 text-red-700 border border-red-200',
                $log->tindakan === 'log_masuk_berjaya'         => 'bg-emerald-50 text-emerald-700',
                $log->tindakan === 'log_keluar'                => 'bg-slate-100 text-slate-600',
                str_starts_with($log->tindakan, 'buat_')       => 'bg-green-50 text-green-700',
                str_starts_with($log->tindakan, 'kemaskini_')  => 'bg-blue-50 text-blue-700',
                str_starts_with($log->tindakan, 'padam_')      => 'bg-red-50 text-red-700',
                str_starts_with($log->tindakan, 'eksport_')    => 'bg-purple-50 text-purple-700',
                str_contains($log->tindakan, 'nyahaktifkan')   => 'bg-orange-50 text-orange-700',
                str_contains($log->tindakan, 'aktifkan')       => 'bg-teal-50 text-teal-700',
                default                                         => 'bg-gray-100 text-gray-600',
            };
        @endphp
        <div class="bg-white rounded-lg border {{ $isBahaya ? 'border-red-200 bg-red-50/40' : 'border-gray-200' }} px-4 py-3 shadow-sm">
            <div class="flex items-start gap-3 flex-wrap">
                {{-- Masa --}}
                <span class="font-mono text-xs text-gray-400 flex-shrink-0 mt-0.5 w-14">
                    {{ $log->dicipta_pada->format('H:i:s') }}
                </span>

                {{-- Badge tindakan --}}
                <span class="inline-block {{ $badgeColor }} text-xs font-semibold px-2 py-0.5 rounded-md font-mono flex-shrink-0">
                    {{ $log->tindakan }}
                </span>

                {{-- Penerangan --}}
                <span class="text-xs text-gray-600 flex-1 min-w-0">{{ $log->penerangan }}</span>

                {{-- IP --}}
                @if($log->ip_address)
                <span class="font-mono text-xs text-gray-300 flex-shrink-0 hidden lg:inline">{{ $log->ip_address }}</span>
                @endif

                {{-- Expand butiran --}}
                @if($log->butiran)
                <button type="button"
                    class="toggle-butiran-tl text-amber-400 hover:text-amber-600 flex-shrink-0"
                    data-log-id="{{ $log->id }}"
                    aria-expanded="false"
                    aria-controls="tl-butiran-{{ $log->id }}"
                    title="Lihat butiran">
                    <i class="fa-solid fa-chevron-down text-xs" aria-hidden="true"></i>
                </button>
                @endif
            </div>

            {{-- Butiran expandable --}}
            @if($log->butiran)
            <div id="tl-butiran-{{ $log->id }}" class="hidden mt-3 pt-3 border-t border-gray-100" aria-hidden="true">
                @if(!empty($log->butiran['perubahan']) && is_array($log->butiran['perubahan']))
                <table class="w-full text-xs border border-gray-200 rounded-lg overflow-hidden">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-left">
                            <th class="px-3 py-1.5 font-semibold w-1/4">Medan</th>
                            <th class="px-3 py-1.5 font-semibold text-red-500 w-5/12">Sebelum</th>
                            <th class="px-3 py-1.5 font-semibold text-green-600 w-5/12">Selepas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($log->butiran['perubahan'] as $medan => $nilai)
                        <tr>
                            <td class="px-3 py-1.5 font-mono text-gray-500 font-semibold align-top">{{ $medan }}</td>
                            <td class="px-3 py-1.5 align-top">
                                <span class="inline-block bg-red-50 text-red-700 rounded px-1.5 font-mono break-all">
                                    {{ $nilai['lama'] ?? '—' }}
                                </span>
                            </td>
                            <td class="px-3 py-1.5 align-top">
                                <span class="inline-block bg-green-50 text-green-700 rounded px-1.5 font-mono break-all">
                                    {{ $nilai['baru'] ?? '—' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @php $butiranLain = array_diff_key($log->butiran, ['perubahan' => null]); @endphp
                @if(!empty($butiranLain))
                <table class="w-full text-xs border border-gray-200 rounded-lg overflow-hidden mt-2">
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($butiranLain as $kunci => $nilai)
                        <tr>
                            <td class="px-3 py-1.5 font-mono font-semibold text-gray-400 w-1/3 bg-gray-50 align-top">{{ $kunci }}</td>
                            <td class="px-3 py-1.5 text-gray-700 align-top break-all">
                                @if(is_array($nilai)) <span class="font-mono text-gray-500">{{ json_encode($nilai, JSON_UNESCAPED_UNICODE) }}</span>
                                @elseif(is_bool($nilai)) <span class="{{ $nilai ? 'text-green-600' : 'text-red-500' }} font-semibold">{{ $nilai ? 'ya' : 'tidak' }}</span>
                                @elseif($nilai === null || $nilai === '') <span class="text-gray-300 italic">—</span>
                                @else {{ $nilai }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
                @else
                <table class="w-full text-xs border border-gray-200 rounded-lg overflow-hidden">
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($log->butiran as $kunci => $nilai)
                        <tr>
                            <td class="px-3 py-1.5 font-mono font-semibold text-gray-400 w-1/3 bg-gray-50 align-top">{{ $kunci }}</td>
                            <td class="px-3 py-1.5 text-gray-700 align-top break-all">
                                @if(is_array($nilai)) <span class="font-mono text-gray-500">{{ json_encode($nilai, JSON_UNESCAPED_UNICODE) }}</span>
                                @elseif(is_bool($nilai)) <span class="{{ $nilai ? 'text-green-600' : 'text-red-500' }} font-semibold">{{ $nilai ? 'ya' : 'tidak' }}</span>
                                @elseif($nilai === null || $nilai === '') <span class="text-gray-300 italic">—</span>
                                @else {{ $nilai }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endforeach
</div>

{{-- Pagination --}}
@if($logs->hasPages())
<div class="mt-6 bg-white rounded-xl shadow-sm px-5 py-4">
    {{ $logs->links() }}
</div>
@endif

@endif

@endsection

@push('scripts')
<script nonce="{{ $cspNonce }}">
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.toggle-butiran-tl');
    if (!btn) return;

    const id   = btn.dataset.logId;
    const box  = document.getElementById('tl-butiran-' + id);
    const icon = btn.querySelector('i');
    if (!box) return;

    const isHidden = box.classList.toggle('hidden');
    btn.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
    box.setAttribute('aria-hidden', isHidden ? 'true' : 'false');
    icon.classList.toggle('fa-chevron-down', isHidden);
    icon.classList.toggle('fa-chevron-up', !isHidden);
});
</script>
@endpush
