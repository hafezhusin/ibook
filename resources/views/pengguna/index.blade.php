@extends('layouts.app')

@section('title', 'Pengguna')

@push('styles')
<style>
    .tab-btn { padding: 8px 18px; border-radius: 8px; font-size: 13px; font-weight: 600;
               cursor: pointer; border: none; background: transparent; color: #6b7280; transition: all .15s; }
    .tab-btn.aktif-tab { background: #1a1a2e; color: #fff; }
    .tab-btn:hover:not(.aktif-tab) { background: #f3f4f6; }

    .view-btn { padding: 6px 10px; border-radius: 6px; border: 1.5px solid #e5e7eb;
                background: white; cursor: pointer; color: #6b7280; transition: all .15s; }
    .view-btn.aktif-view { background: #1a1a2e; color: #fff; border-color: #1a1a2e; }

    /* List view table */
    .list-row { display: grid; grid-template-columns: 2fr 1.5fr 1fr 1fr 1fr 1fr; align-items: center;
                padding: 12px 16px; border-bottom: 1px solid #f3f4f6; gap: 8px; }
    .list-row:last-child { border-bottom: none; }
    .list-header { background: #f9fafb; font-size: 11px; font-weight: 700;
                   color: #9ca3af; text-transform: uppercase; letter-spacing: .05em; }
    .sort-btn { display: inline-flex; align-items: center; gap: 4px; cursor: pointer;
                background: none; border: none; padding: 0; font: inherit; color: inherit;
                text-transform: uppercase; letter-spacing: .05em; font-size: 11px; font-weight: 700; }
    .sort-btn:hover { color: #1a1a2e; }
    .sort-btn.sorted { color: #f59e0b; }
    .sort-icon { font-size: 10px; opacity: .5; }
    .sort-btn.sorted .sort-icon { opacity: 1; }
</style>
@endpush

@section('content')

{{-- ── Header ── --}}
<div class="flex items-center justify-between mb-5 gap-3 flex-wrap">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Pengguna</h1>
        <p class="text-gray-500 text-sm mt-1">Pengurusan pengguna dan peranan</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('pengguna.import-csv') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 bg-white border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 hover:border-amber-300 transition-colors shadow-sm">
            <i class="fa-solid fa-file-csv text-green-600" aria-hidden="true"></i>
            Import CSV
        </a>
        @if(auth()->user()->isPentadbir())
        <button type="button" id="btn-buka-tambah"
            class="btn-primary" aria-haspopup="dialog" aria-controls="modal-tambah">
            <i class="fa-solid fa-plus" aria-hidden="true"></i> Tambah Pengguna
        </button>
        @endif
    </div>
</div>

{{-- Flash messages --}}
@if(session('success'))
<div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm flex items-center gap-2" role="alert">
    <i class="fa-solid fa-circle-check text-green-500" aria-hidden="true"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm flex items-center gap-2" role="alert">
    <i class="fa-solid fa-circle-xmark text-red-500" aria-hidden="true"></i> {{ session('error') }}
</div>
@endif

{{-- ── Carian + Filter Unit --}}
<form id="form-carian" method="GET" action="{{ route('pengguna.index') }}" class="mb-4 flex flex-wrap gap-2 items-center" role="search">

    {{-- Carian teks --}}
    <div class="relative w-full md:w-72">
        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm" aria-hidden="true"></i>
        <input type="search" id="carian-pengguna" name="cari"
            value="{{ $cari }}"
            placeholder="Cari nama atau emel..."
            class="form-input pl-9 pr-8 text-sm w-full"
            aria-label="Cari pengguna"
            autocomplete="off">
        @if($cari !== '')
        <button type="button" onclick="document.getElementById('carian-pengguna').value=''; document.getElementById('form-carian').submit();"
            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
            aria-label="Kosongkan carian" title="Kosongkan carian">
            <i class="fa-solid fa-xmark text-xs" aria-hidden="true"></i>
        </button>
        @endif
    </div>

    {{-- Dropdown Unit --}}
    <div class="relative">
        <select id="filter-unit" name="unit"
            class="form-input text-sm pr-8 appearance-none cursor-pointer min-w-[200px]"
            aria-label="Tapis mengikut unit">
            <option value="">— Semua Unit —</option>
            @foreach($units as $u)
            <option value="{{ $u }}" {{ $unit === $u ? 'selected' : '' }}>{{ $u }}</option>
            @endforeach
        </select>
        <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none" aria-hidden="true"></i>
    </div>

    {{-- Papar butang Kosongkan jika ada filter aktif --}}
    @if($cari !== '' || $unit !== '')
    <a href="{{ route('pengguna.index') }}"
        class="inline-flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-700 border border-gray-300 rounded-lg px-3 py-2 bg-white transition-colors"
        title="Kosongkan semua penapis">
        <i class="fa-solid fa-filter-circle-xmark" aria-hidden="true"></i> Kosongkan Penapis
    </a>
    @endif

</form>

{{-- ── Tab + View Toggle Bar ── --}}
<div class="flex items-center justify-between mb-4">

    {{-- Tab --}}
    <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl" role="tablist">
        <button type="button" id="tab-aktif" role="tab"
            class="tab-btn aktif-tab"
            aria-selected="true" aria-controls="panel-aktif"
            data-tab="aktif">
            <i class="fa-solid fa-circle-check mr-1" aria-hidden="true"></i>
            Aktif
            <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full"
                style="background:rgba(255,255,255,.25)">{{ $penggunaAktif->total() }}</span>
        </button>
        <button type="button" id="tab-pending" role="tab"
            class="tab-btn"
            aria-selected="false" aria-controls="panel-pending"
            data-tab="pending">
            <i class="fa-solid fa-clock mr-1" aria-hidden="true"></i>
            Menunggu Kelulusan
            @if($penggunaPending->total() > 0)
            <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full font-bold"
                  style="background:#f59e0b; color:#1a1a2e">
                {{ $penggunaPending->total() }}
            </span>
            @endif
        </button>
        <button type="button" id="tab-nyahaktif" role="tab"
            class="tab-btn"
            aria-selected="false" aria-controls="panel-nyahaktif"
            data-tab="nyahaktif">
            <i class="fa-solid fa-ban mr-1" aria-hidden="true"></i>
            Dinyahaktifkan
            @if($penggunaNyahaktif->total() > 0)
            <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full bg-red-500 text-white">
                {{ $penggunaNyahaktif->total() }}
            </span>
            @endif
        </button>
    </div>

    {{-- View toggle + Toolbar pukal --}}
    <div class="flex items-center gap-3">

        {{-- Toolbar Pukal (pentadbir sahaja) --}}
        @if(auth()->user()->isPentadbir())
        <div id="toolbar-pukal" class="hidden items-center gap-2">
            <span class="text-sm font-semibold text-gray-600">
                <span id="kiraan-pilihan">0</span> dipilih
            </span>
            <button type="button" id="btn-pilih-semua"
                class="text-xs text-amber-600 underline hover:no-underline">Semua</button>
            <button type="button" id="btn-nyahpilih-semua"
                class="text-xs text-gray-400 underline hover:no-underline">Nyahpilih</button>
            <form id="form-bulk-aktif" method="POST" action="{{ route('pengguna.bulk-aktif') }}" class="flex gap-1">
                @csrf
                <div id="hidden-ids"></div>
                <input type="hidden" name="tindakan" id="bulk-tindakan" value="">
                <button type="button" id="btn-bulk-aktifkan"
                    class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-lg bg-green-100 text-green-700 hover:bg-green-200">
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i> Aktifkan
                </button>
                <button type="button" id="btn-bulk-nyahaktifkan"
                    class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-lg bg-red-100 text-red-700 hover:bg-red-200">
                    <i class="fa-solid fa-ban" aria-hidden="true"></i> Nyahaktifkan
                </button>
            </form>
        </div>
        @endif

        {{-- View Toggle --}}
        <div class="flex gap-1" role="group" aria-label="Pilih paparan">
            <button type="button" id="btn-kad" class="view-btn aktif-view"
                data-view="kad" aria-pressed="true" aria-label="Paparan kad">
                <i class="fa-solid fa-grip" aria-hidden="true"></i>
            </button>
            <button type="button" id="btn-senarai" class="view-btn"
                data-view="senarai" aria-pressed="false" aria-label="Paparan senarai">
                <i class="fa-solid fa-list" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════ --}}
{{-- PANEL: AKTIF                                --}}
{{-- ════════════════════════════════════════════ --}}
<div id="panel-aktif" role="tabpanel" aria-labelledby="tab-aktif">

    {{-- KAD VIEW --}}
    <div id="kad-aktif" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
        @forelse($penggunaAktif as $p)
        @include('pengguna._kad', ['p' => $p, 'isAktif' => true])
        @empty
        <div class="col-span-3 text-center py-12 text-gray-400">
            <i class="fa-solid fa-users text-3xl mb-2" aria-hidden="true"></i>
            <p>Tiada pengguna aktif</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination Aktif (kad view) --}}
    @if($penggunaAktif->hasPages())
    <div id="pagination-kad-aktif" class="mb-4">
        {{ $penggunaAktif->appends(request()->except('page_aktif'))->links() }}
    </div>
    @endif

    {{-- LIST VIEW --}}
    <div id="list-aktif" class="hidden bg-white rounded-xl shadow-sm overflow-hidden mb-8">
        <div class="list-row list-header rounded-t-xl">
            <div class="flex items-center gap-2">
                <input type="checkbox" id="cb-semua-list"
                    style="accent-color:#f59e0b" aria-label="Pilih semua">
                <button class="sort-btn" data-col="name" data-panel="aktif" aria-label="Isih mengikut nama">
                    Pengguna <span class="sort-icon">⇅</span>
                </button>
            </div>
            <button class="sort-btn" data-col="unit" data-panel="aktif" aria-label="Isih mengikut unit">
                Unit <span class="sort-icon">⇅</span>
            </button>
            <button class="sort-btn" data-col="peranan" data-panel="aktif" aria-label="Isih mengikut peranan">
                Peranan <span class="sort-icon">⇅</span>
            </button>
            <button class="sort-btn" data-col="tarikh" data-panel="aktif" aria-label="Isih mengikut tarikh diwujudkan">
                Tarikh Diwujudkan <span class="sort-icon">⇅</span>
            </button>
            <button class="sort-btn" data-col="status" data-panel="aktif" aria-label="Isih mengikut status">
                Status <span class="sort-icon">⇅</span>
            </button>
            <div>Tindakan</div>
        </div>
        @forelse($penggunaAktif as $p)
        @include('pengguna._baris', ['p' => $p, 'isAktif' => true])
        @empty
        <div class="p-8 text-center text-gray-400">Tiada pengguna aktif</div>
        @endforelse
    </div>

    {{-- Pagination Aktif (list view) --}}
    @if($penggunaAktif->hasPages())
    <div id="pagination-list-aktif" class="hidden mb-4">
        {{ $penggunaAktif->appends(request()->except('page_aktif'))->links() }}
    </div>
    @endif

</div>

{{-- ════════════════════════════════════════════ --}}
{{-- PANEL: MENUNGGU KELULUSAN                   --}}
{{-- ════════════════════════════════════════════ --}}
<div id="panel-pending" role="tabpanel" aria-labelledby="tab-pending" class="hidden">

    {{-- Info banner --}}
    <div class="mb-4 p-4 rounded-xl flex items-start gap-3 text-sm"
         style="background:#fefce8; border:1px solid #fde68a; color:#92400e">
        <i class="fa-solid fa-clock mt-0.5 flex-shrink-0" style="color:#f59e0b" aria-hidden="true"></i>
        <div>
            <span class="font-semibold">Pengguna ini mendaftar melalui MyGovUC SSO</span> dan sedang menunggu kelulusan pentadbir.
            Semak sama ada mereka warga BPTM, kemudian klik <strong>Aktifkan</strong> untuk memberi akses.
        </div>
    </div>

    {{-- KAD VIEW --}}
    <div id="kad-pending" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
        @forelse($penggunaPending as $p)
        @include('pengguna._kad', ['p' => $p, 'isAktif' => false])
        @empty
        <div class="col-span-3 text-center py-12 text-gray-400">
            <i class="fa-solid fa-circle-check text-3xl mb-2 text-green-400" aria-hidden="true"></i>
            <p>Tiada pendaftaran baharu menunggu kelulusan</p>
        </div>
        @endforelse
    </div>

    @if($penggunaPending->hasPages())
    <div id="pagination-kad-pending" class="mb-4">
        {{ $penggunaPending->appends(request()->except('page_pending'))->links() }}
    </div>
    @endif

    {{-- LIST VIEW --}}
    <div id="list-pending" class="hidden bg-white rounded-xl shadow-sm overflow-hidden mb-8">
        <div class="list-row list-header rounded-t-xl">
            <div class="flex items-center gap-2">
                <input type="checkbox" id="cb-semua-list-pending"
                    style="accent-color:#f59e0b" aria-label="Pilih semua">
                <button class="sort-btn" data-col="name" data-panel="pending">Pengguna <span class="sort-icon">⇅</span></button>
            </div>
            <button class="sort-btn" data-col="unit" data-panel="pending">Unit <span class="sort-icon">⇅</span></button>
            <button class="sort-btn" data-col="peranan" data-panel="pending">Peranan <span class="sort-icon">⇅</span></button>
            <button class="sort-btn" data-col="tarikh" data-panel="pending">Tarikh Daftar <span class="sort-icon">⇅</span></button>
            <button class="sort-btn" data-col="status" data-panel="pending">Status <span class="sort-icon">⇅</span></button>
            <div>Tindakan</div>
        </div>
        @forelse($penggunaPending as $p)
        @include('pengguna._baris', ['p' => $p, 'isAktif' => false])
        @empty
        <div class="p-8 text-center text-gray-400">Tiada pendaftaran baharu</div>
        @endforelse
    </div>

    @if($penggunaPending->hasPages())
    <div id="pagination-list-pending" class="hidden mb-4">
        {{ $penggunaPending->appends(request()->except('page_pending'))->links() }}
    </div>
    @endif

</div>

{{-- ════════════════════════════════════════════ --}}
{{-- PANEL: DINYAHAKTIFKAN                       --}}
{{-- ════════════════════════════════════════════ --}}
<div id="panel-nyahaktif" role="tabpanel" aria-labelledby="tab-nyahaktif" class="hidden">

    {{-- KAD VIEW --}}
    <div id="kad-nyahaktif" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
        @forelse($penggunaNyahaktif as $p)
        @include('pengguna._kad', ['p' => $p, 'isAktif' => false])
        @empty
        <div class="col-span-3 text-center py-12 text-gray-400">
            <i class="fa-solid fa-circle-check text-3xl mb-2 text-green-400" aria-hidden="true"></i>
            <p>Tiada akaun yang dinyahaktifkan</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination Nyahaktif (kad view) --}}
    @if($penggunaNyahaktif->hasPages())
    <div id="pagination-kad-nyahaktif" class="mb-4">
        {{ $penggunaNyahaktif->appends(request()->except('page_nyahaktif'))->links() }}
    </div>
    @endif

    {{-- LIST VIEW --}}
    <div id="list-nyahaktif" class="hidden bg-white rounded-xl shadow-sm overflow-hidden mb-8">
        <div class="list-row list-header rounded-t-xl">
            <div class="flex items-center gap-2">
                <input type="checkbox" id="cb-semua-list-nyahaktif"
                    style="accent-color:#f59e0b" aria-label="Pilih semua">
                <button class="sort-btn" data-col="name" data-panel="nyahaktif" aria-label="Isih mengikut nama">
                    Pengguna <span class="sort-icon">⇅</span>
                </button>
            </div>
            <button class="sort-btn" data-col="unit" data-panel="nyahaktif" aria-label="Isih mengikut unit">
                Unit <span class="sort-icon">⇅</span>
            </button>
            <button class="sort-btn" data-col="peranan" data-panel="nyahaktif" aria-label="Isih mengikut peranan">
                Peranan <span class="sort-icon">⇅</span>
            </button>
            <button class="sort-btn" data-col="tarikh" data-panel="nyahaktif" aria-label="Isih mengikut tarikh diwujudkan">
                Tarikh Diwujudkan <span class="sort-icon">⇅</span>
            </button>
            <button class="sort-btn" data-col="status" data-panel="nyahaktif" aria-label="Isih mengikut status">
                Status <span class="sort-icon">⇅</span>
            </button>
            <div>Tindakan</div>
        </div>
        @forelse($penggunaNyahaktif as $p)
        @include('pengguna._baris', ['p' => $p, 'isAktif' => false])
        @empty
        <div class="p-8 text-center text-gray-400">Tiada akaun yang dinyahaktifkan</div>
        @endforelse
    </div>

    {{-- Pagination Nyahaktif (list view) --}}
    @if($penggunaNyahaktif->hasPages())
    <div id="pagination-list-nyahaktif" class="hidden mb-4">
        {{ $penggunaNyahaktif->appends(request()->except('page_nyahaktif'))->links() }}
    </div>
    @endif

</div>

{{-- ── Keterangan Peranan ── --}}
<section class="bg-white rounded-xl shadow-sm p-6" aria-labelledby="heading-keterangan-peranan">
    <h2 id="heading-keterangan-peranan" class="font-bold text-gray-800 mb-4">Keterangan Peranan</h2>
    <dl class="space-y-3">
        <div class="flex items-start gap-3">
            <dt><span class="text-xs font-semibold px-2 py-1 rounded-full bg-red-100 text-red-700 inline-block">Pentadbir Sistem</span></dt>
            <dd class="text-sm text-gray-600">Akses penuh kepada semua fungsi termasuk pengurusan pengguna, bilik, dan tetapan sistem.</dd>
        </div>
        <div class="flex items-start gap-3">
            <dt><span class="text-xs font-semibold px-2 py-1 rounded-full bg-amber-100 text-amber-700 inline-block">Urus Setia</span></dt>
            <dd class="text-sm text-gray-600">Boleh meluluskan atau menolak permohonan tempahan, dan menguruskan mesyuarat.</dd>
        </div>
        <div class="flex items-start gap-3">
            <dt><span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-700 inline-block">Staf</span></dt>
            <dd class="text-sm text-gray-600">Boleh membuat tempahan bilik mesyuarat sahaja.</dd>
        </div>
    </dl>
</section>

{{-- ─── Modal Tambah ─── --}}
<div id="modal-tambah" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    role="dialog" aria-modal="true" aria-labelledby="modal-tambah-heading">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg mx-4">
        <h3 id="modal-tambah-heading" class="font-bold text-gray-800 text-lg mb-5">Tambah Pengguna Baru</h3>
        <form method="POST" action="{{ route('pengguna.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="tambah-name" class="form-label">Nama Penuh <span class="text-red-500">*</span></label>
                    <input type="text" id="tambah-name" name="name" class="form-input"
                        placeholder="Nama penuh pengguna" required aria-required="true">
                </div>
                <div>
                    <label for="tambah-email" class="form-label">Emel <span class="text-red-500">*</span></label>
                    <input type="email" id="tambah-email" name="email" class="form-input"
                        placeholder="emel@jabatan.gov.my" required aria-required="true" autocomplete="off">
                </div>
                <div>
                    <label for="tambah-jabatan" class="form-label">Unit</label>
                    <input type="text" id="tambah-jabatan" name="jabatan" class="form-input"
                        placeholder="cth: Unit Pentadbiran">
                </div>
                <div>
                    <label for="tambah-peranan" class="form-label">Peranan <span class="text-red-500">*</span></label>
                    <select id="tambah-peranan" name="peranan" class="form-input" required aria-required="true">
                        <option value="staf">Staf</option>
                        <option value="urus_setia">Urus Setia</option>
                        <option value="pentadbir_sistem">Pentadbir Sistem</option>
                    </select>
                </div>
                <div>
                    <label for="tambah-password" class="form-label">Kata Laluan <span class="text-red-500">*</span></label>
                    <input type="password" id="tambah-password" name="password" class="form-input"
                        placeholder="Sekurang-kurangnya 8 aksara" required aria-required="true" autocomplete="new-password">
                </div>
                <div>
                    <label for="tambah-password-sahkan" class="form-label">Sahkan Kata Laluan <span class="text-red-500">*</span></label>
                    <input type="password" id="tambah-password-sahkan" name="password_confirmation"
                        class="form-input" required aria-required="true" autocomplete="new-password">
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn-primary flex-1 justify-center py-2.5">
                    <i class="fa-solid fa-user-plus" aria-hidden="true"></i> Tambah
                </button>
                <button type="button" id="btn-tutup-tambah"
                    class="btn-secondary flex-1 justify-center py-2.5">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- ─── Modal Edit ─── --}}
<div id="modal-edit" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    role="dialog" aria-modal="true" aria-labelledby="modal-edit-heading">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <h3 id="modal-edit-heading" class="font-bold text-gray-800 text-lg mb-5">Edit Pengguna</h3>
        <form id="form-edit" method="POST">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <label for="edit-name" class="form-label">Nama Penuh</label>
                    <input type="text" id="edit-name" name="name" class="form-input">
                    <p id="edit-name-sso-hint" class="hidden mt-1.5 text-xs flex items-center gap-1.5" style="color:#92400e">
                        <i class="fa-brands fa-google" aria-hidden="true"></i>
                        Nama diambil dari akaun MyGovUC — tidak boleh diubah di sini.
                    </p>
                </div>
                <div>
                    <label for="edit-jabatan" class="form-label">Unit</label>
                    <input type="text" id="edit-jabatan" name="jabatan" class="form-input">
                </div>
                <div>
                    <label for="edit-peranan" class="form-label">Peranan</label>
                    <select id="edit-peranan" name="peranan" class="form-input">
                        <option value="staf">Staf</option>
                        <option value="urus_setia">Urus Setia</option>
                        <option value="pentadbir_sistem">Pentadbir Sistem</option>
                    </select>
                </div>
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="edit-aktif" name="aktif" value="1" style="accent-color:#f59e0b">
                        <span class="text-sm font-semibold text-gray-700">Akaun Aktif</span>
                    </label>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn-primary flex-1 justify-center py-2.5">Kemaskini</button>
                <button type="button" id="btn-tutup-edit"
                    class="btn-secondary flex-1 justify-center py-2.5">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- ─── Modal Nyahaktifkan ─── --}}
