@extends('layouts.app')

@section('title', 'Tempahan Baru')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* Flatpickr overrides to match system theme */
    .flatpickr-day.selected, .flatpickr-day.selected:hover {
        background: #f59e0b !important; border-color: #f59e0b !important;
    }
    .flatpickr-day.today { border-color: #f59e0b !important; }
    .flatpickr-input { cursor: pointer !important; }

    /* Conflict / warning states on sesi cards */
    .sesi-konflik {
        border-color: #dc2626 !important;
        background: #fff1f2 !important;
        opacity: 0.65;
    }
    .sesi-konflik .sesi-status-badge {
        display: inline-flex !important;
    }
    .kapasiti-warning {
        display: flex; align-items: center; gap: 6px;
        font-size: 12px; color: #b45309;
        background: #fef3c7; border: 1px solid #fcd34d;
        border-radius: 6px; padding: 6px 10px; margin-top: 6px;
    }
    .kapasiti-warning.lebih {
        color: #991b1b; background: #fee2e2; border-color: #fca5a5;
    }
    #info-kapasiti { display: none; }
</style>
@endpush

@section('content')
@php
    // Helper: nilai dari old() → duplikat → null
    $val = fn($field) => old($field, $duplikat[$field] ?? null);
@endphp

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Tempahan Baru</h1>
        <p class="text-gray-500 mt-1">
            @if($duplikat)
                <span class="inline-flex items-center gap-1 text-amber-600 font-semibold text-sm bg-amber-50 px-2 py-0.5 rounded-full">
                    <i class="fa-solid fa-copy" aria-hidden="true"></i> Duplikat — semak dan kemaskini maklumat sebelum hantar
                </span>
            @else
                Isi maklumat mesyuarat untuk membuat tempahan bilik.
            @endif
        </p>
    </div>
    <a href="{{ route('tempahan.index') }}" class="text-sm text-gray-400 hover:text-gray-600">
        ← Kembali
    </a>
</div>

<div class="max-w-3xl space-y-5">
    @if($errors->any())
    <div role="alert" aria-live="assertive" class="alert-error" id="ralat-borang">
        <p class="font-semibold mb-1"><i class="fa-solid fa-circle-xmark mr-1" aria-hidden="true"></i>Sila betulkan ralat berikut:</p>
        <ul class="list-disc list-inside text-sm" aria-label="Senarai ralat borang">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form id="borang-tempahan" method="POST" action="{{ route('tempahan.store') }}" novalidate aria-label="Borang tempahan bilik mesyuarat baru">
        @csrf

        {{-- ══ Bahagian 1: Maklumat Mesyuarat ══════════════════════ --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-white text-[10px] font-bold flex-shrink-0" style="background:#f59e0b" aria-hidden="true">1</span>
                Maklumat Mesyuarat
            </h2>

            {{-- Nama Mesyuarat --}}
            <div class="mb-4">
                <label for="nama_mesyuarat" class="form-label">
                    Nama Mesyuarat <span class="text-red-500" aria-hidden="true">*</span>
                    <span class="sr-only">(wajib)</span>
                </label>
                <input type="text" id="nama_mesyuarat" name="nama_mesyuarat"
                    value="{{ $val('nama_mesyuarat') }}"
                    required
                    aria-required="true"
                    @error('nama_mesyuarat') aria-invalid="true" aria-describedby="ralat-nama_mesyuarat" @enderror
                    class="form-input @error('nama_mesyuarat') border-red-400 @enderror"
                    placeholder="cth: Mesyuarat Pengurusan Bil. 4/2026">
                @error('nama_mesyuarat')
                <p id="ralat-nama_mesyuarat" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
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
                    <option value="{{ $k }}" {{ $val('kategori') === $k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('kategori')
                <p id="ralat-kategori" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- ══ Bahagian 2: Slot & Lokasi ════════════════════════════ --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-white text-[10px] font-bold flex-shrink-0" style="background:#f59e0b" aria-hidden="true">2</span>
                Slot &amp; Lokasi
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">

                {{-- Tarikh --}}
                <div>
                    <label for="tarikh" class="form-label">
                        Tarikh <span class="text-red-500" aria-hidden="true">*</span>
                        <span class="sr-only">(wajib, mesti hari ini atau selepasnya)</span>
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-calendar-days absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" aria-hidden="true"></i>
                        <input type="text" id="tarikh" name="tarikh"
                            value="{{ old('tarikh', $duplikat['tarikh'] ?? '') }}"
                            required
                            aria-required="true"
                            placeholder="YYYY-MM-DD atau pilih dari kalendar"
                            @error('tarikh') aria-invalid="true" aria-describedby="ralat-tarikh" @enderror
                            class="form-input pl-10 @error('tarikh') border-red-400 @enderror">
                    </div>
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
                        <option value="{{ $b->id }}"
                                data-kapasiti="{{ $b->kapasiti }}"
                                {{ $val('bilik_id') == $b->id ? 'selected' : '' }}>
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
            <fieldset @error('sesi') aria-describedby="ralat-sesi" @enderror>
                <legend class="form-label mb-2">
                    Sesi Mesyuarat <span class="text-red-500" aria-hidden="true">*</span>
                    <span class="sr-only">(wajib, boleh pilih satu atau kedua-dua sesi)</span>
                </legend>
                <p class="text-xs text-gray-400 mb-3">Boleh pilih satu atau kedua-dua sesi.</p>
                <div class="space-y-3">
                    @foreach($sesi as $key => $s)
                    @php
                        $sesiPrefill = old('sesi') ?? ($duplikat['sesi'] ?? []);
                        $checked = is_array($sesiPrefill) && in_array($key, $sesiPrefill);
                    @endphp
                    <label class="flex items-center gap-3 p-3 border-2 rounded-lg cursor-pointer transition-all
                        {{ $checked ? 'border-amber-400 bg-amber-50' : 'border-gray-200 hover:border-amber-300' }}"
                        id="label-sesi-{{ $key }}">
                        <input type="checkbox" name="sesi[]" value="{{ $key }}"
                            id="sesi-{{ $key }}"
                            class="text-amber-500 w-4 h-4 rounded flex-shrink-0"
                            style="accent-color:#f59e0b"
                            {{ $checked ? 'checked' : '' }}>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-800 flex items-center gap-2">
                                {{ $key === 'pagi' ? 'Sesi Pagi' : 'Sesi Petang' }}
                                <span class="sesi-status-badge hidden text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-700">
                                    <i class="fa-solid fa-ban mr-1" aria-hidden="true"></i>Telah Ditempah
                                </span>
                            </div>
                            <div class="text-sm text-gray-500">{{ $s['label'] }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
                {{-- Pintasan: Sehari Penuh = tandakan kedua-dua sesi sekaligus --}}
                <div class="mt-3 flex items-center gap-2">
                    <button type="button"
                        id="btn-sehari-penuh"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-full px-3 py-1.5 transition-colors"
                        aria-label="Pilih kedua-dua sesi pagi dan petang sekaligus">
                        <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                        Sehari Penuh
                    </button>
                    <span class="text-xs text-gray-400">— tandakan kedua-dua sesi sekaligus</span>
                </div>

                <div id="info-konflik" class="hidden mt-2 text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2" role="alert" aria-live="polite">
                    <i class="fa-solid fa-triangle-exclamation mr-1" aria-hidden="true"></i>
                    <span id="info-konflik-teks"></span>
                </div>
                @error('sesi')
                <p id="ralat-sesi" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                @enderror
            </fieldset>
        </div>

        {{-- ══ Bahagian 2.5: Ulang Tempahan (Opsional) ═══════════════ --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-1">
                <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-white text-[10px] font-bold flex-shrink-0" style="background:#8b5cf6" aria-hidden="true">↻</span>
                    Ulang Tempahan
                </h2>
                <label class="flex items-center gap-2 cursor-pointer select-none" aria-label="Aktifkan tempahan berulang">
                    <span class="text-xs text-gray-400">Aktifkan</span>
                    <div class="relative">
                        <input type="checkbox" id="toggle-berulang" class="sr-only peer" aria-controls="panel-berulang">
                        <div class="w-10 h-5 bg-gray-200 rounded-full peer-checked:bg-violet-500 transition-colors"></div>
                        <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                    </div>
                </label>
            </div>
            <p class="text-xs text-gray-400 mb-3">Hidupkan untuk membuat tempahan berulang secara automatik. Maksimum 12 kejadian.</p>

            <div id="panel-berulang" class="hidden space-y-4 border-t pt-4 mt-2 border-gray-100">

                {{-- Jenis & Selang --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="jenis" class="form-label">Jenis Ulangan <span class="text-red-500" aria-hidden="true">*</span></label>
                        <select id="jenis" name="jenis" class="form-input" disabled>
                            <option value="mingguan">Mingguan</option>
                            <option value="bulanan">Bulanan (tarikh sama setiap bulan)</option>
                        </select>
                    </div>
                    <div>
                        <label for="setiap_n" class="form-label">Ulang Setiap</label>
                        <div class="flex items-center gap-2">
                            <input type="number" id="setiap_n" name="setiap_n" value="1" min="1" max="12"
                                class="form-input w-24" disabled aria-describedby="label-setiap-n">
                            <span id="label-setiap-n" class="text-sm text-gray-500">minggu</span>
                        </div>
                    </div>
                </div>

                {{-- Hari dalam minggu (mingguan sahaja) --}}
                <div id="panel-hari">
                    <label class="form-label">Hari dalam Minggu <span class="text-red-500" aria-hidden="true">*</span></label>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @foreach(['Ahad'=>0,'Isnin'=>1,'Selasa'=>2,'Rabu'=>3,'Khamis'=>4,'Jumaat'=>5,'Sabtu'=>6] as $namaHari => $dow)
                        <label class="hari-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border-2 border-gray-200 text-sm font-medium cursor-pointer transition-all select-none has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50 has-[:checked]:text-violet-700">
                            <input type="checkbox" name="hari_dalam_minggu[]" value="{{ $dow }}"
                                class="hari-cb sr-only" disabled>
                            {{ $namaHari }}
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Tarikh Mula & Tamat --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="tarikh_mula_berulang" class="form-label">Tarikh Mula <span class="text-red-500" aria-hidden="true">*</span></label>
                        <input type="text" id="tarikh_mula_berulang" name="tarikh_mula"
                            class="form-input" placeholder="YYYY-MM-DD" disabled readonly>
                        <p class="text-xs text-gray-400 mt-1">Ikut tarikh yang dipilih di atas.</p>
                    </div>
                    <div>
                        <label for="tarikh_tamat" class="form-label">Tarikh Tamat Ulangan <span class="text-red-500" aria-hidden="true">*</span></label>
                        <input type="text" id="tarikh_tamat" name="tarikh_tamat"
                            class="form-input" placeholder="YYYY-MM-DD" disabled>
                        @error('tarikh_tamat')
                        <p class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Pratonton Tarikh --}}
                <div id="panel-pratonton" class="hidden bg-violet-50 border border-violet-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xs font-bold text-violet-700 uppercase tracking-wider">Pratonton Tarikh</h4>
                        <span id="pratonton-jumlah" class="text-xs font-semibold text-violet-500"></span>
                    </div>
                    <ul id="pratonton-senarai" class="text-xs text-violet-800 space-y-0.5 max-h-36 overflow-y-auto list-disc list-inside"></ul>
                    <p id="pratonton-had" class="hidden text-xs text-amber-600 mt-2 flex items-center gap-1">
                        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                        Had 12 kejadian dicapai — tarikh selebihnya tidak akan dicipta.
                    </p>
                </div>

                @error('tarikh_mula')
                <p class="text-red-500 text-xs" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- ══ Bahagian 3: Butiran Penganjur ════════════════════════ --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-white text-[10px] font-bold flex-shrink-0" style="background:#f59e0b" aria-hidden="true">3</span>
                Butiran Penganjur
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">

                {{-- Bilangan Peserta --}}
                <div>
                    <label for="bilangan_peserta" class="form-label">
                        Bilangan Peserta <span class="text-red-500" aria-hidden="true">*</span>
                        <span class="sr-only">(wajib, sekurang-kurangnya 1)</span>
                    </label>
                    <input type="number" id="bilangan_peserta" name="bilangan_peserta"
                        value="{{ $val('bilangan_peserta') }}"
                        min="1"
                        required
                        aria-required="true"
                        @error('bilangan_peserta') aria-invalid="true" aria-describedby="ralat-bilangan_peserta" @enderror
                        class="form-input @error('bilangan_peserta') border-red-400 @enderror"
                        placeholder="cth: 20">
                    <div id="info-kapasiti" role="alert" aria-live="polite"></div>
                    @error('bilangan_peserta')
                    <p id="ralat-bilangan_peserta" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nama Pengerusi --}}
                <div>
                    <label for="nama_pengerusi" class="form-label">
                        Nama Pengerusi <span class="text-red-500" aria-hidden="true">*</span>
                        <span class="sr-only">(wajib)</span>
                    </label>
                    <input type="text" id="nama_pengerusi" name="nama_pengerusi"
                        value="{{ $val('nama_pengerusi') }}"
                        required
                        aria-required="true"
                        @error('nama_pengerusi') aria-invalid="true" aria-describedby="ralat-nama_pengerusi" @enderror
                        class="form-input @error('nama_pengerusi') border-red-400 @enderror"
                        placeholder="cth: YBrs. Encik Ahmad">
                    @error('nama_pengerusi')
                    <p id="ralat-nama_pengerusi" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Catatan / Tujuan --}}
            <div>
                <label for="tujuan" class="form-label">Tujuan / Agenda <span class="text-gray-400 font-normal text-xs">(pilihan)</span></label>
                <textarea id="tujuan" name="tujuan" rows="4"
                    @error('tujuan') aria-invalid="true" aria-describedby="ralat-tujuan" @enderror
                    class="form-input @error('tujuan') border-red-400 @enderror"
                    placeholder="Nyatakan tujuan atau agenda mesyuarat">{{ $val('tujuan') }}</textarea>
                @error('tujuan')
                <p id="ralat-tujuan" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Panel Ringkasan Pra-Hantar — muncul apabila bilik + tarikh + sesi dipilih --}}
        <div id="panel-ringkasan"
             class="hidden bg-indigo-50 border border-indigo-200 rounded-xl p-5"
             role="status"
             aria-live="polite"
             aria-label="Ringkasan tempahan sebelum dihantar">
            <h3 class="text-sm font-bold text-indigo-800 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-clipboard-check text-indigo-500" aria-hidden="true"></i>
                Semak Sebelum Hantar
            </h3>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-2.5 text-sm">
                <div>
                    <dt class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider mb-0.5">Bilik</dt>
                    <dd class="font-semibold text-indigo-900" id="rs-bilik">—</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider mb-0.5">Tarikh</dt>
                    <dd class="font-semibold text-indigo-900" id="rs-tarikh">—</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider mb-0.5">Sesi</dt>
                    <dd class="font-semibold text-indigo-900" id="rs-sesi">—</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider mb-0.5">Kapasiti Bilik</dt>
                    <dd class="font-semibold text-indigo-900" id="rs-kapasiti">—</dd>
                </div>
            </dl>
            <p class="text-xs text-indigo-400 mt-3 flex items-center gap-1.5">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                Semak maklumat ini sebelum klik "Hantar Permohonan".
            </p>
        </div>

        {{-- Butang --}}
        <div class="flex gap-3 pb-2">
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ms.js"></script>
<script nonce="{{ $cspNonce }}">
// ── Flatpickr date picker ──────────────────────────────────────────
const tarikhInput = document.getElementById('tarikh');
flatpickr(tarikhInput, {
    locale: 'ms',
    dateFormat: 'Y-m-d',
    minDate: 'today',
    disableMobile: true,
    allowInput: true,   // Benarkan input keyboard — keyboard users & power users
    onChange: function() {
        semakKonflik();
        kemaskiniRingkasan();
    }
});

// ── Semakan konflik masa nyata ─────────────────────────────────────
const bilikSelect = document.getElementById('bilik_id');
const pesertaInput = document.getElementById('bilangan_peserta');
const infoKapasiti = document.getElementById('info-kapasiti');
const infoKonflik  = document.getElementById('info-konflik');
const infoKonflikTeks = document.getElementById('info-konflik-teks');

let kapasitiSemasa = 0;

function semakKonflik() {
    const bilikId = bilikSelect.value;
    const tarikh  = tarikhInput.value;

    // Reset states
    resetSesiState();

    if (!bilikId || !tarikh) return;

    fetch(`{{ route('tempahan.cek-konflik') }}?bilik_id=${encodeURIComponent(bilikId)}&tarikh=${encodeURIComponent(tarikh)}`)
        .then(r => r.json())
        .then(data => {
            kapasitiSemasa = data.kapasiti || 0;
            semakKapasiti(); // refresh kapasiti warning with new data

            let konflikMesej = [];

            ['pagi', 'petang'].forEach(sesi => {
                if (data[sesi]) {
                    // Mark this sesi as booked
                    const label = document.getElementById('label-sesi-' + sesi);
                    const cb    = document.getElementById('sesi-' + sesi);
                    const badge = label.querySelector('.sesi-status-badge');

                    label.classList.add('sesi-konflik');
                    label.classList.remove('border-amber-400', 'bg-amber-50', 'border-gray-200', 'hover:border-amber-300');
                    badge.classList.remove('hidden');

                    // Uncheck and disable
                    cb.checked = false;
                    cb.disabled = true;

                    const namaLabel = sesi === 'pagi' ? 'Sesi Pagi' : 'Sesi Petang';
                    konflikMesej.push(namaLabel);
                }
            });

            if (konflikMesej.length > 0) {
                infoKonflikTeks.textContent = konflikMesej.join(' dan ') + ' sudah ditempah untuk bilik dan tarikh ini.';
                infoKonflik.classList.remove('hidden');
            }
        })
        .catch(() => {
            // Fail silently on network error
        });
}

function resetSesiState() {
    ['pagi', 'petang'].forEach(sesi => {
        const label = document.getElementById('label-sesi-' + sesi);
        const cb    = document.getElementById('sesi-' + sesi);
        const badge = label.querySelector('.sesi-status-badge');

        label.classList.remove('sesi-konflik');
        badge.classList.add('hidden');
        cb.disabled = false;

        // Restore default border
        if (cb.checked) {
            label.classList.add('border-amber-400', 'bg-amber-50');
            label.classList.remove('border-gray-200');
        } else {
            label.classList.remove('border-amber-400', 'bg-amber-50');
            label.classList.add('border-gray-200');
        }
    });

    infoKonflik.classList.add('hidden');
    infoKonflikTeks.textContent = '';
}

// ── Amaran kapasiti ────────────────────────────────────────────────
function semakKapasiti() {
    const peserta = parseInt(pesertaInput.value, 10);
    if (!kapasitiSemasa || !peserta || peserta <= 0) {
        infoKapasiti.style.display = 'none';
        infoKapasiti.innerHTML = '';
        return;
    }

    infoKapasiti.style.display = 'flex';

    if (peserta > kapasitiSemasa) {
        infoKapasiti.className = 'kapasiti-warning lebih';
        infoKapasiti.innerHTML = `<i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> Bilangan peserta (${peserta}) melebihi kapasiti bilik (${kapasitiSemasa} orang). Tempahan tidak akan diterima.`;
    } else if (peserta > kapasitiSemasa * 0.8) {
        infoKapasiti.className = 'kapasiti-warning';
        infoKapasiti.innerHTML = `<i class="fa-solid fa-circle-info" aria-hidden="true"></i> Hampir penuh — kapasiti bilik: ${kapasitiSemasa} orang.`;
    } else {
        infoKapasiti.className = 'kapasiti-warning';
        infoKapasiti.innerHTML = `<i class="fa-solid fa-circle-check" style="color:#16a34a" aria-hidden="true"></i> <span style="color:#166534">Kapasiti bilik: ${kapasitiSemasa} orang — mencukupi.</span>`;
    }
}

// Update kapasiti when bilik changes
bilikSelect.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    kapasitiSemasa = parseInt(opt.dataset.kapasiti, 10) || 0;
    semakKapasiti();
    semakKonflik();
    kemaskiniRingkasan();
});

