<div class="list-row list-data-row hover:bg-gray-50 transition"
    data-name="{{ strtolower($p->name) }}"
    data-unit="{{ strtolower($p->jabatan ?? '') }}"
    data-peranan="{{ $p->peranan }}"
    data-tarikh="{{ $p->created_at->format('Y-m-d') }}"
    data-status="{{ $isAktif ? 'aktif' : 'nyahaktif' }}">

    {{-- Nama + Emel --}}
    <div class="flex items-center gap-2 min-w-0">
        <input type="checkbox"
            class="checkbox-pengguna w-4 h-4 rounded cursor-pointer flex-shrink-0"
            style="accent-color:#f59e0b"
            value="{{ $p->id }}"
            {{ $p->id === auth()->id() ? 'disabled' : '' }}
            aria-label="Pilih {{ $p->name }}">
        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
            style="background: {{ !$isAktif ? '#9ca3af' : '#f59e0b' }}" aria-hidden="true">
            {{ strtoupper(substr($p->name, 0, 1)) }}
        </div>
        <div class="min-w-0">
            <div class="text-sm font-semibold text-gray-800 truncate">
                {{ $p->name }}
                @if($p->id === auth()->id())
                <span class="text-xs text-amber-500 font-normal">(anda)</span>
                @endif
            </div>
            <div class="text-xs text-gray-400 truncate">
                {{ auth()->user()->isPentadbir() ? $p->email : $p->masked_email }}
            </div>
        </div>
    </div>

    {{-- Unit --}}
    <div class="text-sm text-gray-600 truncate">{{ $p->jabatan ?? '—' }}</div>

    {{-- Peranan --}}
    <div>
        <span class="text-xs font-semibold px-2 py-0.5 rounded-full
            {{ $p->peranan === 'pentadbir_sistem' ? 'bg-red-100 text-red-700' :
               ($p->peranan === 'urus_setia' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
            {{ $p->label_peranan }}
        </span>
    </div>

    {{-- Tarikh Diwujudkan --}}
    <div class="text-xs text-gray-500">{{ $p->created_at->format('d M Y') }}</div>

    {{-- Status --}}
    <div>
        @if($isAktif)
        <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 flex items-center gap-1 w-fit">
            <i class="fa-solid fa-circle text-xs" aria-hidden="true"></i> Aktif
        </span>
        @else
        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-600 flex items-center gap-1 w-fit">
            <i class="fa-solid fa-ban text-xs" aria-hidden="true"></i> Nyahaktif
        </span>
        @endif
    </div>

    {{-- Tindakan --}}
    <div class="flex items-center gap-2 flex-wrap">
        @if(auth()->user()->isPentadbir())
        <button type="button"
            data-open-edit="{{ $p->id }}"
            data-name="{{ addslashes($p->name) }}"
            data-jabatan="{{ addslashes($p->jabatan ?? '') }}"
            data-peranan="{{ $p->peranan }}"
            data-aktif="{{ $p->aktif ? 'true' : 'false' }}"
            class="text-amber-500 text-xs hover:underline" aria-label="Edit {{ $p->name }}">
            <i class="fa-solid fa-pen" aria-hidden="true"></i> Edit
        </button>
        {{-- Reset: ikon sahaja, subordinat --}}
        <button type="button"
            data-open-reset="{{ $p->id }}"
            data-name="{{ addslashes($p->name) }}"
            class="text-gray-300 text-xs hover:text-gray-500 transition" title="Reset Kata Laluan"
            aria-label="Reset kata laluan {{ $p->name }}" aria-haspopup="dialog" aria-controls="modal-reset">
            <i class="fa-solid fa-key" aria-hidden="true"></i>
        </button>
        @endif
        @if($p->id !== auth()->id() && auth()->user()->isPentadbir())
        <form method="POST" action="{{ route('pengguna.toggle-aktif', $p) }}" class="inline">
            @csrf
            @if($isAktif)
            <button type="submit" class="text-red-500 text-xs hover:underline"
                data-confirm-toggle="Nyahaktifkan akaun {{ addslashes($p->name) }}?">
                <i class="fa-solid fa-ban" aria-hidden="true"></i> Nyahaktifkan
            </button>
            @else
            <button type="submit" class="text-green-600 text-xs hover:underline"
                data-confirm-toggle="Aktifkan semula akaun {{ addslashes($p->name) }}?">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i> Aktifkan
            </button>
            @endif
        </form>
        @endif
    </div>
</div>
