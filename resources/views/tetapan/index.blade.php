@extends('layouts.app')

@section('title', 'Tetapan')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Tetapan</h1>
    <p class="text-gray-500 text-sm mt-1">Konfigurasi sistem</p>
</div>

<div class="max-w-2xl">
    <form method="POST" action="{{ route('tetapan.update') }}">
        @csrf

        {{-- Maklumat Organisasi --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="font-bold text-gray-800 mb-5 pb-3 border-b border-gray-100">Maklumat Organisasi</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="form-label">Nama Jabatan <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_jabatan" value="{{ old('nama_jabatan', $tetapan['nama_jabatan'] ?? '') }}"
                        class="form-input" placeholder="cth: Bahagian Pengurusan Teknologi Maklumat">
                    @error('nama_jabatan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Singkatan <span class="text-red-500">*</span></label>
                    <input type="text" name="singkatan" value="{{ old('singkatan', $tetapan['singkatan'] ?? '') }}"
                        class="form-input" placeholder="cth: BPTM">
                    @error('singkatan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Waktu Operasi --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="font-bold text-gray-800 mb-5 pb-3 border-b border-gray-100">Waktu Operasi</h2>
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Masa Mula <span class="text-red-500">*</span></label>
                    <input type="time" name="masa_mula" value="{{ old('masa_mula', $tetapan['masa_mula'] ?? '08:00') }}"
                        class="form-input">
                    @error('masa_mula') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Masa Tamat <span class="text-red-500">*</span></label>
                    <input type="time" name="masa_tamat" value="{{ old('masa_tamat', $tetapan['masa_tamat'] ?? '17:00') }}"
                        class="form-input">
                    @error('masa_tamat') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Notifikasi --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h2 class="font-bold text-gray-800 mb-5 pb-3 border-b border-gray-100">Notifikasi</h2>
            <div class="space-y-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="notif_tempahan_baru" value="1"
                        class="w-4 h-4 rounded" style="accent-color:#f59e0b"
                        {{ ($tetapan['notif_tempahan_baru'] ?? '1') === '1' ? 'checked' : '' }}>
                    <div>
                        <div class="font-semibold text-sm text-gray-700">E-mel notifikasi untuk tempahan baru</div>
                        <div class="text-xs text-gray-400">Hantar emel kepada urus setia apabila ada tempahan baru</div>
                    </div>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="notif_kelulusan" value="1"
                        class="w-4 h-4 rounded" style="accent-color:#f59e0b"
                        {{ ($tetapan['notif_kelulusan'] ?? '1') === '1' ? 'checked' : '' }}>
                    <div>
                        <div class="font-semibold text-sm text-gray-700">E-mel notifikasi untuk kelulusan/penolakan</div>
                        <div class="text-xs text-gray-400">Hantar emel kepada pemohon selepas keputusan</div>
                    </div>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="peringatan_mesyuarat" value="1"
                        class="w-4 h-4 rounded" style="accent-color:#f59e0b"
                        {{ ($tetapan['peringatan_mesyuarat'] ?? '1') === '1' ? 'checked' : '' }}>
                    <div>
                        <div class="font-semibold text-sm text-gray-700">Peringatan mesyuarat (1 jam sebelum)</div>
                        <div class="text-xs text-gray-400">Hantar peringatan emel 1 jam sebelum mesyuarat bermula</div>
                    </div>
                </label>
            </div>
        </div>

        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Tetapan
        </button>
    </form>
</div>
@endsection