pesertaInput.addEventListener('input', semakKapasiti);

// ── Togol sesi ─────────────────────────────────────────────────────
function toggleSesi(key) {
    const cb    = document.getElementById('sesi-' + key);
    const label = document.getElementById('label-sesi-' + key);
    if (!cb || cb.disabled) return;
    if (cb.checked) {
        label.classList.add('border-amber-400', 'bg-amber-50');
        label.classList.remove('border-gray-200');
    } else {
        label.classList.remove('border-amber-400', 'bg-amber-50');
        label.classList.add('border-gray-200');
    }
    kemaskiniRingkasan();
}

// ── Pintasan Sehari Penuh ──────────────────────────────────────────
function pilihSehariPenuh() {
    ['pagi', 'petang'].forEach(sesi => {
        const cb    = document.getElementById('sesi-' + sesi);
        const label = document.getElementById('label-sesi-' + sesi);
        if (cb && !cb.disabled) {
            cb.checked = true;
            label.classList.add('border-amber-400', 'bg-amber-50');
            label.classList.remove('border-gray-200');
        }
    });
    kemaskiniRingkasan();
}

// ── Panel Ringkasan Pra-Hantar ─────────────────────────────────────
// Muncul apabila bilik + tarikh + sekurang-kurangnya 1 sesi dipilih.
function kemaskiniRingkasan() {
    const panel = document.getElementById('panel-ringkasan');
    if (!panel) return;

    const sesiDipilih = [...document.querySelectorAll('input[name="sesi[]"]:checked:not(:disabled)')];
    const bilikVal    = bilikSelect.value;
    const tarikhVal   = tarikhInput.value;

    if (!bilikVal || !tarikhVal || sesiDipilih.length === 0) {
        panel.classList.add('hidden');
        return;
    }

    // Nama bilik — buang sufiks "(X orang)" dari teks option
    const bilikOpt  = bilikSelect.options[bilikSelect.selectedIndex];
    const bilikNama = bilikOpt.text.replace(/\s*\(\d+\s*orang\)$/, '').trim();

    // Format tarikh: YYYY-MM-DD → DD/MM/YYYY
    const bahagian   = tarikhVal.split('-');
    const tarikhPapar = bahagian.length === 3
        ? bahagian[2] + '/' + bahagian[1] + '/' + bahagian[0]
        : tarikhVal;

    // Label sesi
    const sesiMap = {
        pagi:   'Sesi Pagi (9:00 — 13:00)',
        petang: 'Sesi Petang (14:00 — 18:00)',
    };
    const sesiTeks = sesiDipilih.map(cb => sesiMap[cb.value] || cb.value).join(' + ');

    // Kapasiti bilik
    const kapasiti = parseInt(bilikOpt.dataset.kapasiti, 10) || 0;

    document.getElementById('rs-bilik').textContent    = bilikNama;
    document.getElementById('rs-tarikh').textContent   = tarikhPapar;
    document.getElementById('rs-sesi').textContent     = sesiTeks;
    document.getElementById('rs-kapasiti').textContent = kapasiti ? kapasiti + ' orang' : '—';

    panel.classList.remove('hidden');
}