<div id="modal-nyahaktifkan" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    role="dialog" aria-modal="true" aria-labelledby="modal-nyahaktifkan-heading">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-ban text-red-600 text-lg" aria-hidden="true"></i>
            </div>
            <div>
                <h3 id="modal-nyahaktifkan-heading" class="font-bold text-gray-800 text-lg leading-tight">Nyahaktifkan Akaun</h3>
                <p id="nyahaktifkan-nama" class="text-gray-500 text-sm"></p>
            </div>
        </div>
        <form id="form-nyahaktifkan" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="nyahaktifkan-sebab" class="form-label">Sebab Nyahaktifkan <span class="text-red-500">*</span></label>
                    <input type="text" id="nyahaktifkan-sebab" name="sebab"
                        class="form-input" placeholder="cth: Staf berpindah unit / berhenti kerja"
                        required aria-required="true" maxlength="255">
                </div>
                <p class="text-xs text-gray-400">
                    <i class="fa-solid fa-circle-info mr-1" aria-hidden="true"></i>
                    Akaun boleh diaktifkan semula pada bila-bila masa.
                </p>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit"
                    class="flex-1 justify-center py-2.5 inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition text-sm">
                    <i class="fa-solid fa-ban" aria-hidden="true"></i> Nyahaktifkan
                </button>
                <button type="button" id="btn-tutup-nyahaktifkan"
                    class="btn-secondary flex-1 justify-center py-2.5">Batal</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script nonce="{{ $cspNonce }}">
