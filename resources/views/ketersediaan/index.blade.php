@extends('layouts.app')

@section('title', 'Semak Ketersediaan Bilik')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ms.js"></script>
<style>
    /* ── Bilik cards (mod Kad) ─────────────────────────────────── */
    .bilik-card {
        background: #fff; border-radius: 14px; border: 2px solid #e5e7eb;
        overflow: hidden; transition: box-shadow .2s, border-color .2s, transform .15s;
    }
    .bilik-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.1); transform: translateY(-2px); }
    .bilik-card.tersedia   { border-color: #16a34a; }
    .bilik-card.sebahagian { border-color: #f59e0b; }
    .bilik-card.ditempah   { border-color: #e5e7eb; opacity: .8; }
    .bilik-card.kapasiti-kurang { border-color: #e5e7eb; opacity: .7; }
    .sesi-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;
    }
    .sesi-badge.kosong { background: #dcfce7; color: #15803d; }
    .sesi-badge.penuh  { background: #fee2e2; color: #b91c1c; }
    .kemudahan-tag {
        background: #f3f4f6; color: #374151; font-size: 11px;
        padding: 2px 8px; border-radius: 12px; font-weight: 500;
    }
    .status-bar { height: 5px; width: 100%; }
    .empty-state { text-align: center; padding: 60px 20px; color: #9ca3af; }
    .loader {
        display: flex; align-items: center; justify-content: center;
        padding: 60px; color: #9ca3af; gap: 10px;
    }

    /* ── Panel carian ─────────────────────────────────────────── */
    #panel-carian { background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%); border-radius: 16px; padding: 28px 32px; }
    /* Paksa kekal gelap + teks cerah dalam light mode (specificity 1-2-2 > 0-2-1) */
    html.light body #panel-carian { background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%) !important; }
    html.light body #panel-carian .form-label { color: #e5e7eb !important; }
    html.light body #panel-carian .form-hint  { color: #9ca3af !important; }
    html.light body #panel-carian .text-gray-400 { color: #94a3b8 !important; }

    /* ── Mode tabs ────────────────────────────────────────────── */
    .mode-tab {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 18px; border-radius: 100px; font-size: 13px; font-weight: 600;
        border: 2px solid #e5e7eb; color: #6b7280; background: white;
        cursor: pointer; transition: all .15s;
    }
    .mode-tab:hover  { border-color: #f59e0b; color: #b45309; }
    .mode-tab.aktif  { background: #1a1a2e; color: #f59e0b; border-color: #1a1a2e; }

    /* ── Week navigation ──────────────────────────────────────── */
    .nav-btn-minggu {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 7px 14px; border-radius: 8px; font-size: 13px; font-weight: 600;
        border: 1.5px solid #374151; background: #1e293b; color: #cbd5e1;
        cursor: pointer; transition: all .15s;
    }
    .nav-btn-minggu:hover { background: #273548; border-color: #4b5563; color: #f1f5f9; }
    .nav-btn-minggu.ini   { background: #1c2d1a; border-color: #f59e0b; color: #f59e0b; }

    /* ── Week grid table ──────────────────────────────────────── */
    #tbl-minggu { border-collapse: collapse; min-width: 100%; }
    #tbl-minggu th, #tbl-minggu td { border: 1px solid #2d3748; padding: 0; vertical-align: middle; }
    .bilik-header {
        background: #0f172a; color: #64748b; font-size: 11px; font-weight: 700;
        text-align: left; padding: 8px 12px; min-width: 150px;
        position: sticky; left: 0; z-index: 2;
    }
    .bilik-subheader { background: #0f172a; position: sticky; left: 0; z-index: 2; }
    .hari-header { background: #1e293b; padding: 5px 4px; text-align: center; min-width: 72px; }
    .hari-nama   { font-size: 12px; font-weight: 700; color: #e2e8f0; }
    .hari-tarikh { font-size: 11px; color: #64748b; margin-top: 1px; }
    .hari-header.hari-ini { background: #1e3a5f; }
    .hari-header.hari-ini .hari-nama { color: #93c5fd; }
    .hari-header.hari-ini .hari-tarikh { color: #60a5fa; }
    .sesi-subheader {
        background: #162032; font-size: 10px; font-weight: 800;
        color: #475569; padding: 3px 0; text-align: center; width: 36px;
    }
    .bilik-nama-cell {
        padding: 8px 12px;
        background: #111827 !important;
        color: #f1f5f9 !important;
        position: sticky; left: 0; z-index: 1;
        min-width: 150px; max-width: 210px;
        border-right: 2px solid #2d3748;
    }
    .row-alt .bilik-nama-cell { background: #1a1a2e !important; color: #f1f5f9 !important; }
    .slot-cell { text-align: center; padding: 4px 2px; width: 36px; background: #111827; }
    .row-alt .slot-cell { background: #1a1a2e; }

    /* ── Slot chips ───────────────────────────────────────────── */
    .slot-chip {
        display: inline-flex; align-items: center; justify-content: center;
        width: 28px; height: 24px; border-radius: 5px; font-size: 12px; font-weight: 800;
        text-decoration: none; transition: all .12s;
    }
    .slot-chip.kosong { background: #14532d; color: #4ade80; cursor: pointer; border: 1px solid #166534; }
    .slot-chip.kosong:hover {
        background: #16a34a; color: #fff;
        transform: scale(1.1); box-shadow: 0 2px 8px rgba(22,163,74,.4);
    }
    .slot-chip.penuh  { background: #450a0a; color: #f87171; cursor: default; border: 1px solid #7f1d1d; }
    .slot-chip.tiada  { background: #1f2937; color: #4b5563; cursor: default; font-size: 14px; border: 1px solid #374151; }
    .slot-chip.sehari {
        background: #1e3a5f; color: #93c5fd; cursor: pointer;
        font-size: 10px; font-weight: 900; width: 22px;
        border: 1px solid #1d4ed8; letter-spacing: -0.5px;
    }
    .slot-chip.sehari:hover {
        background: #1d4ed8; color: #fff;
        transform: scale(1.1); box-shadow: 0 2px 8px rgba(29,78,216,.4);
    }
    .slot-chip.sehari-off { background: #111827; color: #2d3748; cursor: default; font-size: 14px; width: 22px; border: 1px solid #1f2937; }
    .sehari-subheader {
        background: #0f172a; font-size: 9px; font-weight: 800;
        color: #1e3a5f; padding: 3px 0; text-align: center; width: 26px;
        border-left: 1px dashed #1e3a5f !important;
    }
    .slot-cell.sehari-col { width: 26px; border-left: 1px dashed #1e3a5f !important; }

    .result-count { font-size: 13px; color: #6b7280; font-weight: 500; }
</style>
@endpush

@section('content')

<div class="mb-5">
    <h1 class="text-2xl font-bold text-gray-800">Semak Ketersediaan Bilik</h1>
    <p class="text-gray-500 text-sm mt-1">Semak slot kosong mengikut tarikh atau lihat jadual seminggu</p>
</div>

{{-- ══ Panel Carian ══════════════════════════════════════════════════ --}}
<div id="panel-carian" class="mb-5">
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

            {{-- Sesi (sembunyi di mod jadual) --}}
            <div id="wrap-sesi">
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
                    min="1" max="500" value="1"
                    class="form-input"
                    aria-describedby="hint-peserta">
                <p id="hint-peserta" class="form-hint" style="color:#9ca3af">Bilik kapasiti kurang diserlahkan</p>
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

{{-- ══ Mode Tabs ═════════════════════════════════════════════════════ --}}
<div class="flex items-center gap-2 mb-5">
    <button id="tab-kad" class="mode-tab aktif" aria-pressed="true">
        <i class="fa-solid fa-grip" aria-hidden="true"></i> Kad Bilik
    </button>
    <button id="tab-jadual" class="mode-tab" aria-pressed="false">
        <i class="fa-solid fa-table" aria-hidden="true"></i> Jadual Minggu
    </button>
</div>

{{-- ══ Mod Kad: Kawasan Keputusan ════════════════════════════════════ --}}
<div id="kawasan-keputusan" aria-live="polite" aria-atomic="true">

    {{-- Keadaan awal --}}
    <div id="state-awal" class="empty-state">
        <i class="fa-solid fa-building-columns text-5xl mb-4 text-gray-200" aria-hidden="true"></i>
        <p class="text-lg font-semibold text-gray-400">Pilih tarikh dan sesi untuk semak ketersediaan</p>
        <p class="text-sm mt-2">Atau tukar ke <strong>Jadual Minggu</strong> untuk lihat semua bilik sekaligus</p>
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
        <p class="text-sm mt-2 text-gray-400">Semua bilik telah ditempah atau kapasiti tidak mencukupi</p>
        <a href="{{ route('kalendar') }}" class="btn-secondary mt-5 inline-flex">
            <i class="fa-solid fa-calendar-days" aria-hidden="true"></i> Lihat Kalendar
        </a>
    </div>
</div>

{{-- ══ Mod Jadual Minggu ══════════════════════════════════════════════ --}}
<div id="kawasan-jadual" class="hidden">

    {{-- Week navigation bar --}}
    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
        <button id="btn-prev-minggu" class="nav-btn-minggu">
            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Sebelum
        </button>
        <div class="flex items-center gap-3">
            <span id="label-tarikh-minggu" class="font-semibold text-gray-700 text-sm"></span>
            <button id="btn-minggu-ini" class="nav-btn-minggu ini">
                <i class="fa-solid fa-calendar-day" aria-hidden="true"></i> Minggu Ini
            </button>
        </div>
        <button id="btn-next-minggu" class="nav-btn-minggu">
            Seterusnya <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </button>
    </div>

    {{-- Legend --}}
    <div class="flex gap-3 text-xs font-semibold mb-3 flex-wrap">
        <span class="flex items-center gap-1.5">
            <span class="slot-chip kosong" style="cursor:default">✓</span> Kosong — klik untuk tempah
        </span>
        <span class="flex items-center gap-1.5">
            <span class="slot-chip penuh">✗</span> Telah Ditempah
        </span>
        <span class="flex items-center gap-1.5">
            <span class="slot-chip tiada">—</span> Kapasiti Kurang
        </span>
        <span class="flex items-center gap-1.5">
            <span class="slot-chip sehari" style="cursor:default">S</span> Sehari Penuh (P+T)
        </span>
        <span class="text-gray-400 ml-auto">P = Pagi &nbsp;|&nbsp; T = Petang &nbsp;|&nbsp; S = Sehari Penuh</span>
    </div>

    {{-- Loader jadual --}}
    <div id="jadual-loading" class="loader hidden">
        <i class="fa-solid fa-spinner fa-spin text-3xl text-amber-400" aria-hidden="true"></i>
        <span class="text-gray-500 font-medium">Memuat jadual minggu...</span>
    </div>

    {{-- Grid table --}}
    <div id="jadual-grid" class="overflow-x-auto rounded-xl shadow-sm" style="border:1px solid #2d3748">
        <table id="tbl-minggu"></table>
    </div>

    <p class="text-xs text-gray-400 mt-3 flex items-center gap-1.5">
        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
        Bilangan peserta mempengaruhi penanda kapasiti. Klik slot hijau untuk buat tempahan.
    </p>
</div>

@endsection

@push('scripts')
<script nonce="{{ $cspNonce }}">
// ── Init Flatpickr ─────────────────────────────────────────────────
flatpickr('#cek-tarikh', {
    locale: 'ms',
    dateFormat: 'Y-m-d',
    minDate: 'today',
    disableMobile: true,
    onChange: function(selectedDates, dateStr) {
        autoSemak(dateStr);
    }
});

// ── Shared state ───────────────────────────────────────────────────
const SESI_LABEL = { pagi: 'Pagi (9:00 AM – 1:00 PM)', petang: 'Petang (2:00 PM – 6:00 PM)' };
const HARI_NAMA  = ['Ahad','Isnin','Selasa','Rabu','Khamis','Jumaat','Sabtu'];

const form       = document.getElementById('form-semak');
const btnSemak   = document.getElementById('btn-semak');
const cekTarikh  = document.getElementById('cek-tarikh');
const cekSesi    = document.getElementById('cek-sesi');
const cekPeserta = document.getElementById('cek-peserta');

let modeSemasa       = 'kad';
let tarikhMulaMinggu = null; // Isnin bagi minggu semasa dalam jadual

// ── Mode toggle ────────────────────────────────────────────────────
function setMode(mode) {
    modeSemasa = mode;
    const isJadual = mode === 'jadual';

    document.getElementById('kawasan-keputusan').classList.toggle('hidden',  isJadual);
    document.getElementById('kawasan-jadual').classList.toggle('hidden', !isJadual);
    document.getElementById('wrap-sesi').classList.toggle('hidden', isJadual);

    document.getElementById('tab-kad').classList.toggle('aktif', !isJadual);
    document.getElementById('tab-jadual').classList.toggle('aktif', isJadual);
    document.getElementById('tab-kad').setAttribute('aria-pressed', String(!isJadual));
    document.getElementById('tab-jadual').setAttribute('aria-pressed', String(isJadual));

    if (isJadual) {
        muatMinggu(tarikhMulaMinggu || cekTarikh.value || null);
    }
}

document.getElementById('tab-kad').addEventListener('click',    () => setMode('kad'));
document.getElementById('tab-jadual').addEventListener('click', () => setMode('jadual'));

// ── Auto semak ─────────────────────────────────────────────────────
cekSesi.addEventListener('change', () => { if (cekTarikh.value) semak(); });
cekPeserta.addEventListener('change', () => {
    if (modeSemasa === 'jadual' && tarikhMulaMinggu) muatMinggu(tarikhMulaMinggu);
    else if (cekTarikh.value) semak();
});

function autoSemak(tarikh) {
    if (modeSemasa === 'jadual') muatMinggu(tarikh || null);
    else if (tarikh) semak();
}

// ══ MOD KAD ════════════════════════════════════════════════════════

function showState(state) {
    ['awal','loading','hasil','kosong'].forEach(s => {
        document.getElementById('state-' + s).classList.toggle('hidden', s !== state);
    });
}

function ikonKemudahan(item) {
    const map = {
        'Projektor':'fa-film','Papan Putih':'fa-chalkboard',
        'Video Konferans':'fa-video','PA System':'fa-microphone',
        'WiFi':'fa-wifi','Air Conditioner':'fa-snowflake',
    };
    const lower = item.toLowerCase();
    for (const [k,v] of Object.entries(map)) {
        if (lower.includes(k.toLowerCase())) return v;
    }
    return 'fa-check';
}

async function semak() {
    const tarikh  = cekTarikh.value;
    const sesi    = cekSesi.value;
    const peserta = parseInt(cekPeserta.value) || 1;

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
        const url  = `{{ route('ketersediaan.cek') }}?tarikh=${tarikh}&sesi=${sesi}&peserta=${peserta}`;
        const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
        renderHasil(await res.json());
    } catch {
        showState('awal');
        alert('Ralat: tidak dapat menyemak ketersediaan. Sila cuba lagi.');
    } finally {
        btnSemak.disabled = false;
    }
}

function renderHasil(data) {
    const grid   = document.getElementById('grid-bilik');
    const tajuk  = document.getElementById('tajuk-hasil');
    const keterangan = document.getElementById('keterangan-hasil');
    grid.innerHTML = '';

    const { bilik, tarikh, sesi, peserta } = data;
    const dt = new Date(tarikh + 'T00:00:00');
    const tarikhDisplay  = dt.toLocaleDateString('ms-MY', { weekday:'long', day:'numeric', month:'long', year:'numeric' });
    const sesiDisplay    = sesi === 'semua' ? 'Kedua-dua Sesi' : (sesi === 'pagi' ? 'Sesi Pagi' : 'Sesi Petang');

    tajuk.textContent = `Ketersediaan Bilik — ${tarikhDisplay}`;
    const tersedia = bilik.filter(b => b.boleh_tempah).length;
    keterangan.textContent = `${tersedia} daripada ${bilik.length} bilik boleh ditempah • ${sesiDisplay} • ${peserta} peserta`;

    if (bilik.length === 0 || (tersedia === 0 && bilik.every(b => !b.ada_tersedia))) {
        showState('kosong'); return;
    }

    bilik.forEach(b => {
        const sesiKeys = Object.keys(b.status_sesi);
        const kapasitiBadge = b.kapasiti_cukup
            ? `<span class="sesi-badge kosong"><i class="fa-solid fa-users" aria-hidden="true"></i> ${b.kapasiti} orang (cukup)</span>`
            : `<span class="sesi-badge penuh"><i class="fa-solid fa-users-slash" aria-hidden="true"></i> ${b.kapasiti} orang (kurang)</span>`;

        const sesiHTML = sesiKeys.map(s => {
            const ok = b.status_sesi[s];
            return `<span class="sesi-badge ${ok ? 'kosong':'penuh'}" aria-label="Sesi ${SESI_LABEL[s]}: ${ok?'Kosong':'Telah Ditempah'}">
                <i class="fa-solid ${ok?'fa-circle-check':'fa-circle-xmark'}" aria-hidden="true"></i>
                ${s.charAt(0).toUpperCase()+s.slice(1)}</span>`;
        }).join('');

        const kemudahanHTML = (b.kemudahan || []).slice(0,5).map(k =>
            `<span class="kemudahan-tag" title="${k}"><i class="fa-solid ${ikonKemudahan(k)} mr-1 text-gray-400" aria-hidden="true"></i>${k}</span>`
        ).join('');

        let cardClass, accentColor, statusLabel, statusIcon;
        if (b.boleh_tempah)                            { cardClass='tersedia';       accentColor='#16a34a'; statusLabel='Boleh Ditempah';      statusIcon='fa-circle-check'; }
        else if (b.ada_tersedia && b.kapasiti_cukup)   { cardClass='sebahagian';     accentColor='#f59e0b'; statusLabel='Sebahagian Tersedia';  statusIcon='fa-circle-half-stroke'; }
        else if (!b.kapasiti_cukup)                    { cardClass='kapasiti-kurang';accentColor='#9ca3af'; statusLabel='Kapasiti Tidak Cukup'; statusIcon='fa-users-slash'; }
        else                                           { cardClass='ditempah';       accentColor='#ef4444'; statusLabel='Telah Ditempah';       statusIcon='fa-lock'; }

        const sesiParam  = sesiKeys.length === 1 ? `&sesi[]=${sesiKeys[0]}` : '';
        const urlTempah  = `{{ route('tempahan.create') }}?bilik_id=${b.id}&tarikh=${tarikh}${sesiParam}`;
        const gambarHTML = b.gambar
            ? `<img src="/storage/${b.gambar}" alt="Gambar ${b.nama}" class="w-full h-36 object-cover">`
            : `<div class="w-full h-36 flex items-center justify-center" style="background:#f8fafc"><i class="fa-solid fa-building text-4xl text-gray-200" aria-hidden="true"></i></div>`;
        const lokasi = b.lokasi
            ? `<p class="text-xs text-gray-400 mt-1 flex items-center gap-1"><i class="fa-solid fa-location-dot text-amber-400" aria-hidden="true"></i> ${b.lokasi}</p>` : '';
        const moreKemudahan = (b.kemudahan||[]).length > 5
            ? `<span class="kemudahan-tag text-gray-400">+${(b.kemudahan||[]).length-5} lagi</span>` : '';

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
                          style="background:${accentColor}20;color:${accentColor}">
                        <i class="fa-solid ${statusIcon}" aria-hidden="true"></i> ${statusLabel}
                    </span>
                </div>
                <div class="flex flex-wrap gap-2 mb-3">${kapasitiBadge}</div>
                <div class="flex flex-wrap gap-1.5 mb-3">${sesiHTML}</div>
                ${kemudahanHTML||moreKemudahan ? `<div class="flex flex-wrap gap-1.5 mt-2 mb-4">${kemudahanHTML}${moreKemudahan}</div>` : ''}
                ${b.boleh_tempah
                    ? `<a href="${urlTempah}" class="btn-primary w-full justify-center mt-1"><i class="fa-solid fa-circle-plus" aria-hidden="true"></i> Tempah Bilik Ini</a>`
                    : b.ada_tersedia && b.kapasiti_cukup
                        ? `<div class="text-xs text-amber-600 font-semibold text-center mt-2 bg-amber-50 rounded-lg py-2"><i class="fa-solid fa-info-circle mr-1"></i>Tempah sesi yang masih kosong</div>`
                        : `<div class="text-xs text-gray-400 font-semibold text-center mt-2 bg-gray-50 rounded-lg py-2"><i class="fa-solid fa-ban mr-1"></i>Tidak tersedia untuk parameter ini</div>`}
            </div>
        </article>`);
    });
    showState('hasil');
}

// ══ MOD JADUAL MINGGU ══════════════════════════════════════════════

async function muatMinggu(tarikhRef) {
    const peserta = parseInt(cekPeserta.value) || 1;
    document.getElementById('jadual-loading').classList.remove('hidden');
    document.getElementById('jadual-grid').style.opacity = '0.4';

    try {
        const params = new URLSearchParams({ peserta });
        if (tarikhRef) params.set('tarikh_mula', tarikhRef);
        const res  = await fetch(`{{ route('ketersediaan.minggu') }}?` + params, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        const data = await res.json();
        tarikhMulaMinggu = data.tarikh_mula;
        renderJadual(data);
    } catch (e) {
        console.error('muatMinggu error:', e);
    } finally {
        document.getElementById('jadual-loading').classList.add('hidden');
        document.getElementById('jadual-grid').style.opacity = '1';
    }
}

function renderJadual(data) {
    const { hari, bilik } = data;
    const hariToday = new Date().toISOString().split('T')[0];

    // Tapis hanya hari bekerja (Isnin–Jumaat), buang Sabtu & Ahad
    const hariBekerja = hari.filter(t => {
        const dow = new Date(t + 'T00:00:00').getDay();
        return dow !== 0 && dow !== 6; // 0=Ahad, 6=Sabtu
    });

    // Label header minggu: "26 Mei – 30 Mei 2026"
    if (hariBekerja.length >= 2) {
        const d1 = new Date(hariBekerja[0] + 'T00:00:00');
        const dN = new Date(hariBekerja[hariBekerja.length - 1] + 'T00:00:00');
        document.getElementById('label-tarikh-minggu').textContent =
            d1.toLocaleDateString('ms-MY', { day:'numeric', month:'long' }) +
            ' – ' + dN.toLocaleDateString('ms-MY', { day:'numeric', month:'long', year:'numeric' });
    }

    const tbl = document.getElementById('tbl-minggu');

    // ── thead ──
    let thead = '<thead><tr>';
    thead += '<th class="bilik-header" rowspan="2">Bilik</th>';
    hariBekerja.forEach(t => {
        const dt  = new Date(t + 'T00:00:00');
        const dow = dt.getDay();
        const isHariIni = t === hariToday;
        const cls = isHariIni ? ' hari-ini' : '';
        thead += `<th class="hari-header${cls}" colspan="3">
            <div class="hari-nama">${HARI_NAMA[dow]}</div>
            <div class="hari-tarikh">${dt.getDate()}/${dt.getMonth()+1}${isHariIni ? ' <span style="color:#60a5fa;font-size:9px">●</span>' : ''}</div>
        </th>`;
    });
    thead += '</tr><tr>';
    hariBekerja.forEach(() => {
        thead += `<th class="sesi-subheader">P</th><th class="sesi-subheader">T</th><th class="sehari-subheader">S</th>`;
    });
    thead += '</tr></thead>';

    // ── tbody ──
    let tbody = '<tbody>';
    bilik.forEach((b, idx) => {
        const rowCls = idx % 2 === 1 ? 'row-alt' : '';
        tbody += `<tr class="${rowCls}">`;
        tbody += `<td class="bilik-nama-cell">
            <div style="font-weight:600;font-size:13px;line-height:1.3;color:#f1f5f9">${b.nama}</div>
            <div style="font-size:11px;margin-top:3px;color:${b.kapasiti_cukup ? '#94a3b8' : '#fbbf24'}">
                ${b.kapasiti} org${!b.kapasiti_cukup ? ' &#9888;' : ''}
            </div>
        </td>`;

        hariBekerja.forEach(tarikh => {
            const slot    = (b.slot && b.slot[tarikh]) || { pagi: false, petang: false };
            const urlBase = `/tempahan/baru?bilik_id=${b.id}&tarikh=${tarikh}`;
            const pagiOk  = slot['pagi'];
            const petangOk = slot['petang'];

            // P chip
            ['pagi', 'petang'].forEach(sesi => {
                const kosong = slot[sesi];
                let chip;
                if (!b.kapasiti_cukup) {
                    chip = `<span class="slot-chip tiada" title="Kapasiti bilik (${b.kapasiti} org) tidak mencukupi">—</span>`;
                } else if (kosong) {
                    chip = `<a href="${urlBase}&sesi[]=${sesi}" class="slot-chip kosong" title="Tempah sesi ${sesi === 'pagi' ? 'Pagi (9–1)' : 'Petang (2–6)'} pada ${tarikh}">✓</a>`;
                } else {
                    chip = `<span class="slot-chip penuh" title="Telah ditempah">✗</span>`;
                }
                tbody += `<td class="slot-cell">${chip}</td>`;
            });

            // S chip — Sehari Penuh (P+T)
            let sehariChip;
            if (!b.kapasiti_cukup) {
                sehariChip = `<span class="slot-chip sehari-off" title="Kapasiti kurang">—</span>`;
            } else if (pagiOk && petangOk) {
                sehariChip = `<a href="${urlBase}&sesi[]=pagi&sesi[]=petang" class="slot-chip sehari" title="Tempah Sehari Penuh (Pagi + Petang) pada ${tarikh}">S</a>`;
            } else {
                sehariChip = `<span class="slot-chip sehari-off" title="${!pagiOk && !petangOk ? 'Kedua-dua sesi telah ditempah' : 'Satu sesi telah ditempah'}">—</span>`;
            }
            tbody += `<td class="slot-cell sehari-col">${sehariChip}</td>`;
        });

        tbody += '</tr>';
    });

    if (bilik.length === 0) {
        tbody += `<tr><td colspan="${1 + hariBekerja.length * 3}" style="padding:40px;text-align:center;color:#9ca3af">Tiada bilik aktif dalam sistem.</td></tr>`;
    }
    tbody += '</tbody>';

    tbl.innerHTML = thead + tbody;
}

// ── Week navigation ────────────────────────────────────────────────
function navigasiMinggu(arah) {
    if (!tarikhMulaMinggu) { muatMinggu(null); return; }
    const d = new Date(tarikhMulaMinggu + 'T00:00:00');
    d.setDate(d.getDate() + arah * 7);
    tarikhMulaMinggu = d.toISOString().split('T')[0];
    muatMinggu(tarikhMulaMinggu);
}

document.getElementById('btn-prev-minggu').addEventListener('click', () => navigasiMinggu(-1));
document.getElementById('btn-next-minggu').addEventListener('click', () => navigasiMinggu(1));
document.getElementById('btn-minggu-ini').addEventListener('click', () => { tarikhMulaMinggu = null; muatMinggu(null); });

// ── Form submit (mod Kad) ──────────────────────────────────────────
form.addEventListener('submit', function(e) {
    e.preventDefault();
    if (modeSemasa === 'jadual') muatMinggu(cekTarikh.value || null);
    else semak();
});

// ── URL query string — init dari dashboard / link luar ────────────
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('mode') && urlParams.get('mode') === 'jadual') {
    setMode('jadual');
} else if (urlParams.has('tarikh')) {
    const fp = document.getElementById('cek-tarikh')._flatpickr;
    fp.setDate(urlParams.get('tarikh'));
    if (urlParams.has('sesi'))    cekSesi.value    = urlParams.get('sesi');
    if (urlParams.has('peserta')) cekPeserta.value = urlParams.get('peserta');
    semak();
}
</script>
@endpush
