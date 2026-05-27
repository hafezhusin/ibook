@extends('layouts.app')

@section('title', 'Log Audit')

@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Log Audit</h1>
        <p class="text-gray-500 text-sm mt-1">
            <i class="fa-solid fa-shield-halved text-amber-400 mr-1" aria-hidden="true"></i>
            {{ number_format($jumlahKeseluruhan) }} rekod aktiviti sistem
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-2 self-start">
        {{-- Eksport Excel (menghormati penapis semasa) --}}
        <a href="{{ route('audit.excel', request()->query()) }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-lg border border-green-200 bg-green-50 text-green-700 hover:bg-green-100 transition-colors"
           title="Eksport log audit ke Excel (menghormati penapis semasa, maks 5,000 rekod)">
            <i class="fa-solid fa-file-excel" aria-hidden="true"></i> Eksport Excel
        </a>
        <div class="flex items-center gap-2 text-xs text-gray-400 bg-white border border-gray-200 rounded-lg px-3 py-2">
            <i class="fa-solid fa-link text-green-500" aria-hidden="true"></i>
            <span>SHA-256 hash chain &mdash; integriti boleh disahkan secara luar talian</span>
        </div>
    </div>
</div>

{{-- ── Banner Amaran Keselamatan ──────────────────────────────────────── --}}
@if($amalanBahaya > 0)
<div class="mb-5 bg-red-50 border border-red-200 rounded-xl overflow-hidden" role="alert" aria-live="polite">
    <div class="flex items-start gap-4 p-4">
        <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
            <i class="fa-solid fa-triangle-exclamation text-red-500 text-lg" aria-hidden="true"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-bold text-red-700 text-sm">
                {{ $amalanBahaya }} percubaan log masuk mencurigai dalam 24 jam terakhir
            </p>
            <p class="text-red-500 text-xs mt-0.5">
                Terdapat aktiviti log masuk gagal yang luar biasa. Semak rekod di bawah dan ambil tindakan jika perlu.
            </p>
            @if($ipMencurigai->isNotEmpty())
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($ipMencurigai as $ipRow)
                <a href="{{ route('audit.index', ['tindakan' => 'log_masuk_gagal', 'carian' => $ipRow->ip_address]) }}"
                   class="inline-flex items-center gap-1 text-xs font-mono font-semibold bg-red-100 text-red-700 border border-red-200 rounded px-2 py-0.5 hover:bg-red-200 transition-colors">
                    <i class="fa-solid fa-wifi text-red-400" aria-hidden="true"></i>
                    {{ $ipRow->ip_address }}
                    <span class="bg-red-200 text-red-800 rounded px-1">{{ $ipRow->kiraan }}×</span>
                </a>
                @endforeach
            </div>
            @endif
        </div>
        <a href="{{ route('audit.index', ['tindakan' => 'log_masuk_gagal']) }}"
           class="flex-shrink-0 text-xs font-semibold text-red-600 hover:text-red-800 underline whitespace-nowrap">
            Lihat Semua →
        </a>
    </div>
</div>
@endif