const LS_VIEW_KEY = 'ibook_pengguna_view';
let tabSemasa  = 'aktif';
let viewSemasa = localStorage.getItem(LS_VIEW_KEY) || 'kad';

// ── Listeners (formerly inline handlers) ──────────────────────────

// Buka modal tambah
const btnBukaTambah = document.getElementById('btn-buka-tambah');
if (btnBukaTambah) {
    btnBukaTambah.addEventListener('click', function() {
        document.getElementById('modal-tambah').classList.remove('hidden');
        document.getElementById('tambah-name').focus();
    });
}

// Tutup modal
const btnTutupTambah = document.getElementById('btn-tutup-tambah');
if (btnTutupTambah) {
    btnTutupTambah.addEventListener('click', function() {
        document.getElementById('modal-tambah').classList.add('hidden');
    });
}
document.getElementById('btn-tutup-edit').addEventListener('click', function() {
    document.getElementById('modal-edit').classList.add('hidden');
});
document.getElementById('btn-tutup-nyahaktifkan').addEventListener('click', function() {
    document.getElementById('modal-nyahaktifkan').classList.add('hidden');
});

// Carian pengguna — tapis segera (visual) + hantar ke server selepas 650ms
let _carianTimer = null;
document.getElementById('carian-pengguna').addEventListener('input', function() {
    applyFilters();                     // maklum balas visual segera

    clearTimeout(_carianTimer);
    _carianTimer = setTimeout(function() {
        document.getElementById('form-carian').submit();
    }, 650);
});