// ── Pastikan sekurang-kurangnya 1 sesi dipilih sebelum hantar ─────
const btnHantar = document.querySelector('#borang-tempahan button[type="submit"]');

document.querySelector('form').addEventListener('submit', function(e) {
    const dipilih = document.querySelectorAll('input[name="sesi[]"]:checked');
    if (dipilih.length === 0) {
        e.preventDefault();
        let ralat = document.getElementById('ralat-sesi');
        if (!ralat) {
            ralat = document.createElement('p');
            ralat.id = 'ralat-sesi';
            ralat.className = 'text-red-500 text-xs mt-1';
            ralat.setAttribute('role', 'alert');
            document.querySelector('fieldset').appendChild(ralat);
        }
        ralat.textContent = 'Sila pilih sekurang-kurangnya satu sesi mesyuarat.';
        document.querySelector('fieldset').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return; // jangan tunjuk loading state — form tak dihantar
    }

    // Form dihantar — tahan sebentar untuk repaint spinner, kemudian submit
    e.preventDefault();
    const form = this;
    if (btnHantar) {
        btnHantar.disabled = true;
        btnHantar.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i> Menghantar...';
        btnHantar.style.opacity = '0.75';
        btnHantar.style.cursor = 'not-allowed';
    }
    // 80ms: cukup untuk browser repaint spinner sebelum navigasi berlaku
    setTimeout(() => form.submit(), 80);
});

