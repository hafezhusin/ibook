@extends('layouts.app')

@section('title', 'Edit Tempahan')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ms.js"></script>
@endpush

@section('content')

<div class="mb-6">
    <a href="{{ route('tempahan.index') }}"
       class="text-sm text-gray-500 hover:text-amber-500 flex items-center gap-1 mb-3 w-fit">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Kembali ke Senarai
    </a>
    <h1 class="text-2xl font-bold text-gray-800">Edit Tempahan</h1>
    <p class="text-gray-500 text-sm mt-1">Kemaskini maklumat tempahan di bawah</p>
</div>

{{-- Info asal --}}
<div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-3 mb-6 flex items-center gap-3 text-sm text-amber-800">
    <i class="fa-solid fa-circle-info text-amber-500 flex-shrink-0" aria-hidden="true"></i>
    <span>
        Tempahan asal: <strong>{{ $tempahan->nama_mesyuarat }}</strong> —
        {{ $tempahan->tarikh->format('d/m/Y') }},
        {{ $tempahan->bilik->nama ?? '-' }}
    </span>
</div>

<form method="POST" action="{{ route('tempahan.update', $tempahan) }}" novalidate>
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kolum Kiri (2/3) --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Nama Mesyuarat --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="font-bold text-gray-700 mb-4">Maklumat Mesyuarat</h2>
                <div class="space-y-4">

                    <div>
                        <label for="nama_mesyuarat" class="form-label">
                            Nama Mesyuarat <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input type="text" id="nama_mesyuarat" name="nama_mesyuarat"
                            value="{{ old('nama_mesyuarat', $tempahan->nama_mesyuarat) }}"
                            class="form-input @error('nama_mesyuarat') !border-red-400 @enderror"
                            required maxlength="255"
                            aria-required="true"
                            aria-describedby="@error('nama_mesyuarat') err-nama @enderror">
                        @error('nama_mesyuarat')
                        <p id="err-nama" class="form-error" role="alert">
                            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="nama_pengerusi" class="form-label">
                                Nama Pengerusi <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input type="text" id="nama_pengerusi" name="nama_pengerusi"
                                value="{{ old('nama_pengerusi', $tempahan->nama_pengerusi) }}"
                                class="form-input @error('nama_pengerusi') !border-red-400 @enderror"
                                required maxlength="255" aria-required="true">
                            @error('nama_pengerusi')
                            <p class="form-error" role="alert">
                                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <div>
                            <label for="bilangan_peserta" class="form-label">
                                Bilangan Peserta <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input type="number" id="bilangan_peserta" name="bilangan_peserta"
                                value="{{ old('bilangan_peserta', $tempahan->bilangan_peserta) }}"
                                class="form-input @error('bilangan_peserta') !border-red-400 @enderror"
                                min="1" required aria-required="true">
                            @error('bilangan_peserta')
                            <p class="form-error" role="alert">
                                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}
                            </p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="kategori" class="form-label">
                            Kategori <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <select id="kategori" name="kategori"
                            class="form-input @error('kategori') !border-red-400 @enderror"
                            required aria-required="true">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategori as $key => $label)
                            <option value="{{ $key }}" {{ old('kategori', $tempahan->kategori) === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                        @error('kategori')
                        <p class="form-error" role="alert">
                            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <div>
                        <label for="tujuan" class="form-label">Tujuan / Agenda</label>
                        <textarea id="tujuan" name="tujuan" rows="3"
                            class="form-input @error('tujuan') !border-red-400 @enderror"
                            maxlength="1000"
                            placeholder="Huraian ringkas tujuan mesyuarat (pilihan)">{{ old('tujuan', $tempahan->tujuan) }}</textarea>
                        @error('tujuan')
                        <p class="form-error" role="alert">
                            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}
                        </p>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- Kolum Kanan (1/3) --}}
        <div class="space-y-5">

            {{-- Tarikh, Sesi, Bilik --}}
            <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
                <h2 class="font-bold text-gray-700 mb-1">Masa &amp; Tempat</h2>

                <div>
                    <label for="tarikh" class="form-label">
                        Tarikh <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" id="tarikh" name="tarikh"
                            value="{{ old('tarikh', $tempahan->tarikh->format('Y-m-d')) }}"
                            class="form-input pr-10 @error('tarikh') !border-red-400 @enderror"
                            readonly required aria-required="true">
                        <i class="fa-solid fa-calendar-days absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" aria-hidden="true"></i>
                    </div>
                    @error('tarikh')
                    <p class="form-error" role="alert">
                        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}
                    </p>
                    @enderror
                </div>

                <div>
                    <label for="sesi" class="form-label">
                        Sesi <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <select id="sesi" name="sesi"
                        class="form-input @error('sesi') !border-red-400 @enderror"
                        required aria-required="true">
                        @foreach($sesi as $key => $info)
                        <option value="{{ $key }}" {{ old('sesi', $tempahan->sesi) === $key ? 'selected' : '' }}>
                            {{ $info['label'] }}
                        </option>
                        @endforeach
                    </select>
                    @error('sesi')
                    <p class="form-error" role="alert">
                        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}
                    </p>
                    @enderror
                </div>

                <div>
                    <label for="bilik_id" class="form-label">
                        Bilik Mesyuarat <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <select id="bilik_id" name="bilik_id"
                        class="form-input @error('bilik_id') !border-red-400 @enderror"
                        required aria-required="true">
                        <option value="">-- Pilih Bilik --</option>
                        @foreach($bilik as $b)
                        <option value="{{ $b->id }}"
                            data-kapasiti="{{ $b->kapasiti }}"
                            {{ old('bilik_id', $tempahan->bilik_id) == $b->id ? 'selected' : '' }}>
                            {{ $b->nama }} ({{ $b->kapasiti }} orang)
                        </option>
                        @endforeach
                    </select>
                    @error('bilik_id')
                    <p class="form-error" role="alert">
                        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}
                    </p>
                    @enderror
                </div>
            </div>

            {{-- Butang --}}
            <div class="bg-white rounded-xl shadow-sm p-6 space-y-3">
                <button type="submit" class="btn-primary w-full justify-center">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Simpan Perubahan
                </button>
                <a href="{{ route('tempahan.index') }}"
                   class="btn-secondary w-full justify-center">
                    Batal
                </a>
                <a href="{{ route('tempahan.show', $tempahan) }}"
                   class="flex items-center justify-center gap-1 text-sm text-gray-400 hover:text-gray-600 pt-1">
                    <i class="fa-solid fa-eye" aria-hidden="true"></i> Lihat Butiran Asal
                </a>
            </div>

        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
flatpickr('#tarikh', {
    locale: 'ms',
    dateFormat: 'Y-m-d',
    disableMobile: true,
    defaultDate: '{{ old('tarikh', $tempahan->tarikh->format('Y-m-d')) }}'
});
</script>
@endpush
