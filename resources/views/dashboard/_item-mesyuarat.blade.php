<li class="flex gap-4 px-6 py-3.5 hover:bg-gray-50 transition-colors">
    {{-- Tarikh --}}
    <div class="text-center min-w-[40px] flex-shrink-0" aria-hidden="true">
        <div class="text-xs text-gray-400 uppercase font-semibold leading-tight">{{ $m->tarikh->isoFormat('ddd') }}</div>
        <div class="text-lg font-extrabold leading-tight" style="color:{{ $warnaTarikh }}">
            {{ $m->tarikh->format('d') }}
        </div>
        <div class="text-xs text-gray-400">{{ $m->tarikh->isoFormat('MMM') }}</div>
    </div>

    {{-- Maklumat --}}
    <div class="flex-1 min-w-0">
        <div class="font-semibold text-gray-800 text-sm truncate">{{ $m->nama_mesyuarat }}</div>
        <div class="text-xs text-gray-500 mt-0.5 flex flex-wrap gap-x-2">
            <span>
                <i class="fa-solid fa-door-open text-amber-400 mr-1" aria-hidden="true"></i>
                {{ $m->bilik->nama ?? '—' }}
            </span>
            <span>&middot; {{ $m->masa_label }}</span>
            <span>&middot; {{ $m->bilangan_peserta }} orang</span>
        </div>
    </div>

    {{-- Tindakan --}}
    <div class="flex flex-col items-end justify-center gap-1.5 flex-shrink-0">
        <span class="badge-lulus text-xs">Disahkan</span>
        <a href="{{ route('tempahan.show', $m) }}"
           class="text-xs text-gray-400 hover:text-amber-500 transition-colors"
           aria-label="Butiran: {{ $m->nama_mesyuarat }}">
            Butiran →
        </a>
    </div>
</li>