{{-- ── Filter Panel ──────────────────────────────────────────── --}}
<form method="GET" action="{{ route('audit.index') }}" class="bg-white rounded-xl shadow-sm p-4 mb-5" aria-label="Tapis log audit">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

        {{-- Carian teks --}}
        <div class="lg:col-span-2">
            <label for="carian" class="sr-only">Carian</label>
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm" aria-hidden="true"></i>
                <input type="text" id="carian" name="carian"
                    value="{{ request('carian') }}"
                    placeholder="Cari penerangan, tindakan atau IP..."
                    class="form-input pl-9 text-sm">
            </div>
        </div>

        {{-- Filter tindakan --}}
        <div>
            <label for="tindakan" class="sr-only">Tindakan</label>
            <select id="tindakan" name="tindakan" class="form-input text-sm">
                <option value="">Semua Tindakan</option>
                @foreach($senaraiTindakan as $t)
                <option value="{{ $t }}" {{ request('tindakan') === $t ? 'selected' : '' }}>
                    {{ str_replace('_', ' ', ucfirst($t)) }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Filter pengguna --}}
        <div>
            <label for="pengguna_id" class="sr-only">Pengguna</label>
            <select id="pengguna_id" name="pengguna_id" class="form-input text-sm">
                <option value="">Semua Pengguna</option>
                @foreach($senaraiPengguna as $p)
                <option value="{{ $p->id }}" {{ request('pengguna_id') == $p->id ? 'selected' : '' }}>
                    {{ $p->name }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Tarikh dari --}}
        <div>
            <label for="tarikh_dari" class="sr-only">Tarikh dari</label>
            <input type="date" id="tarikh_dari" name="tarikh_dari"
                value="{{ request('tarikh_dari') }}"
                class="form-input text-sm"
                placeholder="Dari tarikh">
        </div>

        {{-- Tarikh hingga --}}
        <div>
            <label for="tarikh_hingga" class="sr-only">Tarikh hingga</label>
            <input type="date" id="tarikh_hingga" name="tarikh_hingga"
                value="{{ request('tarikh_hingga') }}"
                class="form-input text-sm"
                placeholder="Hingga tarikh">
        </div>

        {{-- Butang --}}
        <div class="sm:col-span-2 flex gap-2">
            <button type="submit" class="btn-primary text-sm flex items-center gap-1.5">
                <i class="fa-solid fa-filter" aria-hidden="true"></i> Tapis
            </button>
            @if(request()->hasAny(['carian','tindakan','pengguna_id','tarikh_dari','tarikh_hingga']))
            <a href="{{ route('audit.index') }}" class="btn-secondary text-sm flex items-center gap-1.5">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i> Set Semula
            </a>
            @endif
        </div>

    </div>
</form>

