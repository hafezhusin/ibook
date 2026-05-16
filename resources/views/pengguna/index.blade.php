@extends('layouts.app')

@section('title', 'Pengguna')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Pengguna</h1>
        <p class="text-gray-500 text-sm mt-1">Pengurusan pengguna dan peranan</p>
    </div>
    <button type="button"
        onclick="document.getElementById('modal-tambah').classList.remove('hidden'); document.getElementById('tambah-name').focus();"
        class="btn-primary"
        aria-haspopup="dialog"
        aria-controls="modal-tambah">
        <i class="fa-solid fa-plus" aria-hidden="true"></i> Tambah Pengguna
    </button>
</div>

{{-- ── Toolbar Tindakan Pukal (tersembunyi — muncul bila checkbox dipilih) ── --}}
<div id="toolbar-pukal"
    class="hidden mb-4 p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-between gap-4"
    role="region"
    aria-live="polite"
    aria-label="Tindakan pukal pengguna">
    <div class="flex items-center gap-3">
        <span class="text-sm font-semibold text-amber-800">
            <span id="kiraan-pilihan">0</span> pengguna dipilih
        </span>
        <button type="button"
            onclick="pilihSemua(true)"
            class="text-xs text-amber-700 underline hover:no-underline">
            Pilih Semua
        </button>
        <button type="button"
            onclick="pilihSemua(false)"
            class="text-xs text-gray-500 underline hover:no-underline">
            Nyahpilih Semua
        </button>
    </div>
    <div class="flex items-center gap-2">
        <form id="form-bulk-aktif" method="POST" action="{{ route('pengguna.bulk-aktif') }}">
            @csrf
            <div id="hidden-ids"></div>
            <input type="hidden" name="tindakan" id="bulk-tindakan" value="">
            <button type="button"
                onclick="submitBulk('aktifkan')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-green-100 text-green-700 hover:bg-green-200 transition">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i> Aktifkan
            </button>
            <button type="button"
                onclick="submitBulk('nyahaktifkan')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-100 text-red-700 hover:bg-red-200 transition ml-1">
                <i class="fa-solid fa-ban" aria-hidden="true"></i> Nyahaktifkan
            </button>
        </form>
    </div>
</div>

{{-- Flash messages --}}
@if(session('success'))
<div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm flex items-center gap-2" role="alert">
    <i class="fa-solid fa-circle-check text-green-500" aria-hidden="true"></i>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm flex items-center gap-2" role="alert">
    <i class="fa-solid fa-circle-xmark text-red-500" aria-hidden="true"></i>
    {{ session('error') }}
</div>
@endif

