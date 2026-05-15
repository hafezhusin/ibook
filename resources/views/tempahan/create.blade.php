@extends('layouts.app')

@section('title', 'Tempahan Baru')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Tempahan Baru</h1>
    <p class="text-gray-500 mt-1">Isi maklumat mesyuarat untuk membuat tempahan bilik.</p>
</div>

<div class="bg-white rounded-xl shadow-sm p-8 max-w-3xl">
    @if($errors->any())
    <div class="alert-error">
        <ul class="list-disc list-inside text-sm">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('tempahan.store') }}">
        @csrf

        <div class="mb-5">
            <label class="form-label">Nama Mesyuarat <span class="text-red-500">*</span></label>
            <input type="text" name="nama_mesyuarat" value="{{ old('nama_mesyuarat') }}"
                class="form-input @error('nama_mesyuarat') border-red-400 @enderror"
                placeholder="cth: Mesyuarat Pengurusan Bil. 4/2026">
            @error('nama_mesyuarat') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div>
                <label class="form-label">Tarikh <span class="text-red-500">*</span></label>
                <input type="date" name="tarikh" value="{{ old('tarikh') }}"
                    min="{{ date('Y-m-d') }}"
                    class="form-input @error('tarikh') border-red-400 @enderror">
                @error('tarikh') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Bilik Mesyuarat <span class="text-red-500">*</span></label>
                <select name="bilik_id" class="form-input @error('bilik_id') border-red-400 @enderror">
                    <option value="">Pilih bilik</option>
                    @foreach($bilik as $b)
                    <option value="{{ $b->id }}" {{ old('bilik_id') == $b->id ? 'selected' : '' }}>
                        {{ $b->nama }} ({{ $b->kapasiti }} orang)
                    </option>
                    @endforeach
                </select>
                @error('bilik_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mb-5">
            <label class="form-label">Masa Mesyuarat <span class="text-red-500">*</span></label>
            <div class="space-y-3">
                @foreach($sesi as $key => $s)
                <label class="flex items-center gap-3 p-3 border-2 rounded-lg cursor-pointer transition-all
                    {{ old('sesi') === $key ? 'border-amber-400 bg-amber-50' : 'border-gray-200 hover:border-amber-300' }}"
                    onclick="selectSesi(this, '{{ $key }}')">
                    <input type="radio" name="sesi" value="{{ $key }}" class="text-amber-500"
                        {{ old('sesi') === $key ? 'checked' : '' }}>
                    <div>
                        <div class="font-semibold text-gray-800">{{ Str::upper(Str::replace('_', ' ', $key === 'pagi' ? 'SESI PAGI 1' : 'SESI PETANG 2')) }}</div>
                        <div class="text-sm text-gray-500">{{ $s['label'] }}</div>
                    </div>
                </label>
                @endforeach
            </div>
            @error('sesi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div>
                <label class="form-label">Bilangan Peserta <span class="text-red-500">*</span></label>
                <input type="number" name="bilangan_peserta" value="{{ old('bilangan_peserta') }}" min="1"
                    class="form-input @error('bilangan_peserta') border-red-400 @enderror"
                    placeholder="cth: 20">
                @error('bilangan_peserta') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Kategori Mesyuarat <span class="text-red-500">*</span></label>
                <select name="kategori" class="form-input @error('kategori') border-red-400 @enderror">
                    <option value="">Pilih kategori</option>
                    @foreach($kategori as $k => $label)
                    <option value="{{ $k }}" {{ old('kategori') === $k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('kategori') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mb-5">
            <label class="form-label">Nama Pengerusi <span class="text-red-500">*</span></label>
            <input type="text" name="nama_pengerusi" value="{{ old('nama_pengerusi') }}"
                class="form-input @error('nama_pengerusi') border-red-400 @enderror"
                placeholder="cth: YBrs. Encik Ahmad">
            @error('nama_pengerusi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-7">
            <label class="form-label">Tujuan / Agenda</label>
            <textarea name="tujuan" rows="4"
                class="form-input @error('tujuan') border-red-400 @enderror"
                placeholder="Nyatakan tujuan atau agenda mesyuarat">{{ old('tujuan') }}</textarea>
            @error('tujuan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-paper-plane"></i> Hantar Permohonan
            </button>
            <a href="{{ route('tempahan.index') }}" class="btn-secondary">
                <i class="fa-solid fa-xmark"></i> Batal
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
