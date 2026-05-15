@extends('layouts.app')

@section('title', $bilik ? 'Edit Bilik' : 'Tambah Bilik')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('bilik.index') }}" class="text-gray-400 hover:text-gray-600">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $bilik ? 'Edit Bilik Mesyuarat' : 'Tambah Bilik Mesyuarat' }}</h1>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-8 max-w-2xl">
    @if($errors->any())
    <div class="alert-error mb-5">
        <ul class="list-disc list-inside text-sm">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ $bilik ? route('bilik.update', $bilik) : route('bilik.store') }}">
        @csrf
        @if($bilik) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div class="md:col-span-2">
                <label class="form-label">Nama Bilik <span class="text-red-500">*</span></label>
                <input type="text" name="nama" value="{{ old('nama', $bilik?->nama) }}"
                    class="form-input" placeholder="cth: Bilik Mesyuarat Utama">
                @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Kapasiti (orang) <span class="text-red-500">*</span></label>
                <input type="number" name="kapasiti" value="{{ old('kapasiti', $bilik?->kapasiti) }}" min="1"
                    class="form-input" placeholder="cth: 40">
                @error('kapasiti') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Lokasi</label>
                <input type="text" name="lokasi" value="{{ old('lokasi', $bilik?->lokasi) }}"
                    class="form-input" placeholder="cth: Tingkat 3, Blok A">
            </div>
        </div>

        <div class="mb-5">
            <label class="form-label">Kemudahan</label>
            <div class="grid grid-cols-2 gap-3">
                @php
                $kemudahanList = ['Projektor', 'Papan Putih', 'Sistem Audio', 'Video Conferencing', 'Skrin LCD', 'Pendingin Hawa', 'WiFi'];
                $selected = old('kemudahan', $bilik?->kemudahan ?? []);
                @endphp
                @foreach($kemudahanList as $k)
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="checkbox" name="kemudahan[]" value="{{ $k }}"
                        class="rounded" style="accent-color:#f59e0b"
                        {{ in_array($k, $selected) ? 'checked' : '' }}>
                    {{ $k }}
                </label>
                @endforeach
            </div>
        </div>

        <div class="mb-7">
            <label class="form-label">Status <span class="text-red-500">*</span></label>
            <select name="status" class="form-input">
                <option value="aktif" {{ old('status', $bilik?->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="tidak_aktif" {{ old('status', $bilik?->status) === 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
            </select>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> {{ $bilik ? 'Kemaskini' : 'Simpan' }}
            </button>
            <a href="{{ route('bilik.index') }}" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