{{-- Kad Pengguna --}}
<section aria-labelledby="heading-senarai-pengguna">
    <h2 id="heading-senarai-pengguna" class="sr-only">Senarai Pengguna</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
        @foreach($pengguna as $p)
        <article class="bg-white rounded-xl shadow-sm p-5 border-2 transition
            {{ !$p->aktif ? 'border-red-200 opacity-70' : 'border-transparent' }}"
            aria-labelledby="pengguna-{{ $p->id }}"
            data-id="{{ $p->id }}">

            {{-- Checkbox pukal + avatar --}}
            <div class="flex items-start gap-3">
                {{-- Checkbox --}}
                <div class="pt-0.5 flex-shrink-0">
                    <input type="checkbox"
                        class="checkbox-pengguna w-4 h-4 rounded cursor-pointer"
                        style="accent-color:#f59e0b"
                        value="{{ $p->id }}"
                        {{ $p->id === auth()->id() ? 'disabled title=Akaun anda sendiri' : '' }}
                        onchange="kemaskiniToolbar()"
                        aria-label="Pilih {{ $p->name }}">
                </div>

                {{-- Avatar --}}
                <div class="w-11 h-11 rounded-full flex items-center justify-center text-white font-bold text-base flex-shrink-0"
                    style="background: {{ !$p->aktif ? '#9ca3af' : '#f59e0b' }}"
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
                    <div class="text-xs text-gray-500 truncate">{{ $p->jabatan ?? 'Tiada jabatan' }}</div>
                    <div class="text-xs text-gray-400 truncate">{{ $p->email }}</div>
                </div>
            </div>

            {{-- Badge peranan + status --}}
            <div class="mt-3 flex items-center gap-2 flex-wrap">
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                    {{ $p->peranan === 'pentadbir_sistem' ? 'bg-red-100 text-red-700' :
                       ($p->peranan === 'urus_setia' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                    {{ $p->label_peranan }}
                </span>
                @if(!$p->aktif)
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-600 flex items-center gap-1">
                    <i class="fa-solid fa-ban text-xs" aria-hidden="true"></i> Dinyahaktifkan
                </span>
                @else
                <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 flex items-center gap-1">
                    <i class="fa-solid fa-circle text-xs" aria-hidden="true"></i> Aktif
                </span>
                @endif
            </div>

            {{-- Butang tindakan --}}
            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between gap-2">

                {{-- Kiri: Edit + Tukar Kata Laluan --}}
                <div class="flex gap-2">
                    <button type="button"
                        onclick="openEdit({{ $p->id }}, '{{ addslashes($p->name) }}', '{{ addslashes($p->jabatan ?? '') }}', '{{ $p->peranan }}', {{ $p->aktif ? 'true' : 'false' }})"
                        class="text-amber-500 text-xs hover:underline"
                        aria-label="Edit pengguna — {{ $p->name }}"
                        aria-haspopup="dialog"
                        aria-controls="modal-edit">
                        <i class="fa-solid fa-pen" aria-hidden="true"></i> Edit
                    </button>
                    <button type="button"
                        onclick="openReset({{ $p->id }}, '{{ addslashes($p->name) }}')"
                        class="text-gray-400 text-xs hover:text-gray-600"
                        aria-label="Tukar kata laluan — {{ $p->name }}"
                        aria-haspopup="dialog"
                        aria-controls="modal-reset">
                        <i class="fa-solid fa-key" aria-hidden="true"></i> Kata Laluan
                    </button>
                </div>

                {{-- Kanan: Toggle Aktif/Nyahaktif --}}
                @if($p->id !== auth()->id())
                <form method="POST" action="{{ route('pengguna.toggle-aktif', $p) }}">
                    @csrf
                    @if($p->aktif)
                    <button type="submit"
                        class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition"
                        aria-label="Nyahaktifkan akaun {{ $p->name }}"
                        onclick="return confirm('Nyahaktifkan akaun {{ addslashes($p->name) }}? Pengguna tidak akan dapat log masuk.')">
                        <i class="fa-solid fa-ban" aria-hidden="true"></i> Nyahaktifkan
                    </button>
                    @else
                    <button type="submit"
                        class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition"
                        aria-label="Aktifkan semula akaun {{ $p->name }}"
                        onclick="return confirm('Aktifkan semula akaun {{ addslashes($p->name) }}?')">
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i> Aktifkan
                    </button>
                    @endif
                </form>
                @endif
            </div>

        </article>
        @endforeach
    </div>
</section>

{{-- Keterangan Peranan --}}
<section class="bg-white rounded-xl shadow-sm p-6" aria-labelledby="heading-keterangan-peranan">
    <h2 id="heading-keterangan-peranan" class="font-bold text-gray-800 mb-4">Keterangan Peranan</h2>
    <dl class="space-y-3">
        <div class="flex items-start gap-3">
            <dt><span class="text-xs font-semibold px-2 py-1 rounded-full bg-red-100 text-red-700 flex-shrink-0 inline-block">Pentadbir Sistem</span></dt>
            <dd class="text-sm text-gray-600">Akses penuh kepada semua fungsi termasuk pengurusan pengguna, bilik, dan tetapan sistem.</dd>
        </div>
        <div class="flex items-start gap-3">
            <dt><span class="text-xs font-semibold px-2 py-1 rounded-full bg-amber-100 text-amber-700 flex-shrink-0 inline-block">Urus Setia</span></dt>
            <dd class="text-sm text-gray-600">Boleh meluluskan atau menolak permohonan tempahan, dan menguruskan mesyuarat.</dd>
        </div>
        <div class="flex items-start gap-3">
            <dt><span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-700 flex-shrink-0 inline-block">Staf</span></dt>
            <dd class="text-sm text-gray-600">Boleh membuat tempahan bilik mesyuarat sahaja.</dd>
        </div>
    </dl>
</section>

{{-- ─── Modal Tambah Pengguna ─── --}}
<div id="modal-tambah"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-tambah-heading">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg mx-4">
        <h3 id="modal-tambah-heading" class="font-bold text-gray-800 text-lg mb-5">Tambah Pengguna Baru</h3>
        <form method="POST" action="{{ route('pengguna.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="tambah-name" class="form-label">
                        Nama Penuh <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input type="text" id="tambah-name" name="name"
                        class="form-input" placeholder="Nama penuh pengguna"
                        required aria-required="true">
                </div>
                <div>
                    <label for="tambah-email" class="form-label">
                        Emel <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input type="email" id="tambah-email" name="email"
                        class="form-input" placeholder="emel@jabatan.gov.my"
                        required aria-required="true" autocomplete="off">
                </div>
                <div>
                    <label for="tambah-jabatan" class="form-label">Jabatan</label>
                    <input type="text" id="tambah-jabatan" name="jabatan"
                        class="form-input" placeholder="cth: Bahagian ICT">
                </div>
                <div>
                    <label for="tambah-peranan" class="form-label">
                        Peranan <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <select id="tambah-peranan" name="peranan"
                        class="form-input" required aria-required="true">
                        <option value="staf">Staf</option>
                        <option value="urus_setia">Urus Setia</option>
                        <option value="pentadbir_sistem">Pentadbir Sistem</option>
                    </select>
                </div>
                <div>
                    <label for="tambah-password" class="form-label">
                        Kata Laluan <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input type="password" id="tambah-password" name="password"
                        class="form-input" placeholder="Sekurang-kurangnya 8 aksara"
                        required aria-required="true" autocomplete="new-password">
                </div>
                <div>
                    <label for="tambah-password-sahkan" class="form-label">
                        Sahkan Kata Laluan <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input type="password" id="tambah-password-sahkan" name="password_confirmation"
                        class="form-input" required aria-required="true" autocomplete="new-password">
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn-primary flex-1 justify-center py-2.5">
                    <i class="fa-solid fa-user-plus" aria-hidden="true"></i> Tambah
                </button>
                <button type="button"
                    onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                    class="btn-secondary flex-1 justify-center py-2.5">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- ─── Modal Edit Pengguna ─── --}}
<div id="modal-edit"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-edit-heading">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <h3 id="modal-edit-heading" class="font-bold text-gray-800 text-lg mb-5">Edit Pengguna</h3>
        <form id="form-edit" method="POST">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <label for="edit-name" class="form-label">Nama Penuh</label>
                    <input type="text" id="edit-name" name="name" class="form-input">
                </div>
                <div>
                    <label for="edit-jabatan" class="form-label">Jabatan</label>
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
                        <input type="checkbox" id="edit-aktif" name="aktif" value="1"
                            style="accent-color:#f59e0b">
                        <span class="text-sm font-semibold text-gray-700">Akaun Aktif</span>
                    </label>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn-primary flex-1 justify-center py-2.5">Kemaskini</button>
                <button type="button"
                    onclick="document.getElementById('modal-edit').classList.add('hidden')"
                    class="btn-secondary flex-1 justify-center py-2.5">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- ─── Modal Tukar Kata Laluan ─── --}}