// Unit dropdown — tapis visual segera + submit ke server
document.getElementById('filter-unit').addEventListener('change', function() {
    applyFilters();                     // tapis segera pada halaman semasa
    document.getElementById('form-carian').submit();  // hantar ke server (pagination betul)
});

// Tab buttons
document.querySelectorAll('[data-tab]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        tukarTab(this.dataset.tab);
    });
});

// View toggle buttons
document.querySelectorAll('[data-view]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        tukarView(this.dataset.view);
    });
});

// Pilih semua / nyahpilih
const btnPilihSemua = document.getElementById('btn-pilih-semua');
if (btnPilihSemua) btnPilihSemua.addEventListener('click', function() { pilihSemua(true); });
const btnNyahpilih = document.getElementById('btn-nyahpilih-semua');
if (btnNyahpilih) btnNyahpilih.addEventListener('click', function() { pilihSemua(false); });

// Bulk actions
const btnBulkAktif = document.getElementById('btn-bulk-aktifkan');
if (btnBulkAktif) btnBulkAktif.addEventListener('click', function() { submitBulk('aktifkan'); });
const btnBulkNyah = document.getElementById('btn-bulk-nyahaktifkan');
if (btnBulkNyah) btnBulkNyah.addEventListener('click', function() { submitBulk('nyahaktifkan'); });

