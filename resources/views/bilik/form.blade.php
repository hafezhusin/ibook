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

        {{-- Gambar Bilik (URL) --}}
        <div class="mb-7">
            <label for="gambar-bilik" class="form-label">
                URL Gambar Bilik
                <span class="text-gray-400 font-normal text-xs ml-1">(pilihan)</span>
            </label>
            <input type="url" id="gambar-bilik" name="gambar"
                value="{{ old('gambar', $bilik?->gambar) }}"
                class="form-input"
                placeholder="https://example.com/gambar-bilik.jpg"
                @error('gambar') aria-invalid="true" aria-describedby="ralat-gambar" @enderror>
            <p class="form-hint">
                Masukkan URL gambar bilik (JPG/PNG). Kosongkan untuk guna gambar automatik mengikut jenis bilik.
            </p>
            @error('gambar')
            <p id="ralat-gambar" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
            @enderror

            {{-- Preview gambar --}}
            <div id="gambar-preview-wrap" class="mt-3 {{ old('gambar', $bilik?->gambar) ? '' : 'hidden' }}">
                <p class="text-xs text-gray-400 mb-1.5 font-semibold uppercase tracking-wider">Pratonton</p>
                <div class="relative w-full h-40 rounded-xl overflow-hidden bg-gray-100 border border-gray-200">
                    <img id="gambar-preview-img"
                         src="{{ old('gambar', $bilik?->gambar ?? '') }}"
                         alt="Pratonton gambar bilik"
                         class="w-full h-full object-cover"
                         onerror="document.getElementById('gambar-preview-wrap').classList.add('hidden')">
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
document.getElementById('gambar-bilik').addEventListener('input', function() {
    praLihatGambar(this.value);
});

function praLihatGambar(url) {
    const wrap = document.getElementById('gambar-preview-wrap');
    const img  = document.getElementById('gambar-preview-img');
    if (!url || url.trim() === '') {
        wrap.classList.add('hidden');
        return;
    }
    img.src = url.trim();
    img.onload  = () => wrap.classList.remove('hidden');
    img.onerror = () => wrap.classList.add('hidden');
}
</script>
@endpush
@endsection
