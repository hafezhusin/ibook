@extends('layouts.app')

@section('title', 'Tetapan')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Tetapan</h1>
    <p class="text-gray-500 text-sm mt-1">Konfigurasi sistem</p>
</div>

<div class="max-w-2xl">
    <form method="POST" action="{{ route('tetapan.update') }}" novalidate aria-label="Borang tetapan sistem">
        @csrf

        {{-- Maklumat Organisasi --}}
        <fieldset class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <legend class="font-bold text-gray-800 pb-3 border-b border-gray-100 w-full mb-5 block">
                Maklumat Organisasi
            </legend>
            <div class="space-y-5">

                <div>
                    <label for="nama_sistem" class="form-label">Nama Sistem</label>
                    <input type="text" id="nama_sistem" name="nama_sistem"
                        value="{{ old('nama_sistem', $tetapan['nama_sistem'] ?? '') }}"
                        class="form-input"
                        placeholder="cth: iBook 2.0"
                        @error('nama_sistem') aria-invalid="true" aria-describedby="ralat-nama_sistem" @enderror>
                    @error('nama_sistem')
                    <p id="ralat-nama_sistem" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nama_jabatan" class="form-label">
                        Nama Bahagian <span class="text-red-500" aria-hidden="true">*</span>
                        <span class="sr-only">(wajib)</span>
                    </label>
                    <input type="text" id="nama_jabatan" name="nama_jabatan"
                        value="{{ old('nama_jabatan', $tetapan['nama_jabatan'] ?? '') }}"
                        class="form-input"
                        placeholder="cth: Bahagian Pengurusan Teknologi Maklumat"
                        required aria-required="true"
                        @error('nama_jabatan') aria-invalid="true" aria-describedby="ralat-nama_jabatan" @enderror>
                    <p class="form-hint">Nama ini akan dipaparkan di footer sistem.</p>
                    @error('nama_jabatan')
                    <p id="ralat-nama_jabatan" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="emel_pentadbir" class="form-label">Emel Pentadbir Sistem</label>
                    <input type="email" id="emel_pentadbir" name="emel_pentadbir"
                        value="{{ old('emel_pentadbir', $tetapan['emel_pentadbir'] ?? '') }}"
                        class="form-input"
                        placeholder="cth: admin@jabatan.gov.my"
                        @error('emel_pentadbir') aria-invalid="true" aria-describedby="ralat-emel_pentadbir" @enderror>
                    <p class="form-hint">Emel ini akan dipaparkan di footer sistem.</p>
                    @error('emel_pentadbir')
                    <p id="ralat-emel_pentadbir" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </fieldset>

        {{-- Notifikasi --}}
        <fieldset class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <legend class="font-bold text-gray-800 pb-3 border-b border-gray-100 w-full mb-5 block">
                Notifikasi
            </legend>
            <div class="space-y-4">
                <div>
                    <label class="flex items-center gap-3 cursor-pointer" for="notif-tempahan-baru">
                        <input type="checkbox" id="notif-tempahan-baru" name="notif_tempahan_baru" value="1"
                            class="w-4 h-4 rounded flex-shrink-0" style="accent-color:#f59e0b"
                            {{ ($tetapan['notif_tempahan_baru'] ?? '1') === '1' ? 'checked' : '' }}>
                        <div>
                            <div class="font-semibold text-sm text-gray-700">E-mel notifikasi untuk tempahan baru</div>
                            <div class="text-xs text-gray-400">Hantar emel kepada urus setia apabila ada tempahan baru</div>
                        </div>
                    </label>
                </div>
                <div>
                    <label class="flex items-center gap-3 cursor-pointer" for="peringatan-mesyuarat">
                        <input type="checkbox" id="peringatan-mesyuarat" name="peringatan_mesyuarat" value="1"
                            class="w-4 h-4 rounded flex-shrink-0" style="accent-color:#f59e0b"
                            {{ ($tetapan['peringatan_mesyuarat'] ?? '1') === '1' ? 'checked' : '' }}>
                        <div>
                            <div class="font-semibold text-sm text-gray-700">Peringatan mesyuarat (1 jam sebelum)</div>
                            <div class="text-xs text-gray-400">Hantar peringatan emel 1 jam sebelum mesyuarat bermula</div>
                        </div>
                    </label>
                </div>
            </div>
        </fieldset>

        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Simpan Tetapan
        </button>
    </form>
</div>
@endsection