// Checkbox pilih semua (list view aktif)
const cbSemuaList = document.getElementById('cb-semua-list');
if (cbSemuaList) cbSemuaList.addEventListener('change', function() { pilihSemuaList(this); });

// Checkbox pilih semua (list view pending)
const cbSemuaListPending = document.getElementById('cb-semua-list-pending');
if (cbSemuaListPending) cbSemuaListPending.addEventListener('change', function() { pilihSemuaList(this); });

// Checkbox pilih semua (list view nyahaktif)
const cbSemuaListNyah = document.getElementById('cb-semua-list-nyahaktif');
if (cbSemuaListNyah) cbSemuaListNyah.addEventListener('change', function() { pilihSemuaList(this); });

// Sort buttons (event delegation)
document.addEventListener('click', function(e) {
    const sortBtn = e.target.closest('.sort-btn');
    if (sortBtn) {
        const col   = sortBtn.dataset.col;
        const panel = sortBtn.dataset.panel;
        if (col && panel) sortList(panel, col, sortBtn);
    }
});

// Event delegation for openEdit / openNyahaktif (from partial views)
document.addEventListener('click', function(e) {
    const editBtn = e.target.closest('[data-open-edit]');
    if (editBtn) {
        const d = editBtn.dataset;
        openEdit(parseInt(d.openEdit, 10), d.name, d.jabatan, d.peranan, d.aktif === 'true', d.hasSso === 'true');
        return;
    }
    const nyahaktifBtn = e.target.closest('[data-open-nyahaktif]');
    if (nyahaktifBtn) {
        openNyahaktifkan(parseInt(nyahaktifBtn.dataset.openNyahaktif, 10), nyahaktifBtn.dataset.name);
        return;
    }
    const toggleAktifBtn = e.target.closest('[data-confirm-toggle]');
    if (toggleAktifBtn) {
        const msg = toggleAktifBtn.dataset.confirmToggle;
        if (!confirm(msg)) e.preventDefault();
        return;
    }
    const cbPengguna = e.target.closest('.checkbox-pengguna');
    if (cbPengguna) {
        kemaskiniToolbar();
    }
});

