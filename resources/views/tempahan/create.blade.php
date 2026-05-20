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

    <form method="POST" action="{{ route('tempahan.store') }}" novalidate aria-label="Borang tempahan bilik mesyuarat baru">
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
                            value="{{ old('tarikh') }}"
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
                    @php $checked = is_array(old('sesi')) ? in_array($key, old('sesi')) : false; @endphp
                    <label class="flex items-center gap-3 p-3 border-2 rounded-lg cursor-pointer transition-all
                        {{ $checked ? 'border-amber-400 bg-amber-50' : 'border-gray-200 hover:border-amber-300' }}"
                        id="label-sesi-{{ $key }}"
                        onclick="toggleSesi('{{ $key }}')">
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
                        onclick="pilihSehariPenuh()"
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

    if (cb.disabled) {
        // Sesi telah ditempah — jangan beri interaksi
        return;
    }

    // checkbox state belum berubah semasa onclick pada label, so baca selepas tick
    setTimeout(() => {
        if (cb.checked) {
            label.classList.add('border-amber-400', 'bg-amber-50');
            label.classList.remove('border-gray-200');
        } else {
            label.classList.remove('border-amber-400', 'bg-amber-50');
            label.classList.add('border-gray-200');
        }
        kemaskiniRingkasan();
    }, 0);
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
    }
});

// ── Init: semak kapasiti & ringkasan jika ada nilai old() ─────────
document.addEventListener('DOMContentLoaded', function() {
    const opt = bilikSelect.options[bilikSelect.selectedIndex];
    if (opt && opt.dataset.kapasiti) {
        kapasitiSemasa = parseInt(opt.dataset.kapasiti, 10) || 0;
    }
    if (bilikSelect.value && tarikhInput.value) {
        semakKonflik();
    }
    semakKapasiti();
    kemaskiniRingkasan(); // Papar ringkasan jika old() sudah isi medan
});
</script>
@endpush
@endsection
