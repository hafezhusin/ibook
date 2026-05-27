{{-- ── Top Bar ─────────────────────────────────────────────────────── --}}
<header class="bg-white sticky top-0 z-20" role="banner" style="box-shadow:0 1px 0 #e5e7eb, 0 2px 8px rgba(0,0,0,0.04)">

    {{-- Government identity strip --}}
    @if($namaJabatan)
    <div class="px-6 py-1.5 border-b border-gray-100 flex items-center gap-2" style="background:#fafafa">
        @if($logoJabatan)
        <img src="{{ $logoJabatan }}" alt="" class="h-5 w-5 object-contain" aria-hidden="true">
        @else
        <i class="fa-solid fa-landmark text-amber-500 text-xs" aria-hidden="true"></i>
        @endif
        <span class="text-xs font-semibold text-gray-500 tracking-wide uppercase" style="font-size:10px; letter-spacing:0.06em">
            {{ $namaJabatan }}
        </span>
    </div>
    @endif

    {{-- Main header row --}}
    <div class="px-4 lg:px-6 py-3 flex items-center justify-between gap-3">

        {{-- Hamburger button (mobile sahaja) --}}
        <button type="button" id="btn-hamburger"
                class="lg:hidden flex-shrink-0 w-9 h-9 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors"
                aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="sidebar-utama">
            <i class="fa-solid fa-bars text-base" aria-hidden="true"></i>
        </button>

        {{-- Carian Global --}}
        <form method="GET" action="{{ route('carian') }}" role="search" aria-label="Carian sistem merentas semua modul" class="flex-1 lg:flex-none">
            <div class="relative">
                <label for="carian-global" class="sr-only">Cari tempahan, bilik atau pengguna</label>
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm" aria-hidden="true"></i>
                <input type="search" id="carian-global" name="q"
                    value="{{ request()->routeIs('carian') ? request('q') : '' }}"
                    placeholder="Cari semua modul…"
                    class="pl-9 pr-16 py-2 bg-gray-100 rounded-lg text-sm w-full lg:w-72 focus:outline-none focus:bg-white focus:ring-2 focus:ring-amber-400 transition-all"
                    aria-label="Carian global — tekan / untuk fokus"
                    autocomplete="off">
                <kbd id="search-hint"
                     class="absolute right-3 top-1/2 -translate-y-1/2 hidden sm:flex items-center gap-0.5 text-[10px] text-gray-400 font-mono border border-gray-300 rounded px-1.5 py-0.5 pointer-events-none select-none"
                     aria-hidden="true"
                     title="Tekan / untuk fokus ke carian">
                    /
                </kbd>
            </div>
        </form>

        {{-- Profil & tindakan --}}
        <div class="flex items-center gap-4">

            {{-- Toggle Tema: Light / Dark --}}
            <button type="button" id="btn-toggle-tema"
                class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-400"
                aria-label="Tukar tema cerah/gelap" title="Tukar tema">
                <i id="icon-tema" class="fa-solid fa-circle-half-stroke text-sm" aria-hidden="true"></i>
            </button>

            {{-- Maklumat pengguna + dropdown --}}
            <div class="relative flex items-center gap-3" id="profil-dropdown-wrap">
                <div class="text-right hidden lg:block" aria-hidden="true">
                    <div class="font-semibold text-sm text-gray-800">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-gray-500">{{ auth()->user()->label_peranan }}</div>
                </div>

                {{-- Avatar --}}
                <button type="button" id="profil-btn"
                        class="w-9 h-9 rounded-full flex items-center justify-center font-bold focus:outline-none focus:ring-2 focus:ring-amber-400"
                        style="background:var(--accent); color:#1a1a2e;"
                        aria-haspopup="menu" aria-expanded="false"
                        aria-label="Menu profil {{ auth()->user()->name }}">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </button>

                {{-- Dropdown menu --}}
                <div id="profil-menu"
                     class="hidden absolute right-0 top-12 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
                     role="menu" aria-labelledby="profil-btn">
                    <a href="{{ route('profil') }}"
                       class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition-colors"
                       role="menuitem">
                        <i class="fa-solid fa-user-pen w-4" aria-hidden="true"></i> Profil Saya
                    </a>
                    <div class="border-t border-gray-100 my-1"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors"
                                role="menuitem"
                                aria-label="Log keluar daripada akaun {{ auth()->user()->name }}">
                            <i class="fa-solid fa-right-from-bracket w-4" aria-hidden="true"></i> Log Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>{{-- end main header row --}}
</header>