// ── Tab ──
function tukarTab(tab) {
    tabSemasa = tab;
    ['aktif','pending','nyahaktif'].forEach(t => {
        const isActive = (t === tab);
        document.getElementById('tab-' + t)?.classList.toggle('aktif-tab', isActive);
        document.getElementById('tab-' + t)?.setAttribute('aria-selected', isActive);
        document.getElementById('panel-' + t)?.classList.toggle('hidden', !isActive);
    });
    // reset checkbox semua bila tukar tab
    document.querySelectorAll('.checkbox-pengguna:checked').forEach(cb => cb.checked = false);
    kemaskiniToolbar();
    // apply filter visual semula
    applyFilters();
}

// ── View Toggle ──
function tukarView(view) {
    viewSemasa = view;
    localStorage.setItem(LS_VIEW_KEY, view);   // simpan pilihan
    document.getElementById('btn-kad').classList.toggle('aktif-view', view === 'kad');
    document.getElementById('btn-senarai').classList.toggle('aktif-view', view === 'senarai');
    document.getElementById('btn-kad').setAttribute('aria-pressed', view === 'kad');
    document.getElementById('btn-senarai').setAttribute('aria-pressed', view === 'senarai');

    ['aktif','pending','nyahaktif'].forEach(tab => {
        document.getElementById('kad-' + tab)?.classList.toggle('hidden', view !== 'kad');
        document.getElementById('list-' + tab)?.classList.toggle('hidden', view !== 'senarai');
        const pgKad  = document.getElementById('pagination-kad-'  + tab);
        const pgList = document.getElementById('pagination-list-' + tab);
        if (pgKad)  pgKad.classList.toggle('hidden', view !== 'kad');
        if (pgList) pgList.classList.toggle('hidden', view !== 'senarai');
    });

    // reset checkbox bila tukar view
    document.querySelectorAll('.checkbox-pengguna:checked').forEach(cb => cb.checked = false);
    kemaskiniToolbar();
}