<div id="modal-reset"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-reset-heading">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <h3 id="modal-reset-heading" class="font-bold text-gray-800 text-lg mb-1">Tukar Kata Laluan</h3>
        <p id="reset-nama" class="text-gray-500 text-sm mb-5"></p>
        <form id="form-reset" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="reset-password" class="form-label">Kata Laluan Baru</label>
                    <input type="password" id="reset-password" name="password"
                        class="form-input" autocomplete="new-password">
                </div>
                <div>
                    <label for="reset-password-sahkan" class="form-label">Sahkan Kata Laluan</label>
                    <input type="password" id="reset-password-sahkan" name="password_confirmation"
                        class="form-input" autocomplete="new-password">
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn-primary flex-1 justify-center py-2.5">Tukar</button>
                <button type="button"
                    onclick="document.getElementById('modal-reset').classList.add('hidden')"
                    class="btn-secondary flex-1 justify-center py-2.5">Batal</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// ── Modal Edit ──
function openEdit(id, name, jabatan, peranan, aktif) {
    document.getElementById('form-edit').action = '/pengguna/' + id;
    document.getElementById('edit-name').value    = name;
    document.getElementById('edit-jabatan').value = jabatan || '';
    document.getElementById('edit-peranan').value = peranan;
    document.getElementById('edit-aktif').checked = aktif;
    document.getElementById('modal-edit').classList.remove('hidden');
    setTimeout(() => document.getElementById('edit-name').focus(), 50);
}

// ── Modal Tukar Kata Laluan ──
function openReset(id, name) {
    document.getElementById('reset-nama').textContent = name;
    document.getElementById('form-reset').action = '/pengguna/' + id + '/reset-password';
    document.getElementById('modal-reset').classList.remove('hidden');
    setTimeout(() => document.getElementById('reset-password').focus(), 50);
}

// ── Toolbar Pukal ──
function kemaskiniToolbar() {
    const dipilih = document.querySelectorAll('.checkbox-pengguna:checked:not(:disabled)');
    const toolbar  = document.getElementById('toolbar-pukal');
    const kiraan   = document.getElementById('kiraan-pilihan');

    kiraan.textContent = dipilih.length;

    if (dipilih.length > 0) {
        toolbar.classList.remove('hidden');
    } else {
        toolbar.classList.add('hidden');
    }
}

function pilihSemua(pilih) {
    document.querySelectorAll('.checkbox-pengguna:not(:disabled)').forEach(cb => {
        cb.checked = pilih;
    });
    kemaskiniToolbar();
}

function submitBulk(tindakan) {
    const dipilih = document.querySelectorAll('.checkbox-pengguna:checked:not(:disabled)');
    if (dipilih.length === 0) return;

    const labelTindakan = tindakan === 'aktifkan' ? 'aktifkan' : 'nyahaktifkan';
    if (!confirm(`Adakah anda pasti mahu ${labelTindakan} ${dipilih.length} pengguna yang dipilih?`)) return;

    // Tetapkan tindakan
    document.getElementById('bulk-tindakan').value = tindakan;

    // Bina hidden inputs untuk ID
    const container = document.getElementById('hidden-ids');
    container.innerHTML = '';
    dipilih.forEach(cb => {
        const input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'ids[]';
        input.value = cb.value;
        container.appendChild(input);
    });

    document.getElementById('form-bulk-aktif').submit();
}

// ── Tutup modal dengan Esc ──
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        ['modal-tambah','modal-edit','modal-reset'].forEach(id => {
            document.getElementById(id).classList.add('hidden');
        });
    }
});
</script>
@endpush
@endsection
