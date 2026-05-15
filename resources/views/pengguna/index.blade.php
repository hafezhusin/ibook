@extends('layouts.app')

@section('title', 'Pengguna')

@section('content')
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

{{-- Kad Pengguna --}}
<section aria-labelledby="heading-senarai-pengguna">
    <h2 id="heading-senarai-pengguna" class="sr-only">Senarai Pengguna</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
        @foreach($pengguna as $p)
        <article class="bg-white rounded-xl shadow-sm p-6" aria-labelledby="pengguna-{{ $p->id }}">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg flex-shrink-0"
                    style="background:#f59e0b"
                    aria-hidden="true">
                    {{ strtoupper(substr($p->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div id="pengguna-{{ $p->id }}" class="font-bold text-gray-800 truncate">{{ $p->name }}</div>
                    <div class="text-sm text-gray-500">{{ $p->jabatan ?? 'Tiada jabatan' }}</div>
                    <div class="text-xs text-gray-400 truncate">{{ $p->email }}</div>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between">
                <span class="text-xs font-semibold px-2 py-1 rounded-full
                    {{ $p->peranan === 'pentadbir_sistem' ? 'bg-red-100 text-red-700' :
                       ($p->peranan === 'urus_setia' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700') }}">
                    {{ $p->label_peranan }}
                </span>
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
                        <i class="fa-solid fa-key" aria-hidden="true"></i> Tukar
                    </button>
                </div>
            </div>
            @if(!$p->aktif)
            <div class="mt-2 text-xs text-red-500 flex items-center gap-1" role="status">
                <i class="fa-solid fa-ban" aria-hidden="true"></i> Akaun dinyahaktifkan
            </div>
            @endif
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
            <dt><span class="text-xs font-semibold px-2 py-1 rounded-full bg-green-100 text-green-700 flex-shrink-0 inline-block">Staf</span></dt>
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
                        <span class="sr-only">(wajib)</span>
                    </label>
                    <input type="text" id="tambah-name" name="name"
                        class="form-input"
                        placeholder="Nama penuh pengguna"
                        required aria-required="true">
                </div>
                <div>
                    <label for="tambah-email" class="form-label">
                        Emel <span class="text-red-500" aria-hidden="true">*</span>
                        <span class="sr-only">(wajib)</span>
                    </label>
                    <input type="email" id="tambah-email" name="email"
                        class="form-input"
                        placeholder="emel@jabatan.gov.my"
                        required aria-required="true" autocomplete="off">
                </div>
                <div>
                    <label for="tambah-jabatan" class="form-label">Jabatan</label>
                    <input type="text" id="tambah-jabatan" name="jabatan"
                        class="form-input"
                        placeholder="cth: Bahagian ICT">
                </div>
                <div>
                    <label for="tambah-peranan" class="form-label">
                        Peranan <span class="text-red-500" aria-hidden="true">*</span>
                        <span class="sr-only">(wajib)</span>
                    </label>
                    <select id="tambah-peranan" name="peranan"
                        class="form-input"
                        required aria-required="true">
                        <option value="staf">Staf</option>
                        <option value="urus_setia">Urus Setia</option>
                        <option value="pentadbir_sistem">Pentadbir Sistem</option>
                    </select>
                </div>
                <div>
                    <label for="tambah-password" class="form-label">
                        Kata Laluan <span class="text-red-500" aria-hidden="true">*</span>
                        <span class="sr-only">(wajib, sekurang-kurangnya 8 aksara)</span>
                    </label>
                    <input type="password" id="tambah-password" name="password"
                        class="form-input"
                        placeholder="Sekurang-kurangnya 8 aksara"
                        required aria-required="true" autocomplete="new-password">
                </div>
                <div>
                    <label for="tambah-password-sahkan" class="form-label">
                        Sahkan Kata Laluan <span class="text-red-500" aria-hidden="true">*</span>
                        <span class="sr-only">(wajib)</span>
                    </label>
                    <input type="password" id="tambah-password-sahkan" name="password_confirmation"
                        class="form-input"
                        required aria-required="true" autocomplete="new-password">
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
                        <input type="checkbox" id="edit-aktif" name="aktif" value="1" style="accent-color:#f59e0b">
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
function openEdit(id, name, jabatan, peranan, aktif) {
    const modal = document.getElementById('modal-edit');
    document.getElementById('form-edit').action = '/pengguna/' + id;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-jabatan').value = jabatan || '';
    document.getElementById('edit-peranan').value = peranan;
    document.getElementById('edit-aktif').checked = aktif;
    modal.classList.remove('hidden');
    setTimeout(() => document.getElementById('edit-name').focus(), 50);
}
function openReset(id, name) {
    const modal = document.getElementById('modal-reset');
    document.getElementById('reset-nama').textContent = name;
    document.getElementById('form-reset').action = '/pengguna/' + id + '/reset-password';
    modal.classList.remove('hidden');
    setTimeout(() => document.getElementById('reset-password').focus(), 50);
}
// Esc key closes any open modal
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