// ── Filter Gabungan (teks + unit) ──
function applyFilters() {
    const kata  = (document.getElementById('carian-pengguna')?.value || '').trim().toLowerCase();
    const unit  = (document.getElementById('filter-unit')?.value || '').toLowerCase();

    // Kad view
    document.querySelectorAll('#kad-aktif article, #kad-pending article, #kad-nyahaktif article').forEach(kad => {
        const nama    = (kad.querySelector('[id^="pengguna-"]')?.textContent || '').toLowerCase();
        const emel    = (kad.querySelector('.text-xs.text-gray-400.truncate')?.textContent || '').toLowerCase();
        const kadUnit = (kad.querySelector('.text-xs.text-gray-500.truncate')?.textContent || '').toLowerCase().trim();
        const matchTeks = !kata || nama.includes(kata) || emel.includes(kata) || kadUnit.includes(kata);
        const matchUnit = !unit || kadUnit === unit;
        kad.style.display = (matchTeks && matchUnit) ? '' : 'none';
    });

    // Baris senarai (list view)
    document.querySelectorAll('.list-data-row').forEach(baris => {
        const nama      = (baris.dataset.name || '').toLowerCase();
        const barisUnit = (baris.dataset.unit || '').toLowerCase();
        const emel      = (baris.querySelector('.text-xs.text-gray-400.truncate')?.textContent || '').toLowerCase();
        const matchTeks = !kata || nama.includes(kata) || barisUnit.includes(kata) || emel.includes(kata);
        const matchUnit = !unit || barisUnit === unit;
        baris.style.display = (matchTeks && matchUnit) ? '' : 'none';
    });
}

// ── Toolbar Pukal ──
function kemaskiniToolbar() {
    const dipilih = document.querySelectorAll('.checkbox-pengguna:checked:not(:disabled)');
    const toolbar  = document.getElementById('toolbar-pukal');
    document.getElementById('kiraan-pilihan').textContent = dipilih.length;
    toolbar.classList.toggle('hidden', dipilih.length === 0);
    toolbar.classList.toggle('flex', dipilih.length > 0);
}

