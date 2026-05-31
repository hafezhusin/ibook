@extends('layouts.app')

@section('title', 'Sesi Aktif')

@section('content')

{{-- Header --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            <i class="fa-solid fa-users-rectangle text-amber-400 mr-2" aria-hidden="true"></i>
            Sesi Log Masuk Aktif
        </h1>
        <p class="text-gray-500 text-sm mt-1">
            Pengguna yang sedang log masuk — sesi lapuk melebihi {{ $hayatMenit }} minit dibersihkan secara automatik
        </p>
    </div>
    <button onclick="window.location.reload()"
            class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition-colors flex-shrink-0">
        <i class="fa-solid fa-rotate-right" aria-hidden="true"></i> Muat Semula
    </button>
</div>

{{-- Stat cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-amber-400">
        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Jumlah Sesi Aktif</p>
        <p class="text-3xl font-bold text-gray-800">{{ $jumlahSesi }}</p>
        <p class="text-xs text-gray-400 mt-1">sesi dalam talian</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-400">
        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Pengguna Berbeza</p>
        <p class="text-3xl font-bold text-gray-800">{{ $jumlahUnik }}</p>
        <p class="text-xs text-gray-400 mt-1">pengguna unik</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-400">
        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Kaedah Log Masuk</p>
        <div class="flex flex-wrap gap-1.5 mt-1">
            @foreach(['google' => ['Google SSO','bg-blue-100 text-blue-700'], 'emel' => ['Emel','bg-slate-100 text-slate-600'], '2fa' => ['2FA','bg-purple-100 text-purple-700']] as $k => $v)
            @if(($mengikutKaedah[$k] ?? 0) > 0)
            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full {{ $v[1] }}">
                {{ $v[0] }}: {{ $mengikutKaedah[$k] }}
            </span>
            @endif
            @endforeach
            @if($jumlahSesi === 0)<span class="text-sm text-gray-400">—</span>@endif
        </div>
    </div>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">

    @if($sesiAktif->isEmpty())
    <div class="py-20 text-center text-gray-400">
        <i class="fa-solid fa-users-slash text-4xl mb-3 block" aria-hidden="true"></i>
        <p class="font-medium">Tiada pengguna yang sedang log masuk.</p>
        <p class="text-sm mt-1 text-gray-400">Semua sesi telah tamat atau belum ada yang log masuk.</p>
    </div>
    @else

    <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between text-xs text-gray-500">
        <span>{{ $jumlahSesi }} sesi aktif</span>
        <span class="hidden sm:inline text-gray-400">Dikemaskini terakhir — diurutkan mengikut aktiviti terkini</span>
    </div>

    <div class="overflow-x-auto">
    <table class="w-full text-sm" role="grid">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-200 text-left text-xs text-gray-500 uppercase tracking-wider">
                <th class="px-4 py-3 font-semibold" scope="col">Pengguna</th>
                <th class="px-4 py-3 font-semibold" scope="col">Kaedah</th>
                <th class="px-4 py-3 font-semibold hidden sm:table-cell" scope="col">Log Masuk</th>
                <th class="px-4 py-3 font-semibold" scope="col">Aktiviti Terakhir</th>
                <th class="px-4 py-3 font-semibold hidden lg:table-cell" scope="col">IP</th>
                <th class="px-4 py-3 font-semibold hidden xl:table-cell" scope="col">Browser / OS</th>
                <th class="px-4 py-3 font-semibold text-center" scope="col">Tindakan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
        @foreach($sesiAktif as $sesi)
        @php
            $isSendiri = $sesi->session_id === $sesiSendiri;
            $minit = $sesi->aktiviti_terakhir ? now()->diffInMinutes($sesi->aktiviti_terakhir) : null;
            $statusWarna = match(true) {
                $minit === null      => 'bg-gray-100 text-gray-500',
                $minit <= 5          => 'bg-green-100 text-green-700',
                $minit <= 15         => 'bg-amber-100 text-amber-700',
                default              => 'bg-red-100 text-red-600',
            };
        @endphp
        <tr class="hover:bg-gray-50 transition-colors {{ $isSendiri ? 'bg-amber-50/40' : '' }}">

            {{-- Pengguna --}}
            <td class="px-4 py-3 align-top">
                @if($sesi->pengguna)
                <div class="font-medium text-gray-800 text-sm flex items-center gap-1.5">
                    {{ $sesi->pengguna->name }}
                    @if($isSendiri)
                    <span class="text-[10px] font-semibold bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded">Anda</span>
                    @endif
                </div>
                <div class="text-xs text-gray-400 mt-0.5">{{ $sesi->pengguna->email }}</div>
                @if($sesi->pengguna->jabatan)
                <div class="text-xs text-gray-400">{{ $sesi->pengguna->jabatan }}</div>
                @endif
                <span class="inline-block mt-1 text-[10px] font-semibold px-1.5 py-0.5 rounded bg-slate-100 text-slate-500">
                    {{ ucfirst(str_replace('_', ' ', $sesi->pengguna->peranan ?? '')) }}
                </span>
                @else
                <span class="text-gray-400 italic text-xs">Pengguna dihapus</span>
                @endif
            </td>

            {{-- Kaedah --}}
            <td class="px-4 py-3 align-top">
                @php
                    [$kaedahLabel, $kaedahColor] = match($sesi->kaedah ?? '') {
                        'google' => ['Google SSO', 'bg-blue-100 text-blue-700'],
                        '2fa'    => ['Emel + 2FA', 'bg-purple-100 text-purple-700'],
                        'emel'   => ['Emel', 'bg-slate-100 text-slate-600'],
                        default  => ['—', 'bg-gray-100 text-gray-500'],
                    };
                @endphp
                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full {{ $kaedahColor }}">
                    {{ $kaedahLabel }}
                </span>
            </td>

            {{-- Log masuk pada --}}
            <td class="px-4 py-3 align-top whitespace-nowrap hidden sm:table-cell">
                @if($sesi->log_masuk_pada)
                <div class="text-xs text-gray-700 font-mono">{{ $sesi->log_masuk_pada->format('d/m/Y') }}</div>
                <div class="text-xs text-gray-400 font-mono">{{ $sesi->log_masuk_pada->format('H:i:s') }}</div>
                @else
                <span class="text-gray-400 text-xs">—</span>
                @endif
            </td>

            {{-- Aktiviti terakhir --}}
            <td class="px-4 py-3 align-top whitespace-nowrap">
                @if($sesi->aktiviti_terakhir)
                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full {{ $statusWarna }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $minit <= 5 ? 'bg-green-500 animate-pulse' : ($minit <= 15 ? 'bg-amber-500' : 'bg-red-400') }}" aria-hidden="true"></span>
                    {{ $sesi->aktiviti_terakhir->diffForHumans() }}
                </span>
                @else
                <span class="text-gray-400 text-xs">—</span>
                @endif
            </td>

            {{-- IP --}}
            <td class="px-4 py-3 text-gray-400 font-mono text-xs align-top whitespace-nowrap hidden lg:table-cell">
                {{ $sesi->ip_address ?? '—' }}
            </td>

            {{-- Browser / OS --}}
            <td class="px-4 py-3 align-top hidden xl:table-cell">
                <div class="text-xs text-gray-700">{{ $sesi->browser }}</div>
                <div class="text-xs text-gray-400">{{ $sesi->os }}</div>
            </td>

            {{-- Tindakan --}}
            <td class="px-4 py-3 text-center align-top">
                @if($isSendiri)
                <span class="text-xs text-gray-400 italic">Sesi anda</span>
                @elseif($sesi->pengguna)
                <form method="POST"
                      action="{{ route('sesi-aktif.paksa-log-keluar', $sesi->pengguna) }}"
                      onsubmit="return confirm('Paksa log keluar {{ addslashes($sesi->pengguna->name) }}?')">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 transition-colors">
                        <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                        Paksa Keluar
                    </button>
                </form>
                @else
                <span class="text-gray-300 text-xs">—</span>
                @endif
            </td>

        </tr>
        @endforeach
        </tbody>
    </table>
    </div>

    @endif
</div>

{{-- Info --}}
<div class="mt-4 bg-blue-50 border border-blue-100 rounded-xl px-5 py-4 flex gap-3 items-start text-sm text-blue-700">
    <i class="fa-solid fa-circle-info text-blue-400 flex-shrink-0 mt-0.5" aria-hidden="true"></i>
    <div>
        <strong>Nota:</strong> "Paksa Keluar" akan menamatkan <strong>semua sesi aktif</strong> pengguna tersebut.
        Mereka akan dipaksa ke halaman log masuk pada request seterusnya.
        Sesi lapuk melebihi <strong>{{ $hayatMenit }} minit</strong> tanpa aktiviti dibuang secara automatik.
    </div>
</div>

@endsection
