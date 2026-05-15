@extends('layouts.app')

@section('title', 'Tempahan Baru')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Tempahan Baru</h1>
    <p class="text-gray-500 mt-1">Isi maklumat mesyuarat untuk membuat tempahan bilik.</p>
</div>

<div class="bg-white rounded-xl shadow-sm p-8 max-w-3xl">
    @if($errors->any())
    <div role="alert" aria-live="assertive" class="alert-error" id="ralat-borang">
        <p class="font-semibold mb-1">Sila betulkan ralat berikut:</p>
        <ul class="list-disc list-inside text-sm" aria-label="Senarai ralat borang">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('tempahan.store') }}" novalidate aria-label="Borang tempahan bilik mesyuarat baru">
        @csrf

        {{-- Nama Mesyuarat --}}
        <div class="mb-5">
            <label for="nama_mesyuarat" class="form-label">
                Nama Mesyuarat <span class="text-red-500" aria-hidden="true">*</span>
                <span class="sr-only">(wajib)</span>
            </label>
            <input type="text" id="nama_mesyuarat" name="nama_mesyuarat"
                value="{{ old('nama_mesyuarat') }}"
                required
                aria-required="true"
                @error('nama_mesyuarat') aria-invalid="true" aria-describedby="ralat-nama_mesyuarat" @enderror
                class="form-input @error('nama_mesyuarat') border-red-400 @enderror"
                placeholder="cth: Mesyuarat Pengurusan Bil. 4/2026">
            @error('nama_mesyuarat')
            <p id="ralat-nama_mesyuarat" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">

            {{-- Tarikh --}}
            <div>
                <label for="tarikh" class="form-label">
                    Tarikh <span class="text-red-500" aria-hidden="true">*</span>
                    <span class="sr-only">(wajib, mesti hari ini atau selepasnya)</span>
                </label>
                <input type="date" id="tarikh" name="tarikh"
                    value="{{ old('tarikh') }}"
                    min="{{ date('Y-m-d') }}"
                    required
                    aria-required="true"
                    @error('tarikh') aria-invalid="true" aria-describedby="ralat-tarikh" @enderror
                    class="form-input @error('tarikh') border-red-400 @enderror">
                @error('tarikh')
                <p id="ralat-tarikh" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>

            {{-- Bilik --}}
            <div>
                <label for="bilik_id" class="form-label">
                    Bilik Mesyuarat <span class="text-red-500" aria-hidden="true">*</span>
                    <span class="sr-only">(wajib)</span>
                </label>
                <select id="bilik_id" name="bilik_id"
                    required
                    aria-required="true"
                    @error('bilik_id') aria-invalid="true" aria-describedby="ralat-bilik_id" @enderror
                    class="form-input @error('bilik_id') border-red-400 @enderror">
                    <option value="">Pilih bilik</option>
                    @foreach($bilik as $b)
                    <option value="{{ $b->id }}" {{ old('bilik_id') == $b->id ? 'selected' : '' }}>
                        {{ $b->nama }} ({{ $b->kapasiti }} orang)
                    </option>
                    @endforeach
                </select>
                @error('bilik_id')
                <p id="ralat-bilik_id" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Sesi --}}
        <fieldset class="mb-5" @error('sesi') aria-describedby="ralat-sesi" @enderror>
            <legend class="form-label mb-2">
                Masa Mesyuarat <span class="text-red-500" aria-hidden="true">*</span>
                <span class="sr-only">(wajib)</span>
            </legend>
            <div class="space-y-3">
                @foreach($sesi as $key => $s)
                <label class="flex items-center gap-3 p-3 border-2 rounded-lg cursor-pointer transition-all
                    {{ old('sesi') === $key ? 'border-amber-400 bg-amber-50' : 'border-gray-200 hover:border-amber-300' }}"
                    onclick="selectSesi(this, '{{ $key }}')">
                    <input type="radio" name="sesi" value="{{ $key }}"
                        id="sesi-{{ $key }}"
                        class="text-amber-500"
                        {{ old('sesi') === $key ? 'checked' : '' }}
                        required
                        aria-required="true">
                    <div>
                        <div class="font-semibold text-gray-800">
                            {{ Str::upper(Str::replace('_', ' ', $key === 'pagi' ? 'SESI PAGI 1' : 'SESI PETANG 2')) }}
                        </div>
                        <div class="text-sm text-gray-500">{{ $s['label'] }}</div>
                    </div>
                </label>
                @endforeach
            </div>
            @error('sesi')
            <p id="ralat-sesi" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
            @enderror
        </fieldset>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">

            {{-- Bilangan Peserta --}}
            <div>
                <label for="bilangan_peserta" class="form-label">
                    Bilangan Peserta <span class="text-red-500" aria-hidden="true">*</span>
                    <span class="sr-only">(wajib, sekurang-kurangnya 1)</span>
                </label>
                <input type="number" id="bilangan_peserta" name="bilangan_peserta"
                    value="{{ old('bilangan_peserta') }}"
                    min="1"
                    required
                    aria-required="true"
                    @error('bilangan_peserta') aria-invalid="true" aria-describedby="ralat-bilangan_peserta" @enderror
                    class="form-input @error('bilangan_peserta') border-red-400 @enderror"
                    placeholder="cth: 20">
                @error('bilangan_peserta')
                <p id="ralat-bilangan_peserta" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kategori --}}
            <div>
                <label for="kategori" class="form-label">
                    Kategori Mesyuarat <span class="text-red-500" aria-hidden="true">*</span>
                    <span class="sr-only">(wajib)</span>
                </label>
                <select id="kategori" name="kategori"
                    required
                    aria-required="true"
                    @error('kategori') aria-invalid="true" aria-describedby="ralat-kategori" @enderror
                    class="form-input @error('kategori') border-red-400 @enderror">
                    <option value="">Pilih kategori</option>
                    @foreach($kategori as $k => $label)
                    <option value="{{ $k }}" {{ old('kategori') === $k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('kategori')
                <p id="ralat-kategori" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Nama Pengerusi --}}
        <div class="mb-5">
            <label for="nama_pengerusi" class="form-label">
                Nama Pengerusi <span class="text-red-500" aria-hidden="true">*</span>
                <span class="sr-only">(wajib)</span>
            </label>
            <input type="text" id="nama_pengerusi" name="nama_pengerusi"
                value="{{ old('nama_pengerusi') }}"
                required
                aria-required="true"
                @error('nama_pengerusi') aria-invalid="true" aria-describedby="ralat-nama_pengerusi" @enderror
                class="form-input @error('nama_pengerusi') border-red-400 @enderror"
                placeholder="cth: YBrs. Encik Ahmad">
            @error('nama_pengerusi')
            <p id="ralat-nama_pengerusi" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tujuan / Agenda --}}
        <div class="mb-7">
            <label for="tujuan" class="form-label">Tujuan / Agenda</label>
            <textarea id="tujuan" name="tujuan" rows="4"
                @error('tujuan') aria-invalid="true" aria-describedby="ralat-tujuan" @enderror
                class="form-input @error('tujuan') border-red-400 @enderror"
                placeholder="Nyatakan tujuan atau agenda mesyuarat">{{ old('tujuan') }}</textarea>
            @error('tujuan')
            <p id="ralat-tujuan" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Hantar Permohonan
            </button>
            <a href="{{ route('tempahan.index') }}" class="btn-secondary">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i> Batal
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function selectSesi(label, key) {
    document.querySelectorAll('[onclick^="selectSesi"]').forEach(el => {
        el.classList.remove('border-amber-400', 'bg-amber-50');
        el.classList.add('border-gray-200');
    });
    label.classList.remove('border-gray-200');
    label.classList.add('border-amber-400', 'bg-amber-50');
}
</script>
@endpush
@endsection