{{-- ── Keputusan ──────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">

    @if($logs->isEmpty())
    <div class="py-16 text-center text-gray-400">
        <i class="fa-solid fa-shield-halved text-4xl mb-3 block" aria-hidden="true"></i>
        <p class="font-medium">Tiada rekod log ditemui.</p>
        <p class="text-sm mt-1">Cuba ubah kriteria tapisan.</p>
    </div>

    @else

    {{-- Pengepala maklumat --}}
    <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between text-xs text-gray-500">
        <span>Menunjukkan {{ $logs->firstItem() }}–{{ $logs->lastItem() }} daripada {{ number_format($logs->total()) }} rekod</span>
        <span class="hidden sm:inline">Dikemaskini terbaru di atas</span>
    </div>

    {{-- Jadual log --}}
    <div class="overflow-x-auto">
    <table class="w-full text-sm" role="grid" aria-label="Senarai log audit">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-200 text-left text-xs text-gray-500 uppercase tracking-wider">
                <th class="px-4 py-3 font-semibold" scope="col">Masa</th>
                <th class="px-4 py-3 font-semibold" scope="col">Pengguna</th>
                <th class="px-4 py-3 font-semibold" scope="col">Tindakan</th>
                <th class="px-4 py-3 font-semibold" scope="col">Penerangan</th>
                <th class="px-4 py-3 font-semibold hidden lg:table-cell" scope="col">IP</th>
                <th class="px-4 py-3 font-semibold text-center hidden xl:table-cell" scope="col">Hash</th>
                <th class="px-4 py-3 font-semibold text-center" scope="col">Butiran</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
        @foreach($logs as $log)
        @php
            $isBahaya = in_array($log->tindakan, ['log_masuk_gagal', 'percubaan_akaun_nyahaktif']);
        @endphp
        <tr class="hover:bg-gray-50 transition-colors {{ $isBahaya ? 'bg-red-50/60' : '' }}" id="log-{{ $log->id }}">

            {{-- Masa --}}
            <td class="px-4 py-3 text-gray-500 whitespace-nowrap align-top">
                @if($isBahaya)
                <i class="fa-solid fa-shield-exclamation text-red-400 text-xs mr-1" aria-hidden="true" title="Peristiwa keselamatan"></i>
                @endif
                <div class="font-mono text-xs">{{ $log->dicipta_pada->format('d/m/Y') }}</div>
                <div class="font-mono text-xs text-gray-400">{{ $log->dicipta_pada->format('H:i:s') }}</div>
            </td>

            {{-- Pengguna --}}
            <td class="px-4 py-3 align-top">
                @if($log->pengguna)
                <div class="font-medium text-gray-800 text-xs">{{ $log->pengguna->name }}</div>
                @elseif($isBahaya && isset($log->butiran['email_dicuba']))
                <div class="font-mono text-xs text-red-500">{{ $log->butiran['email_dicuba'] }}</div>
                <div class="text-xs text-gray-400 italic">(tidak berdaftar)</div>
                @else
                <span class="text-gray-400 text-xs italic">Sistem</span>
                @endif
            </td>

            {{-- Tindakan --}}
            <td class="px-4 py-3 align-top whitespace-nowrap">
                @php
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
                        str_contains($log->tindakan, 'reset')          => 'bg-amber-50 text-amber-700',
                        default                                         => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <span class="inline-block {{ $badgeColor }} text-xs font-semibold px-2 py-0.5 rounded-md font-mono">
                    {{ $log->tindakan }}
                </span>
            </td>

            {{-- Penerangan --}}
            <td class="px-4 py-3 text-gray-700 align-top max-w-xs">
                <span class="text-xs leading-relaxed">{{ $log->penerangan }}</span>
            </td>

            {{-- IP --}}
            <td class="px-4 py-3 text-gray-400 font-mono text-xs align-top whitespace-nowrap hidden lg:table-cell">
                {{ $log->ip_address ?? '—' }}
            </td>

            {{-- Hash indicator --}}
            <td class="px-4 py-3 text-center align-top hidden xl:table-cell">
                <span title="{{ $log->record_hash }}"
                      class="inline-block w-5 h-5 rounded-full {{ $log->record_hash ? 'bg-green-100' : 'bg-gray-100' }} flex items-center justify-center mx-auto"
                      aria-label="{{ $log->record_hash ? 'Hash wujud' : 'Tiada hash' }}">
                    <i class="fa-solid {{ $log->record_hash ? 'fa-lock text-green-500' : 'fa-unlock text-gray-400' }} text-xs" aria-hidden="true"></i>
                </span>
            </td>

            {{-- Butiran expand --}}
            <td class="px-4 py-3 text-center align-top">
                @if($log->butiran)
                <button type="button"
                    class="text-amber-500 hover:text-amber-600 transition text-xs font-medium"
                    onclick="toggleButiran({{ $log->id }})"
                    aria-expanded="false"
                    aria-controls="butiran-{{ $log->id }}">
                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                </button>
                @else
                <span class="text-gray-300 text-xs">—</span>
                @endif
            </td>

        </tr>

        {{-- Butiran expandable row --}}
        @if($log->butiran)
        <tr id="butiran-{{ $log->id }}" class="hidden {{ $isBahaya ? 'bg-red-50/80' : 'bg-amber-50/50' }}" aria-hidden="true">
            <td colspan="7" class="px-6 py-3 border-b {{ $isBahaya ? 'border-red-100' : 'border-amber-100' }}">
                <pre class="text-xs text-gray-700 bg-white border border-gray-200 rounded-lg p-3 overflow-x-auto font-mono leading-relaxed whitespace-pre-wrap">{{ json_encode($log->butiran, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                @if($log->record_hash)
                <p class="text-xs text-gray-400 mt-2 font-mono">
                    <span class="text-gray-500 font-semibold">hash:</span> {{ $log->record_hash }}
                </p>
                @endif
            </td>
        </tr>
        @endif

        @endforeach
        </tbody>
    </table>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $logs->links() }}
    </div>
    @endif

    @endif
</div>

@endsection

@push('scripts')
<script nonce="{{ $cspNonce }}">
function toggleButiran(id) {
    const row  = document.getElementById('butiran-' + id);
    const btn  = document.querySelector('[aria-controls="butiran-' + id + '"]');
    const icon = btn.querySelector('i');
    const open = row.classList.toggle('hidden');
    btn.setAttribute('aria-expanded', open ? 'false' : 'true');
    row.setAttribute('aria-hidden', open ? 'true' : 'false');
    icon.classList.toggle('fa-chevron-down', open);
    icon.classList.toggle('fa-chevron-up', !open);
}
</script>
@endpush
