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

    {{-- Unit + Bahagian --}}
    <div class="text-sm text-gray-600 truncate">{{ $p->jabatan ?? '—' }}
        @if($p->bahagian)
        <div class="text-xs text-amber-600 font-medium mt-0.5">
            <i class="fa-solid fa-building-columns text-[9px]" aria-hidden="true"></i>
            {{ $p->bahagian->kod }}
        </div>
        @endif
    </div>

    {{-- Peranan --}}
    <div>
        <span class="text-xs font-semibold px-2 py-0.5 rounded-full
            {{ $p->peranan === 'pentadbir_sistem' ? 'bg-red-100 text-red-700' :
               ($p->peranan === 'urus_setia' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
            {{ $p->label_peranan }}
        </span>
    </div>

    {{-- Tarikh Diwujudkan --}}
    <div class="text-xs text-gray-500">
        {{ $p->created_at->format('d M Y') }}
        @if($p->last_login_at)
        <div class="text-gray-400 mt-0.5">
            <i class="fa-solid fa-clock mr-0.5" aria-hidden="true"></i>{{ $p->last_login_at->diffForHumans() }}
        </div>
        @endif
    </div>

    {{-- Status --}}
    @php $isPending = !$isAktif && is_null($p->last_login_at); @endphp
    <div>
        @if($isAktif)
        <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 flex items-center gap-1 w-fit">
            <i class="fa-solid fa-circle text-xs" aria-hidden="true"></i> Aktif
        </span>
        @elseif($isPending)
        <span class="text-xs font-semibold px-2 py-0.5 rounded-full flex items-center gap-1 w-fit"
              style="background:#fef9c3; color:#92400e">
            <i class="fa-solid fa-clock text-xs" aria-hidden="true"></i> Menunggu
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
            data-has-sso="{{ (str_ends_with($p->email, '@anm.gov.my') || $p->google_id) ? 'true' : 'false' }}"
            data-bahagian-id="{{ $p->bahagian_id ?? '' }}"
            class="text-amber-500 text-xs hover:underline" aria-label="Edit {{ $p->name }}">
            <i class="fa-solid fa-pen" aria-hidden="true"></i> Edit
        </button>
        @endif
        @if($p->id !== auth()->id() && auth()->user()->isPentadbir())
        @if($isAktif)
        <button type="button"
            data-open-nyahaktif="{{ $p->id }}"
            data-name="{{ addslashes($p->name) }}"
            class="text-red-500 text-xs hover:underline"
            aria-haspopup="dialog" aria-controls="modal-nyahaktifkan">
            <i class="fa-solid fa-ban" aria-hidden="true"></i> Nyahaktifkan
        </button>
        @else
        <form method="POST" action="{{ route('pengguna.toggle-aktif', $p) }}" class="inline">
            @csrf
            <button type="submit" class="text-green-600 text-xs hover:underline"
                data-confirm-toggle="Aktifkan semula akaun {{ addslashes($p->name) }}?">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i> Aktifkan
            </button>
        </form>
        @endif
        @endif
    </div>
</div>