// ── Tempahan Berulang ──────────────────────────────────────────────
const urlNormal   = "{{ route('tempahan.store') }}";
const urlBerulang = "{{ route('tempahan-berulang.store') }}";
const urlPratonton = "{{ route('tempahan-berulang.pratonton') }}";

const toggleBerulang = document.getElementById('toggle-berulang');
const panelBerulang  = document.getElementById('panel-berulang');
const borang         = document.getElementById('borang-tempahan');
const selectJenis    = document.getElementById('jenis');
const inputSetiapN   = document.getElementById('setiap_n');
const inputTamat     = document.getElementById('tarikh_tamat');
const inputMulaBerulang = document.getElementById('tarikh_mula_berulang');
const panelHari      = document.getElementById('panel-hari');

function setBerulangFieldsDisabled(disabled) {
    panelBerulang.querySelectorAll('input, select').forEach(el => {
        el.disabled = disabled;
    });
}

function toggleJenis() {
    const isMingguan = selectJenis?.value === 'mingguan';
    if (panelHari) panelHari.classList.toggle('hidden', !isMingguan);
    if (document.getElementById('label-setiap-n')) {
        document.getElementById('label-setiap-n').textContent = isMingguan ? 'minggu' : 'bulan';
    }
}

toggleBerulang?.addEventListener('change', function() {
    const aktif = this.checked;
    panelBerulang.classList.toggle('hidden', !aktif);
    borang.action = aktif ? urlBerulang : urlNormal;
    setBerulangFieldsDisabled(!aktif);
    // Sync tarikh mula berulang dengan tarikh biasa
    if (aktif && tarikhInput.value) {
        inputMulaBerulang.value = tarikhInput.value;
    }
    // Init flatpickr untuk tarikh_tamat
    if (aktif && !inputTamat._flatpickr) {
        flatpickr(inputTamat, {
            locale: 'ms',
            dateFormat: 'Y-m-d',
            minDate: tarikhInput.value || 'today',
            disableMobile: true,
            allowInput: true,
            onChange: muatPratonton,
        });
    }
    if (!aktif) {
        document.getElementById('panel-pratonton').classList.add('hidden');
    }
});