function pilihSemua(pilih) {
    // Pilih dalam panel & view yang aktif sahaja
    const panel = document.getElementById('panel-' + tabSemasa);
    panel.querySelectorAll('.checkbox-pengguna:not(:disabled)').forEach(cb => cb.checked = pilih);
    kemaskiniToolbar();
}

function pilihSemuaList(masterCb) {
    const panel = document.getElementById('panel-' + tabSemasa);
    panel.querySelectorAll('.checkbox-pengguna:not(:disabled)').forEach(cb => cb.checked = masterCb.checked);
    kemaskiniToolbar();
}

function submitBulk(tindakan) {
    const dipilih = document.querySelectorAll('.checkbox-pengguna:checked:not(:disabled)');
    if (dipilih.length === 0) return;
    const label = tindakan === 'aktifkan' ? 'aktifkan' : 'nyahaktifkan';
    if (!confirm(`Adakah anda pasti mahu ${label} ${dipilih.length} pengguna yang dipilih?`)) return;

    document.getElementById('bulk-tindakan').value = tindakan;
    const container = document.getElementById('hidden-ids');
    container.innerHTML = '';
    dipilih.forEach(cb => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = cb.value;
        container.appendChild(inp);
    });
    document.getElementById('form-bulk-aktif').submit();
}

// ── Modal Edit ──
// ── Sort senarai ──
const _sortState = {}; // { 'aktif-name': 'asc', ... }

function sortList(panel, col, btnEl) {
    const container = document.getElementById('list-' + panel);
    const rows = Array.from(container.querySelectorAll('.list-data-row'));
    const key  = panel + '-' + col;

    // Toggle arah
    const dir = _sortState[key] === 'asc' ? 'desc' : 'asc';
    _sortState[key] = dir;

    rows.sort((a, b) => {
        let va = (a.dataset[col] || '').trim();
        let vb = (b.dataset[col] || '').trim();

        if (col === 'tarikh') {
            // Bandingkan sebagai tarikh
            const da = va ? new Date(va) : new Date(0);
            const db = vb ? new Date(vb) : new Date(0);
            return dir === 'asc' ? da - db : db - da;
        }
        // Bandingkan sebagai teks
        return dir === 'asc'
            ? va.localeCompare(vb, 'ms', { sensitivity: 'base' })
            : vb.localeCompare(va, 'ms', { sensitivity: 'base' });
    });

    // Susun semula dalam DOM
    rows.forEach(r => container.appendChild(r));

    // Kemaskini ikon sort
    container.querySelectorAll('.sort-btn').forEach(btn => {
        btn.classList.remove('sorted');
        btn.querySelector('.sort-icon').textContent = '⇅';
    });
    btnEl.classList.add('sorted');
    btnEl.querySelector('.sort-icon').textContent = dir === 'asc' ? '↑' : '↓';
}

function openEdit(id, name, jabatan, peranan, aktif, hasSso) {
    document.getElementById('form-edit').action    = '/pengguna/' + id;
    document.getElementById('edit-jabatan').value  = jabatan || '';
    document.getElementById('edit-peranan').value  = peranan;
    document.getElementById('edit-aktif').checked  = aktif;

    const nameInput = document.getElementById('edit-name');
    const ssoHint   = document.getElementById('edit-name-sso-hint');
    nameInput.value    = name;
    nameInput.readOnly = hasSso;
    nameInput.style.opacity = hasSso ? '0.5' : '1';
    nameInput.style.cursor  = hasSso ? 'not-allowed' : '';
    ssoHint.classList.toggle('hidden', !hasSso);

    document.getElementById('modal-edit').classList.remove('hidden');
    // Fokus ke jabatan jika nama readonly, nama jika tidak
    setTimeout(() => (hasSso ? document.getElementById('edit-jabatan') : nameInput).focus(), 50);
}

// ── Modal Nyahaktifkan ──
function openNyahaktifkan(id, name) {
    document.getElementById('nyahaktifkan-nama').textContent = name;
    document.getElementById('form-nyahaktifkan').action = '/pengguna/' + id + '/toggle-aktif';
    document.getElementById('nyahaktifkan-sebab').value = '';
    document.getElementById('modal-nyahaktifkan').classList.remove('hidden');
    setTimeout(() => document.getElementById('nyahaktifkan-sebab').focus(), 50);
}

// ── Esc tutup modal ──
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        ['modal-tambah','modal-edit','modal-nyahaktifkan'].forEach(id =>
            document.getElementById(id)?.classList.add('hidden'));
    }
});

// ── Mulakan paparan berdasarkan localStorage ──
document.addEventListener('DOMContentLoaded', () => {
    if (viewSemasa !== 'kad') {
        tukarView(viewSemasa);
    }
    // Apply filter unit/teks jika ada nilai semasa muat halaman
    applyFilters();
});
</script>
@endpush
@endsection
