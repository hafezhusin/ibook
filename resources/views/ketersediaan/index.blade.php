@extends('layouts.app')

@section('title', 'Semak Ketersediaan Bilik')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ms.js"></script>
<style>
    .bilik-card {
        background: #fff;
        border-radius: 14px;
        border: 2px solid #e5e7eb;
        overflow: hidden;
        transition: box-shadow .2s, border-color .2s, transform .15s;
    }
    .bilik-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.1); transform: translateY(-2px); }
    .bilik-card.tersedia  { border-color: #16a34a; }
    .bilik-card.sebahagian { border-color: #f59e0b; }
    .bilik-card.ditempah  { border-color: #e5e7eb; opacity: .8; }
    .bilik-card.kapasiti-kurang { border-color: #e5e7eb; opacity: .7; }

    .sesi-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;
    }
    .sesi-badge.kosong  { background: #dcfce7; color: #15803d; }
    .sesi-badge.penuh   { background: #fee2e2; color: #b91c1c; }

    .kemudahan-tag {
        background: #f3f4f6; color: #374151; font-size: 11px;
        padding: 2px 8px; border-radius: 12px; font-weight: 500;
    }

    .status-bar {
        height: 5px; width: 100%;
    }

    #panel-carian {
        background: #1a1a2e;
        border-radius: 16px;
        padding: 28px 32px;
    }

    .result-count {
        font-size: 13px; color: #6b7280; font-weight: 500;
    }

    .empty-state {
        text-align: center; padding: 60px 20px; color: #9ca3af;
    }

    .loader {
        display: flex; align-items: center; justify-content: center;
        padding: 60px; color: #9ca3af; gap: 10px;
    }
</style>
@endpush

@section('content')

<div class="mb-5">
    <h1 class="text-2xl font-bold text-gray-800">Semak Ketersediaan Bilik</h1>
    <p class="text-gray-500 text-sm mt-1">Masukkan tarikh, sesi, dan bilangan peserta — terus tahu bilik mana yang kosong</p>
</div>

{{-- ══ Panel Carian ══════════════════════════════════════════════════ --}}
<div id="panel-carian" class="mb-7">
    <form id="form-semak" novalidate aria-label="Borang semak ketersediaan bilik">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">

            {{-- Tarikh --}}
            <div>
                <label for="cek-tarikh" class="form-label" style="color:#e5e7eb">
                    <i class="fa-solid fa-calendar mr-1 text-amber-400" aria-hidden="true"></i>
                    Tarikh
                </label>
                <div class="relative">
                    <input type="text" id="cek-tarikh" name="tarikh"
                        placeholder="Pilih tarikh..."
                        class="form-input pr-10"
                        readonly autocomplete="off"
                        aria-required="true">
                    <i class="fa-solid fa-calendar-days absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" aria-hidden="true"></i>
                </div>
                <p id="error-tarikh" class="form-error hidden" role="alert">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> Sila pilih tarikh
                </p>
            </div>

            {{-- Sesi --}}
            <div>
                <label for="cek-sesi" class="form-label" style="color:#e5e7eb">
                    <i class="fa-solid fa-clock mr-1 text-amber-400" aria-hidden="true"></i>
                    Sesi Mesyuarat
                </label>
                <select id="cek-sesi" name="sesi" class="form-input">
                    <option value="semua">Kedua-dua Sesi</option>
                    <option value="pagi">Pagi sahaja (9:00 AM – 1:00 PM)</option>
                    <option value="petang">Petang sahaja (2:00 PM – 6:00 PM)</option>
                </select>
            </div>

            {{-- Bilangan Peserta --}}
            <div>
                <label for="cek-peserta" class="form-label" style="color:#e5e7eb">
                    <i class="fa-solid fa-users mr-1 text-amber-400" aria-hidden="true"></i>
                    Bilangan Peserta
                </label>
                <input type="number" id="cek-peserta" name="peserta"
                    min="1" max="500" value="10"
                    class="form-input"
                    aria-describedby="hint-peserta">
                <p id="hint-peserta" class="form-hint" style="color:#9ca3af">Hanya bilik dengan kapasiti yang cukup ditunjukkan</p>
            </div>

            {{-- Butang --}}
            <div>
                <button type="submit" id="btn-semak" class="btn-primary w-full justify-center py-3" style="font-size:15px">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    Semak Sekarang
                </button>
            </div>
        </div>
    </form>
</div>

{{-- ══ Kawasan Keputusan ═════════════════════════════════════════════ --}}
<div id="kawasan-keputusan" aria-live="polite" aria-atomic="true">

    {{-- Keadaan awal --}}
    <div id="state-awal" class="empty-state">
        <i class="fa-solid fa-building-columns text-5xl mb-4 text-gray-200" aria-hidden="true"></i>
        <p class="text-lg font-semibold text-gray-400">Pilih tarikh dan sesi untuk semak ketersediaan</p>
        <p class="text-sm mt-2">Sistem akan tunjukkan semua bilik yang masih kosong pada tarikh tersebut</p>
    </div>

    {{-- Loader --}}
    <div id="state-loading" class="loader hidden" aria-label="Sedang menyemak...">
        <i class="fa-solid fa-spinner fa-spin text-3xl text-amber-400" aria-hidden="true"></i>
        <span class="text-gray-500 font-medium">Sedang menyemak ketersediaan...</span>
    </div>

    {{-- Keputusan --}}
    <div id="state-hasil" class="hidden">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-bold text-gray-800 text-lg" id="tajuk-hasil"></h2>
                <p class="result-count mt-0.5" id="keterangan-hasil"></p>
            </div>
            <div class="flex gap-2 text-xs font-semibold">
                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-100 text-green-700">
                    <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span> Tersedia
                </span>
                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-100 text-amber-700">
                    <span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span> Sebahagian
                </span>
                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">
                    <span class="w-2 h-2 rounded-full bg-gray-400 inline-block"></span> Penuh / Kapasiti Kurang
                </span>
            </div>
        </div>
        <div id="grid-bilik" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5"></div>
    </div>

    {{-- Tiada bilik --}}
    <div id="state-kosong" class="empty-state hidden">
        <i class="fa-solid fa-circle-xmark text-5xl mb-4 text-red-300" aria-hidden="true"></i>
        <p class="text-lg font-semibold text-gray-600">Tiada bilik tersedia</p>
        <p class="text-sm mt-2 text-gray-400">Semua bilik telah ditempah atau kapasiti tidak mencukupi pada tarikh ini</p>
        <a href="{{ route('kalendar') }}" class="btn-secondary mt-5 inline-flex">
            <i class="fa-solid fa-calendar-days" aria-hidden="true"></i> Lihat Kalendar
        </a>
    </div>

</div>

@endsection

@push('scripts')
<script>
// Init Flatpickr
flatpickr('#cek-tarikh', {
    locale: 'ms',
    dateFormat: 'Y-m-d',
    minDate: 'today',
    disableMobile: true,
    onChange: function() { autoSemak(); }
});

const SESI_LABEL = {
    pagi:   'Pagi (9:00 AM – 1:00 PM)',
    petang: 'Petang (2:00 PM – 6:00 PM)'
};

const form      = document.getElementById('form-semak');
const btnSemak  = document.getElementById('btn-semak');
const cekTarikh = document.getElementById('cek-tarikh');
const cekSesi   = document.getElementById('cek-sesi');
const cekPeserta= document.getElementById('cek-peserta');

// Auto semak bila sesi atau peserta berubah (jika tarikh sudah diisi)
cekSesi.addEventListener('change', autoSemak);
cekPeserta.addEventListener('change', autoSemak);

function autoSemak() {
    if (cekTarikh.value) semak();
}

function showState(state) {
    ['awal','loading','hasil','kosong'].forEach(s => {
        document.getElementById('state-' + s).classList.toggle('hidden', s !== state);
    });
}

function ikonKemudahan(item) {
    const map = {
        'Projektor':      'fa-film',
        'Papan Putih':    'fa-chalkboard',
        'Video Konferans':'fa-video',
        'PA System':      'fa-microphone',
        'WiFi':           'fa-wifi',
        'Air Conditioner':'fa-snowflake',
    };
    const lower = item.toLowerCase();
    for (const [k, v] of Object.entries(map)) {
        if (lower.includes(k.toLowerCase())) return v;
    }
    return 'fa-check';
}

async function semak() {
    const tarikh  = cekTarikh.value;
    const sesi    = cekSesi.value;
    const peserta = parseInt(cekPeserta.value) || 1;

    // Validate
    const errTarikh = document.getElementById('error-tarikh');
    if (!tarikh) {
        errTarikh.classList.remove('hidden');
        cekTarikh.focus();
        return;
    }
    errTarikh.classList.add('hidden');

    showState('loading');
    btnSemak.disabled = true;

    try {
        const url = `{{ route('ketersediaan.cek') }}?tarikh=${tarikh}&sesi=${sesi}&peserta=${peserta}`;
        const res  = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        const data = await res.json();

        renderHasil(data);
    } catch (e) {
        showState('awal');
        alert('Ralat: tidak dapat menyemak ketersediaan. Sila cuba lagi.');
    } finally {
        btnSemak.disabled = false;
    }
}

function renderHasil(data) {
    const grid    = document.getElementById('grid-bilik');
    const tajuk   = document.getElementById('tajuk-hasil');
    const keterangan = document.getElementById('keterangan-hasil');
    grid.innerHTML = '';

    const bilik  = data.bilik;
    const tarikh = data.tarikh;
    const sesi   = data.sesi;
    const peserta = data.peserta;

    // Format tarikh display
    const dt = new Date(tarikh + 'T00:00:00');
    const tarikhDisplay = dt.toLocaleDateString('ms-MY', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    const sesiDisplay = sesi === 'semua' ? 'Kedua-dua Sesi' : (sesi === 'pagi' ? 'Sesi Pagi' : 'Sesi Petang');

    tajuk.textContent = `Ketersediaan Bilik — ${tarikhDisplay}`;

    const tersedia  = bilik.filter(b => b.boleh_tempah).length;
    const jumlah    = bilik.length;
    keterangan.textContent = `${tersedia} daripada ${jumlah} bilik boleh ditempah • ${sesiDisplay} • ${peserta} peserta`;

    if (jumlah === 0 || tersedia === 0 && bilik.every(b => !b.ada_tersedia)) {
        showState('kosong');
        return;
    }

    bilik.forEach(b => {
        const sesiKeys = Object.keys(b.status_sesi);
        const kapasitiBadge = b.kapasiti_cukup
            ? `<span class="sesi-badge kosong"><i class="fa-solid fa-users" aria-hidden="true"></i> ${b.kapasiti} orang (cukup)</span>`
            : `<span class="sesi-badge penuh"><i class="fa-solid fa-users-slash" aria-hidden="true"></i> ${b.kapasiti} orang (kurang)</span>`;

        let sesiHTML = sesiKeys.map(s => {
            const kosong = b.status_sesi[s];
            return `<span class="sesi-badge ${kosong ? 'kosong' : 'penuh'}" aria-label="Sesi ${SESI_LABEL[s]}: ${kosong ? 'Kosong' : 'Telah Ditempah'}">
                <i class="fa-solid ${kosong ? 'fa-circle-check' : 'fa-circle-xmark'}" aria-hidden="true"></i>
                ${s.charAt(0).toUpperCase() + s.slice(1)}
            </span>`;
        }).join('');

        const kemudahanHTML = (b.kemudahan || []).slice(0, 5).map(k =>
            `<span class="kemudahan-tag" title="${k}">
                <i class="fa-solid ${ikonKemudahan(k)} mr-1 text-gray-400" aria-hidden="true"></i>${k}
            </span>`
        ).join('');

        // Determine card class & accent colour
        let cardClass, accentColor, statusLabel, statusIcon;
        if (b.boleh_tempah) {
            cardClass = 'tersedia'; accentColor = '#16a34a';
            statusLabel = 'Boleh Ditempah'; statusIcon = 'fa-circle-check';
        } else if (b.ada_tersedia && b.kapasiti_cukup) {
            cardClass = 'sebahagian'; accentColor = '#f59e0b';
            statusLabel = 'Sebahagian Tersedia'; statusIcon = 'fa-circle-half-stroke';
        } else if (!b.kapasiti_cukup) {
            cardClass = 'kapasiti-kurang'; accentColor = '#9ca3af';
            statusLabel = 'Kapasiti Tidak Cukup'; statusIcon = 'fa-users-slash';
        } else {
            cardClass = 'ditempah'; accentColor = '#ef4444';
            statusLabel = 'Telah Ditempah'; statusIcon = 'fa-lock';
        }

        // Build booking URL
        const sesiParam = sesiKeys.length === 1 ? `&sesi[]=${sesiKeys[0]}` : '';
        const urlTempah = `{{ route('tempahan.create') }}?bilik_id=${b.id}&tarikh=${tarikh}${sesiParam}`;

        const gambarHTML = b.gambar
            ? `<img src="/storage/${b.gambar}" alt="Gambar ${b.nama}" class="w-full h-36 object-cover">`
            : `<div class="w-full h-36 flex items-center justify-center" style="background:#f8fafc">
                <i class="fa-solid fa-building text-4xl text-gray-200" aria-hidden="true"></i>
               </div>`;

        const lokasi = b.lokasi
            ? `<p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                <i class="fa-solid fa-location-dot text-amber-400" aria-hidden="true"></i> ${b.lokasi}
               </p>` : '';

        const moreKemudahan = (b.kemudahan || []).length > 5
            ? `<span class="kemudahan-tag text-gray-400">+${(b.kemudahan||[]).length - 5} lagi</span>` : '';

        grid.insertAdjacentHTML('beforeend', `
        <article class="bilik-card ${cardClass}" aria-label="Bilik ${b.nama}: ${statusLabel}">
            <div class="status-bar" style="background:${accentColor}" aria-hidden="true"></div>
            ${gambarHTML}
            <div class="p-5">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <div>
                        <h3 class="font-bold text-gray-800 text-base leading-tight">${b.nama}</h3>
                        ${lokasi}
                    </div>
                    <span class="flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full flex-shrink-0"
                          style="background:${accentColor}20; color:${accentColor}">
                        <i class="fa-solid ${statusIcon}" aria-hidden="true"></i>
                        ${statusLabel}
                    </span>
                </div>

                {{-- Kapasiti --}}
                <div class="flex flex-wrap gap-2 mb-3">
                    ${kapasitiBadge}
                </div>

                {{-- Status Sesi --}}
                <div class="flex flex-wrap gap-1.5 mb-3">
                    ${sesiHTML}
                </div>

                {{-- Kemudahan --}}
                ${kemudahanHTML || moreKemudahan
                    ? `<div class="flex flex-wrap gap-1.5 mt-2 mb-4">${kemudahanHTML}${moreKemudahan}</div>`
                    : ''}

                {{-- Butang Tindakan --}}
                ${b.boleh_tempah
                    ? `<a href="${urlTempah}" class="btn-primary w-full justify-center mt-1">
                           <i class="fa-solid fa-circle-plus" aria-hidden="true"></i> Tempah Bilik Ini
                       </a>`
                    : b.ada_tersedia && b.kapasiti_cukup
                        ? `<div class="text-xs text-amber-600 font-semibold text-center mt-2 bg-amber-50 rounded-lg py-2">
                               <i class="fa-solid fa-info-circle mr-1"></i>
                               Tempah sesi yang masih kosong di atas
                           </div>`
                        : `<div class="text-xs text-gray-400 font-semibold text-center mt-2 bg-gray-50 rounded-lg py-2">
                               <i class="fa-solid fa-ban mr-1"></i>
                               Tidak tersedia untuk parameter ini
                           </div>`
                }
            </div>
        </article>`);
    });

    showState('hasil');
}

form.addEventListener('submit', function(e) {
    e.preventDefault();
    semak();
});

// Semak jika URL ada query string (dari dashboard)
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('tarikh')) {
    const fp = document.getElementById('cek-tarikh')._flatpickr;
    fp.setDate(urlParams.get('tarikh'));
    if (urlParams.has('sesi')) document.getElementById('cek-sesi').value = urlParams.get('sesi');
    if (urlParams.has('peserta')) document.getElementById('cek-peserta').value = urlParams.get('peserta');
    semak();
}
</script>
@endpush
