@extends('layouts.app')

@section('title', $bahagian ? 'Edit Bahagian — '.$bahagian->kod : 'Tambah Bahagian')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-3">
        <a href="{{ route('bahagian.index') }}" class="hover:text-amber-600 transition-colors">Bahagian</a>
        <i class="fa-solid fa-chevron-right text-xs text-gray-300" aria-hidden="true"></i>
        <span class="text-gray-800 font-medium">{{ $bahagian ? 'Edit '.$bahagian->kod : 'Tambah Bahagian Baru' }}</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-800">
        {{ $bahagian ? 'Edit Bahagian — '.$bahagian->kod : 'Tambah Bahagian Baru' }}
    </h1>
    <p class="text-gray-500 text-sm mt-1">
        {{ $bahagian ? 'Kemaskini maklumat bahagian.' : 'Daftar bahagian/jabatan baru dalam sistem iBook.' }}
    </p>
</div>

<div class="max-w-xl">
    <form method="POST"
          action="{{ $bahagian ? route('bahagian.update', $bahagian) : route('bahagian.store') }}"
          novalidate
          aria-label="Borang bahagian">
        @csrf
        @if($bahagian) @method('PUT') @endif

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 pt-5 pb-4 border-b border-gray-100">
                <h2 class="font-bold text-gray-800">Maklumat Bahagian</h2>
            </div>

            <div class="p-6 space-y-5">

                {{-- Kod --}}
                <div>
                    <label for="kod" class="form-label">
                        Kod Bahagian <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input type="text" id="kod" name="kod"
                        value="{{ old('kod', $bahagian?->kod) }}"
                        class="form-input font-mono uppercase"
                        placeholder="cth: JPA"
                        maxlength="20"
                        required aria-required="true"
                        oninput="this.value = this.value.toUpperCase()"
                        @error('kod') aria-invalid="true" aria-describedby="ralat-kod" @enderror>
                    <p class="form-hint">Singkatan unik huruf besar. Contoh: JPA, MAMPU, MOF. Tidak boleh diubah selepas digunakan.</p>
                    @error('kod')
                    <p id="ralat-kod" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nama --}}
                <div>
                    <label for="nama" class="form-label">
                        Nama Penuh <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input type="text" id="nama" name="nama"
                        value="{{ old('nama', $bahagian?->nama) }}"
                        class="form-input"
                        placeholder="cth: Jabatan Perkhidmatan Awam"
                        maxlength="150"
                        required aria-required="true"
                        @error('nama') aria-invalid="true" aria-describedby="ralat-nama" @enderror>
                    @error('nama')
                    <p id="ralat-nama" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Lokasi --}}
                <div>
                    <label for="lokasi" class="form-label">Lokasi</label>
                    <textarea id="lokasi" name="lokasi"
                        class="form-input resize-none"
                        rows="2"
                        placeholder="cth: Aras 3, Blok C, Kompleks Jabatan Perdana Menteri, Putrajaya"
                        maxlength="500">{{ old('lokasi', $bahagian?->lokasi) }}</textarea>
                    <p class="form-hint">Aras, blok, bangunan — membantu staf mencari bilik bahagian ini.</p>
                </div>

                {{-- Telefon --}}
                <div>
                    <label for="telefon" class="form-label">Nombor Telefon</label>
                    <input type="tel" id="telefon" name="telefon"
                        value="{{ old('telefon', $bahagian?->telefon) }}"
                        class="form-input"
                        placeholder="cth: 03-8000 8000"
                        maxlength="20">
                </div>

                {{-- Emel --}}
                <div>
                    <label for="emel" class="form-label">Emel Rasmi</label>
                    <input type="email" id="emel" name="emel"
                        value="{{ old('emel', $bahagian?->emel) }}"
                        class="form-input"
                        placeholder="cth: bilik@jpa.gov.my"
                        maxlength="100"
                        @error('emel') aria-invalid="true" aria-describedby="ralat-emel" @enderror>
                    <p class="form-hint">Akan di-CC apabila ada tempahan cross-booking masuk dari bahagian lain.</p>
                    @error('emel')
                    <p id="ralat-emel" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>

        <div class="mt-5 flex items-center gap-3">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                {{ $bahagian ? 'Simpan Perubahan' : 'Daftar Bahagian' }}
            </button>
            <a href="{{ route('bahagian.index') }}"
               class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 rounded hover:bg-gray-100 transition-colors">
                Batal
            </a>
        </div>

    </form>
</div>
@endsection
