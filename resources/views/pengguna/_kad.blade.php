<article class="bg-white rounded-xl shadow-sm p-5 border-2 transition
    {{ !$isAktif ? 'border-red-100' : 'border-transparent' }}"
    aria-labelledby="pengguna-{{ $p->id }}">

    <div class="flex items-start gap-3">
        {{-- Checkbox --}}
        <div class="pt-0.5 flex-shrink-0">
            <input type="checkbox"
                class="checkbox-pengguna w-4 h-4 rounded cursor-pointer"
                style="accent-color:#f59e0b"
                value="{{ $p->id }}"
                {{ $p->id === auth()->id() ? 'disabled title=Akaun anda sendiri' : '' }}
                aria-label="Pilih {{ $p->name }}">
        </div>

        {{-- Avatar --}}
        <div class="w-11 h-11 rounded-full flex items-center justify-center text-white font-bold text-base flex-shrink-0"
            style="background: {{ !$isAktif ? '#9ca3af' : '#f59e0b' }}"
            aria-hidden="true">
            {{ strtoupper(substr($p->name, 0, 1)) }}
        </div>

        {{-- Maklumat --}}
        <div class="flex-1 min-w-0">
            <div id="pengguna-{{ $p->id }}" class="font-bold text-gray-800 truncate text-sm">
                {{ $p->name }}
                @if($p->id === auth()->id())
                <span class="text-xs text-amber-500 font-normal">(anda)</span>
                @endif
            </div>
            <div class="text-xs text-gray-500 truncate">{{ $p->jabatan ?? 'Tiada unit' }}</div>
            <div class="text-xs text-gray-400 truncate">
                {{ auth()->user()->isPentadbir() ? $p->email : $p->masked_email }}
            </div>
        </div>
    </div>

    {{-- Badge peranan + status --}}
    <div class="mt-3 flex items-center gap-2 flex-wrap">
        <span class="text-xs font-semibold px-2 py-0.5 rounded-full
            {{ $p->peranan === 'pentadbir_sistem' ? 'bg-red-100 text-red-700' :
               ($p->peranan === 'urus_setia' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
            {{ $p->label_peranan }}
        </span>
        @if(!$isAktif)
        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-600 flex items-center gap-1">
            <i class="fa-solid fa-ban text-xs" aria-hidden="true"></i> Dinyahaktifkan
        </span>
        @else
        <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 flex items-center gap-1">
            <i class="fa-solid fa-circle text-xs" aria-hidden="true"></i> Aktif
        </span>
        @endif
    </div>

    {{-- Tarikh diwujudkan --}}
    <div class="mt-2 text-xs text-gray-400 flex items-center gap-1">
        <i class="fa-regular fa-calendar" aria-hidden="true"></i>
        Diwujudkan: {{ $p->created_at->format('d M Y') }}
    </div>

    {{-- Butang tindakan --}}
    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between gap-2">
        <div class="flex gap-2 items-center">
            @if(auth()->user()->isPentadbir())
            <button type="button"
                data-open-edit="{{ $p->id }}"
                data-name="{{ addslashes($p->name) }}"
                data-jabatan="{{ addslashes($p->jabatan ?? '') }}"
                data-peranan="{{ $p->peranan }}"
                data-aktif="{{ $p->aktif ? 'true' : 'false' }}"
                class="text-amber-500 text-xs hover:underline"
                aria-label="Edit pengguna — {{ $p->name }}"
                aria-haspopup="dialog" aria-controls="modal-edit">
                <i class="fa-solid fa-pen" aria-hidden="true"></i> Edit
            </button>
            {{-- Reset Kata Laluan — subordinat: ikon sahaja dengan tooltip --}}
            <button type="button"
                data-open-reset="{{ $p->id }}"
                data-name="{{ addslashes($p->name) }}"
                class="text-gray-300 text-xs hover:text-gray-500 transition"
                title="Reset Kata Laluan"
                aria-label="Reset kata laluan — {{ $p->name }}"
                aria-haspopup="dialog" aria-controls="modal-reset">
                <i class="fa-solid fa-key" aria-hidden="true"></i>
            </button>
            @else
            {{-- Urus Setia boleh reset kata laluan sendiri sahaja --}}
            @if($p->id === auth()->id())
            <button type="button"
                data-open-reset="{{ $p->id }}"
                data-name="{{ addslashes($p->name) }}"
                class="text-gray-300 text-xs hover:text-gray-500 transition"
                title="Reset Kata Laluan"
                aria-label="Reset kata laluan — {{ $p->name }}"
                aria-haspopup="dialog" aria-controls="modal-reset">
                <i class="fa-solid fa-key" aria-hidden="true"></i>
            </button>
            @endif
            @endif
        </div>

        @if($p->id !== auth()->id() && auth()->user()->isPentadbir())
        <form method="POST" action="{{ route('pengguna.toggle-aktif', $p) }}">
            @csrf
            @if($isAktif)
            <button type="submit"
                class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition"
                data-confirm-toggle="Nyahaktifkan akaun {{ addslashes($p->name) }}?">
                <i class="fa-solid fa-ban" aria-hidden="true"></i> Nyahaktifkan
            </button>
            @else
            <button type="submit"
                class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition"
                data-confirm-toggle="Aktifkan semula akaun {{ addslashes($p->name) }}?">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i> Aktifkan
            </button>
            @endif
        </form>
        @endif
    </div>
</article>
