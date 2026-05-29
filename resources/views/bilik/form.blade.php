@extends('layouts.app')

@section('title', $bilik ? 'Edit Bilik' : 'Tambah Bilik')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('bilik.index') }}" class="text-gray-400 hover:text-gray-600" aria-label="Kembali ke senarai bilik">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            {{ $bilik ? 'Edit Bilik Mesyuarat' : 'Tambah Bilik Mesyuarat' }}
        </h1>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-8 max-w-2xl">
    @if($errors->any())
    <div role="alert" aria-live="assertive" class="alert-error mb-5" id="ralat-borang-bilik">
        <p class="font-semibold mb-1">Sila betulkan ralat berikut:</p>
        <ul class="list-disc list-inside text-sm" aria-label="Senarai ralat borang bilik">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
        action="{{ $bilik ? route('bilik.update', $bilik) : route('bilik.store') }}"
        enctype="multipart/form-data"
        novalidate
        aria-label="{{ $bilik ? 'Borang edit bilik mesyuarat' : 'Borang tambah bilik mesyuarat baru' }}">
        @csrf
        @if($bilik) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">

            {{-- Nama Bilik --}}
            <div class="md:col-span-2">
                <label for="nama-bilik" class="form-label">
                    Nama Bilik <span class="text-red-500" aria-hidden="true">*</span>
                    <span class="sr-only">(wajib)</span>
                </label>
                <input type="text" id="nama-bilik" name="nama"
                    value="{{ old('nama', $bilik?->nama) }}"
                    class="form-input"
                    placeholder="cth: Bilik Mesyuarat Utama"
                    required aria-required="true"
                    @error('nama') aria-invalid="true" aria-describedby="ralat-nama" @enderror>
                @error('nama')
                <p id="ralat-nama" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>

            {{-- Bahagian --}}
            <div class="md:col-span-2">
                <label for="bahagian-bilik" class="form-label">
                    Bahagian <span class="text-red-500" aria-hidden="true">*</span>
                </label>
                <select id="bahagian-bilik" name="bahagian_id" class="form-input" required aria-required="true"
                    @error('bahagian_id') aria-invalid="true" aria-describedby="ralat-bahagian" @enderror>
                    <option value="">— Pilih Bahagian —</option>
                    @foreach($bahagian as $b)
                    <option value="{{ $b->id }}"
                        {{ old('bahagian_id', $bilik?->bahagian_id) == $b->id ? 'selected' : '' }}>
                        {{ $b->kod }} — {{ $b->nama }}
                    </option>
                    @endforeach
                </select>
                <p class="form-hint">Bahagian/jabatan pemilik bilik mesyuarat ini.</p>
                @error('bahagian_id')
                <p id="ralat-bahagian" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kapasiti --}}
            <div>
                <label for="kapasiti-bilik" class="form-label">
                    Kapasiti (orang) <span class="text-red-500" aria-hidden="true">*</span>
                    <span class="sr-only">(wajib, sekurang-kurangnya 1)</span>
                </label>
                <input type="number" id="kapasiti-bilik" name="kapasiti"
                    value="{{ old('kapasiti', $bilik?->kapasiti) }}"
                    min="1"
                    class="form-input"
                    placeholder="cth: 40"
                    required aria-required="true"
                    @error('kapasiti') aria-invalid="true" aria-describedby="ralat-kapasiti" @enderror>
                @error('kapasiti')
                <p id="ralat-kapasiti" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>

            {{-- Lokasi --}}
            <div>
                <label for="lokasi-bilik" class="form-label">Lokasi</label>
                <input type="text" id="lokasi-bilik" name="lokasi"
                    value="{{ old('lokasi', $bilik?->lokasi) }}"
                    class="form-input"
                    placeholder="cth: Tingkat 3, Blok A">
            </div>
        </div>

        {{-- Kemudahan --}}
        <fieldset class="mb-5">
            <legend class="form-label mb-3">Kemudahan</legend>
            @php
            $kemudahanList = ['TV', 'Papan Putih', 'Sistem Audio', 'Video Conferencing', 'Skrin LCD'];
            $selected = old('kemudahan', $bilik?->kemudahan ?? []);
            @endphp
            <div class="grid grid-cols-2 gap-3">
                @foreach($kemudahanList as $k)
                <label class="flex items-center gap-2 text-sm cursor-pointer" for="kemudahan-{{ Str::slug($k) }}">
                    <input type="checkbox"
                        id="kemudahan-{{ Str::slug($k) }}"
                        name="kemudahan[]"
                        value="{{ $k }}"
                        class="rounded flex-shrink-0"
                        style="accent-color:#f59e0b"
                        {{ in_array($k, $selected) ? 'checked' : '' }}>
                    {{ $k }}
                </label>
                @endforeach
            </div>
        </fieldset>

        {{-- Status --}}
        <div class="mb-5">
            <label for="status-bilik" class="form-label">
                Status <span class="text-red-500" aria-hidden="true">*</span>
                <span class="sr-only">(wajib)</span>
            </label>
            <select id="status-bilik" name="status" class="form-input" required aria-required="true">
                <option value="aktif" {{ old('status', $bilik?->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="tidak_aktif" {{ old('status', $bilik?->status) === 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
            </select>
        </div>

        {{-- Gambar Bilik (Upload) --}}
        <div class="mb-7">
            <label for="gambar-bilik" class="form-label">
                Gambar Bilik
                <span class="text-gray-400 font-normal text-xs ml-1">(pilihan)</span>
            </label>

            {{-- Drop zone --}}
            <div id="gambar-dropzone"
                 class="relative border-2 border-dashed border-gray-300 rounded-xl p-6 text-center cursor-pointer transition-colors hover:border-amber-400 hover:bg-amber-50"
                 role="button" tabindex="0" aria-label="Kawasan muat naik gambar bilik">
                <input type="file" id="gambar-bilik" name="gambar"
                    accept="image/jpeg,image/png,image/webp"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                    @error('gambar') aria-invalid="true" aria-describedby="ralat-gambar" @enderror>
                <div id="gambar-placeholder">
                    <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-300 mb-2" aria-hidden="true"></i>
                    <p class="text-sm text-gray-500">Klik atau seret gambar ke sini</p>
                    <p class="text-xs text-gray-400 mt-1">JPG, PNG atau WebP • Maks 5MB</p>
                    <p class="text-xs text-gray-400">Gambar akan diubah saiz automatik kepada 800×352px</p>
                </div>
            </div>

            <p class="form-hint mt-1">Kosongkan untuk kekalkan gambar sedia ada atau guna gambar automatik.</p>

            @error('gambar')
            <p id="ralat-gambar" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
            @enderror

            {{-- Preview gambar (semasa edit atau selepas pilih fail) --}}
            @php $gambarSediada = $bilik?->gambar; @endphp
            <div id="gambar-preview-wrap" class="mt-3 {{ $gambarSediada ? '' : 'hidden' }}">
                <p class="text-xs text-gray-400 mb-1.5 font-semibold uppercase tracking-wider">
                    <span id="gambar-preview-label">{{ $gambarSediada ? 'Gambar Semasa' : 'Pratonton' }}</span>
                </p>
                <div class="relative w-full rounded-xl overflow-hidden bg-gray-100 border border-gray-200" style="height:176px">
                    <img id="gambar-preview-img"
                         src="{{ $gambarSediada ?? '' }}"
                         alt="Pratonton gambar bilik"
                         class="w-full h-full object-cover">
                    {{-- Butang buang pratonton --}}
                    <button type="button" id="gambar-preview-buang"
                        class="absolute top-2 right-2 bg-white/80 hover:bg-white text-gray-600 hover:text-red-600 rounded-full w-7 h-7 flex items-center justify-center shadow transition-colors"
                        aria-label="Buang gambar yang dipilih">
                        <i class="fa-solid fa-xmark text-sm" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                {{ $bilik ? 'Kemaskini' : 'Simpan' }}
            </button>
            <a href="{{ route('bilik.index') }}" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>
@push('scripts')
<script nonce="{{ $cspNonce }}">
(function () {
    const input     = document.getElementById('gambar-bilik');
    const dropzone  = document.getElementById('gambar-dropzone');
    const wrap      = document.getElementById('gambar-preview-wrap');
    const img       = document.getElementById('gambar-preview-img');
    const label     = document.getElementById('gambar-preview-label');
    const buangBtn  = document.getElementById('gambar-preview-buang');
    const placeholder = document.getElementById('gambar-placeholder');

    // Tunjuk pratonton apabila fail dipilih
    if (input) {
        input.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            // Sahkan jenis & saiz pada klien (server tetap semak semula)
            if (!file.type.match(/image\/(jpeg|png|webp)/)) {
                alert('Sila pilih gambar dalam format JPG, PNG atau WebP.');
                this.value = '';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                alert('Saiz gambar melebihi 5MB. Sila pilih gambar yang lebih kecil.');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                img.src = e.target.result;
                wrap.classList.remove('hidden');
                if (label) label.textContent = 'Pratonton (Akan Dimuat Naik)';
                // Ubah warna border dropzone jadi hijau
                dropzone.classList.remove('border-gray-300', 'hover:border-amber-400');
                dropzone.classList.add('border-green-400', 'bg-green-50');
                if (placeholder) {
                    placeholder.innerHTML = '<i class="fa-solid fa-circle-check text-2xl text-green-500 mb-1" aria-hidden="true"></i><p class="text-sm text-green-700 font-medium">' + file.name + '</p>';
                }
            };
            reader.readAsDataURL(file);
        });
    }

    // Butang buang — kosongkan input dan sembunyikan pratonton
    if (buangBtn) {
        buangBtn.addEventListener('click', function () {
            if (input) {
                input.value = '';
            }
            wrap.classList.add('hidden');
            img.src = '';
            // Reset dropzone
            dropzone.classList.remove('border-green-400', 'bg-green-50');
            dropzone.classList.add('border-gray-300');
            if (placeholder) {
                placeholder.innerHTML = '<i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-300 mb-2" aria-hidden="true"></i><p class="text-sm text-gray-500">Klik atau seret gambar ke sini</p><p class="text-xs text-gray-400 mt-1">JPG, PNG atau WebP • Maks 5MB</p><p class="text-xs text-gray-400">Gambar akan diubah saiz automatik kepada 800×352px</p>';
            }
        });
    }

    // Drag-over visual
    if (dropzone) {
        dropzone.addEventListener('dragover', function (e) {
            e.preventDefault();
            this.classList.add('border-amber-400', 'bg-amber-50');
        });
        dropzone.addEventListener('dragleave', function () {
            this.classList.remove('border-amber-400', 'bg-amber-50');
        });
        dropzone.addEventListener('drop', function (e) {
            e.preventDefault();
            this.classList.remove('border-amber-400', 'bg-amber-50');
            if (e.dataTransfer.files.length && input) {
                // Assign ke file input
                const dt = new DataTransfer();
                dt.items.add(e.dataTransfer.files[0]);
                input.files = dt.files;
                input.dispatchEvent(new Event('change'));
            }
        });
    }
})();
</script>
@endpush
@endsection