selectJenis?.addEventListener('change', function() {
    toggleJenis();
    muatPratonton();
});

inputSetiapN?.addEventListener('input', muatPratonton);

// Sync tarikh mula berulang apabila tarikh biasa berubah
const _oriOnChange = tarikhInput._flatpickr?.config?.onChange;
// Tambah listener tambahan pada tarikh picker
tarikhInput.addEventListener('change', function() {
    if (toggleBerulang?.checked) {
        inputMulaBerulang.value = this.value;
        if (inputTamat._flatpickr) {
            inputTamat._flatpickr.set('minDate', this.value || 'today');
        }
        muatPratonton();
    }
});

document.querySelectorAll('.hari-cb').forEach(cb => {
    cb.addEventListener('change', muatPratonton);
});

// ── AJAX pratonton tarikh ──────────────────────────────────────────
let pratononTimeout;
function muatPratonton() {
    if (!toggleBerulang?.checked) return;
    clearTimeout(pratononTimeout);
    pratononTimeout = setTimeout(() => {
        const jenis  = selectJenis?.value;
        const setiap = inputSetiapN?.value;
        const mula   = tarikhInput?.value;
        const tamat  = inputTamat?.value;
        const hari   = [...document.querySelectorAll('.hari-cb:checked')].map(c => c.value);

        if (!jenis || !mula || !tamat) return;
        if (jenis === 'mingguan' && hari.length === 0) {
            document.getElementById('panel-pratonton').classList.add('hidden');
            return;
        }

        const params = new URLSearchParams({
            jenis,
            setiap_n: setiap,
            tarikh_mula: mula,
            tarikh_tamat: tamat,
        });
        if (jenis === 'mingguan') {
            hari.forEach(h => params.append('hari_dalam_minggu[]', h));
        }

        fetch(urlPratonton + '?' + params)
            .then(r => r.json())
            .then(data => {
                const panel = document.getElementById('panel-pratonton');
                const list  = document.getElementById('pratonton-senarai');
                const jumlah = document.getElementById('pratonton-jumlah');
                const hadEl  = document.getElementById('pratonton-had');

                list.innerHTML = data.tarikh.map(t => `<li>${t.label}</li>`).join('');
                jumlah.textContent = data.jumlah + ' tarikh';
                hadEl.classList.toggle('hidden', !data.tercapai_had);
                panel.classList.remove('hidden');
            })
            .catch(() => {});
    }, 400);
}

// ── Init: event listeners + semak kapasiti & ringkasan ───────────
document.addEventListener('DOMContentLoaded', function() {
    // Sesi checkbox — guna 'change' event (CSP-safe, tiada onclick di HTML)
    ['pagi', 'petang'].forEach(function(key) {
        const cb = document.getElementById('sesi-' + key);
        if (cb) cb.addEventListener('change', function() { toggleSesi(key); });
    });

    // Butang Sehari Penuh
    const btnSehariPenuh = document.getElementById('btn-sehari-penuh');
    if (btnSehariPenuh) btnSehariPenuh.addEventListener('click', pilihSehariPenuh);

    // Init kapasiti & ringkasan jika ada nilai old()
    const opt = bilikSelect.options[bilikSelect.selectedIndex];
    if (opt && opt.dataset.kapasiti) {
        kapasitiSemasa = parseInt(opt.dataset.kapasiti, 10) || 0;
    }
    if (bilikSelect.value && tarikhInput.value) {
        semakKonflik();
    }
    semakKapasiti();
    kemaskiniRingkasan();
});
</script>
@endpush
@endsection
