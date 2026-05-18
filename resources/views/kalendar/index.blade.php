@extends('layouts.app')

@section('title', 'Kalendar')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<style>
    .bilik-btn {
        display: block; width: 100%; text-align: left;
        padding: 10px 12px; border-radius: 8px; border: 1.5px solid #e5e7eb;
        background: white; cursor: pointer; transition: all .15s;
        font-size: 13px; color: #374151;
    }
    .bilik-btn:hover { background: #fef3c7; border-color: #f59e0b; }
    .bilik-btn.aktif  { background: #fef3c7; border-color: #f59e0b; }
    .bilik-btn:focus-visible { outline: 3px solid #f59e0b; outline-offset: 2px; }
</style>
@endpush

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Kalendar</h1>
        <p class="text-gray-500 text-sm mt-1">Paparan tempahan mengikut bulan</p>
    </div>
    <a href="{{ route('tempahan.create') }}" class="btn-primary">
        <i class="fa-solid fa-plus" aria-hidden="true"></i> Tempahan Baru
    </a>
</div>

<div class="flex gap-4" style="min-height:75vh">

    {{-- ===== SIDEBAR BILIK ===== --}}
    <aside class="w-52 flex-shrink-0 flex flex-col gap-3" aria-label="Tapis bilik mesyuarat">

        {{-- Senarai bilik --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden flex flex-col flex-1">
            <div class="px-4 pt-4 pb-2 border-b border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider" id="label-tapis-bilik">
                    <i class="fa-solid fa-door-open text-amber-400 mr-1" aria-hidden="true"></i>
                    Tapis Bilik
                </p>
            </div>
            <div class="p-3 space-y-1.5 overflow-y-auto flex-1" role="list" aria-labelledby="label-tapis-bilik">

                {{-- Semua Bilik --}}
                <div role="listitem">
                    <button type="button"
                        class="bilik-btn aktif"
                        onclick="filterBilik(null, this)"
                        aria-pressed="true"
                        id="btn-semua">
                        <div class="font-semibold text-gray-800 text-sm">Semua Bilik</div>
                        <div class="text-xs text-gray-400 mt-0.5">Papar semua tempahan</div>
                    </button>
                </div>

                {{-- Senarai bilik dari DB --}}
                @foreach($bilik as $b)
                <div role="listitem">
                    <button type="button"
                        class="bilik-btn"
                        onclick="filterBilik({{ $b->id }}, this)"
                        aria-pressed="false"
                        data-bilik-id="{{ $b->id }}">
                        <div class="font-semibold text-gray-800 text-sm leading-snug">{{ $b->nama }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">
                            <i class="fa-solid fa-users text-amber-400 mr-1" aria-hidden="true"></i>
                            {{ $b->kapasiti }} orang
                            @if($b->lokasi)
                            &middot; {{ $b->lokasi }}
                            @endif
                        </div>
                    </button>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Status hari ini --}}
        <div class="bg-white rounded-xl shadow-sm p-4 flex-shrink-0">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">
                <i class="fa-solid fa-circle-dot text-amber-400 mr-1" aria-hidden="true"></i>
                Status Hari Ini
            </p>
            <div id="status-hari-ini" class="space-y-2 text-xs" aria-live="polite">
                <div class="flex items-center gap-2 text-gray-400">
                    <i class="fa-solid fa-spinner fa-spin text-amber-400" aria-hidden="true"></i>
                    <span>Memuatkan...</span>
                </div>
            </div>
        </div>

    </aside>

    {{-- ===== PANEL UTAMA KALENDAR ===== --}}
    <div class="flex-1 bg-white rounded-xl shadow-sm p-5 flex flex-col">

        {{-- Legend --}}
        <div class="flex flex-wrap items-center gap-3 mb-4 pb-3 border-b border-gray-100 flex-shrink-0" aria-label="Legenda warna kalendar">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mr-1">Legenda:</span>

            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                  style="background:#dcfce7; color:#166534"
                  title="Tempahan yang anda buat sendiri dan telah diluluskan">
                <span class="inline-block w-2.5 h-2.5 rounded-sm" style="background:#16a34a" aria-hidden="true"></span>
                Tempahan Saya
            </span>

            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                  style="background:#dbeafe; color:#1e40af"
                  title="Tempahan pengguna lain yang telah diluluskan">
                <span class="inline-block w-2.5 h-2.5 rounded-sm" style="background:#2563eb" aria-hidden="true"></span>
                Tempahan Lain
            </span>

            <span class="ml-auto text-xs text-gray-400 italic flex items-center gap-1">
                <i class="fa-solid fa-hand-pointer" aria-hidden="true"></i>
                Klik acara untuk butiran &amp; tindakan
            </span>
        </div>

        <div id="calendar" class="flex-1"
            role="application"
            aria-label="Kalendar tempahan bilik mesyuarat">
        </div>
    </div>
</div>

{{-- ===== MODAL BUTIRAN ACARA ===== --}}
<div id="event-modal"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    role="dialog"
    aria-modal="true"
    aria-labelledby="ev-modal-heading">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 overflow-hidden">

        {{-- Header --}}
        <div id="ev-header" class="px-6 pt-5 pb-4" style="background:#1a1a2e">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider mb-1" style="color:#f59e0b">
                        <i class="fa-solid fa-calendar-check mr-1" aria-hidden="true"></i>
                        <span id="ev-kategori"></span>
                    </p>
                    <h2 id="ev-modal-heading" class="font-bold text-white text-base leading-snug break-words"></h2>
                </div>
                <button type="button"
                    onclick="document.getElementById('event-modal').classList.add('hidden')"
                    class="flex-shrink-0 text-gray-400 hover:text-white mt-0.5"
                    aria-label="Tutup">
                    <i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i>
                </button>
            </div>
            <div class="mt-3">
                <span id="ev-status" role="status" class="text-xs font-bold px-2 py-1 rounded-full"></span>
            </div>
        </div>

        {{-- Body --}}
        <dl class="px-6 py-4 space-y-3 text-sm">
            <div class="flex items-start gap-3">
                <dt class="flex-shrink-0 w-5 mt-0.5">
                    <i class="fa-solid fa-door-open text-amber-400" aria-hidden="true"></i>
                    <span class="sr-only">Bilik:</span>
                </dt>
                <dd>
                    <span class="font-semibold text-gray-800" id="ev-bilik"></span>
                    <span class="text-gray-400 text-xs ml-1" id="ev-lokasi"></span>
                </dd>
            </div>
            <div class="flex items-center gap-3">
                <dt class="flex-shrink-0 w-5">
                    <i class="fa-solid fa-clock text-amber-400" aria-hidden="true"></i>
                    <span class="sr-only">Masa:</span>
                </dt>
                <dd class="text-gray-700" id="ev-masa"></dd>
            </div>
            <div class="flex items-center gap-3">
                <dt class="flex-shrink-0 w-5">
                    <i class="fa-solid fa-user-tie text-amber-400" aria-hidden="true"></i>
                    <span class="sr-only">Pengerusi:</span>
                </dt>
                <dd class="text-gray-700">
                    Pengerusi: <span class="font-semibold" id="ev-pengerusi"></span>
                </dd>
            </div>
            <div class="flex items-center gap-3">
                <dt class="flex-shrink-0 w-5">
                    <i class="fa-solid fa-users text-amber-400" aria-hidden="true"></i>
                    <span class="sr-only">Peserta:</span>
                </dt>
                <dd class="text-gray-700"><span id="ev-peserta"></span> peserta</dd>
            </div>
            <div class="flex items-center gap-3">
                <dt class="flex-shrink-0 w-5">
                    <i class="fa-solid fa-person text-amber-400" aria-hidden="true"></i>
                    <span class="sr-only">Pemohon:</span>
                </dt>
                <dd class="text-gray-700">Pemohon: <span id="ev-pemohon"></span></dd>
            </div>
        </dl>

        {{-- Footer: Tindakan --}}
        <div class="px-6 pb-5 space-y-2">
            {{-- Butang utama --}}
            <div class="flex gap-2">
                <a id="ev-btn-butiran" href="#"
                   class="btn-primary flex-1 justify-center text-sm"
                   aria-label="Lihat butiran penuh tempahan ini">
                    <i class="fa-solid fa-eye" aria-hidden="true"></i> Lihat Butiran
                </a>
                <a id="ev-btn-duplikat" href="{{ route('tempahan.create') }}"
                   class="btn-secondary flex-1 justify-center text-sm"
                   aria-label="Duplikat — buat tempahan baru berdasarkan acara ini">
                    <i class="fa-solid fa-copy" aria-hidden="true"></i> Duplikat
                </a>
            </div>
            {{-- Tutup --}}
            <button type="button"
                onclick="document.getElementById('event-modal').classList.add('hidden')"
                class="w-full text-center text-sm text-gray-400 hover:text-gray-600 py-1"
                aria-label="Tutup butiran acara">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales/ms.global.min.js"></script>
<script>
let calendar;
let selectedBilikId = null;
const totalBilik = {{ $bilik->count() }};

// ---- Init FullCalendar ----
document.addEventListener('DOMContentLoaded', function () {
    calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        locale: 'ms',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: { today:'Hari Ini', month:'Bulan', week:'Minggu', day:'Hari' },
        height: 'auto',
        dayMaxEvents: 4,
        events: fetchEvents,
        eventsSet: function (events) {
            updateStatusHariIniFromCurrentEvents(events);
        },

        eventClick: function (info) {
            const p     = info.event.extendedProps;
            const start = info.event.start;
            const end   = info.event.end;

            document.getElementById('ev-modal-heading').textContent = info.event.title;

            const kategoriMap = {
                'pengurusan':'Pengurusan','teknikal':'Teknikal',
                'latihan':'Latihan / Bengkel','perbincangan':'Perbincangan',
                'taklimat':'Taklimat','lain-lain':'Lain-lain','mesyuarat':'Mesyuarat'
            };
            document.getElementById('ev-kategori').textContent = kategoriMap[p.kategori] || p.kategori || 'Mesyuarat';
            document.getElementById('ev-bilik').textContent   = p.bilik;
            document.getElementById('ev-lokasi').textContent  = p.lokasi ? '(' + p.lokasi + ')' : '';

            const fmt = t => t ? t.toLocaleTimeString('ms-MY',{hour:'2-digit',minute:'2-digit'}) : '';
            document.getElementById('ev-masa').textContent =
                p.sesi + '  ·  ' + fmt(start) + ' – ' + fmt(end);

            document.getElementById('ev-pengerusi').textContent = p.nama_pengerusi || '-';
            document.getElementById('ev-peserta').textContent   = p.peserta || '0';
            document.getElementById('ev-pemohon').textContent   = p.pemohon || '-';

            const statusEl = document.getElementById('ev-status');
            if (p.status === 'diluluskan') {
                statusEl.style.cssText = 'background:#dcfce7;color:#166534';
                statusEl.textContent   = '✓ Diluluskan';
            } else {
                statusEl.style.cssText = 'background:#fef3c7;color:#92400e';
                statusEl.textContent   = '⏳ Menunggu Kelulusan';
            }

            document.getElementById('ev-header').style.background = p.is_own ? '#14532d' : '#1a1a2e';

            // ── Butang tindakan ─────────────────────────────────────
            // Lihat Butiran → /tempahan/{id}
            const btnButiran = document.getElementById('ev-btn-butiran');
            if (p.tempahan_id) {
                btnButiran.href = '{{ url("/tempahan") }}/' + p.tempahan_id;
                btnButiran.style.display = '';
            } else {
                btnButiran.style.display = 'none';
            }

            // Duplikat → /tempahan/baru?duplikat_id=X
            const btnDuplikat = document.getElementById('ev-btn-duplikat');
            if (p.tempahan_id) {
                btnDuplikat.href = '{{ route("tempahan.create") }}?duplikat_id=' + p.tempahan_id;
            }

            document.getElementById('event-modal').classList.remove('hidden');
            setTimeout(() => document.getElementById('ev-btn-butiran').focus(), 50);
        },

        eventDidMount: function (info) {
            info.el.style.borderRadius = '4px';
            info.el.style.padding = '2px 4px';
            info.el.setAttribute('aria-label',
                info.event.title + (info.event.extendedProps.bilik ? ', ' + info.event.extendedProps.bilik : ''));
        }
    });
    calendar.render();
});

// ---- Fetch events dengan filter bilik ----
function fetchEvents(info, successCallback, failureCallback) {
    const params = new URLSearchParams({ start: info.startStr, end: info.endStr });
    if (selectedBilikId) params.append('bilik_id', selectedBilikId);
    fetch('{{ route("kalendar.events") }}?' + params)
        .then(r => r.json())
        .then(successCallback)
        .catch(failureCallback);
}

// ---- Filter bilik ----
function filterBilik(bilikId, el) {
    selectedBilikId = bilikId;
    document.querySelectorAll('.bilik-btn').forEach(b => {
        b.classList.remove('aktif');
        b.setAttribute('aria-pressed', 'false');
    });
    el.classList.add('aktif');
    el.setAttribute('aria-pressed', 'true');
    if (calendar) calendar.refetchEvents();
}

// ---- Status hari ini (guna event semasa, tanpa fetch tambahan) ----
function updateStatusHariIniFromCurrentEvents(events) {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const todayStr = `${yyyy}-${mm}-${dd}`;

    const eventHariIni = events.filter(e => {
        const tarikh = (e.extendedProps && e.extendedProps.tarikh) ? e.extendedProps.tarikh : null;
        return tarikh === todayStr;
    });

    const ditempah = eventHariIni.filter(e => {
        const props = e.extendedProps || {};
        return props.status === 'diluluskan';
    });

    const bilikDitempah = [...new Set(ditempah.map(e => e.extendedProps?.bilik))].filter(Boolean);
    const gunaBilikSpesifik = !!selectedBilikId;
    const jmlDitempah = gunaBilikSpesifik ? (ditempah.length > 0 ? 1 : 0) : bilikDitempah.length;
    const jmlTersedia = gunaBilikSpesifik ? (ditempah.length > 0 ? 0 : 1) : Math.max(0, totalBilik - jmlDitempah);

    document.getElementById('status-hari-ini').innerHTML = `
        <div class="flex justify-between text-gray-600">
            <span>${gunaBilikSpesifik ? 'Bilik Ini' : 'Jumlah Bilik'}</span>
            <span class="font-bold">${gunaBilikSpesifik ? 1 : totalBilik}</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full inline-block" style="background:#dc2626"></span>
                Ditempah
            </span>
            <span class="font-bold text-red-500">${jmlDitempah}</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full inline-block" style="background:#16a34a"></span>
                Tersedia
            </span>
            <span class="font-bold text-green-600">${jmlTersedia}</span>
        </div>
    `;
}

// ---- Tutup modal dengan Esc ----
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.getElementById('event-modal').classList.add('hidden');
});
document.addEventListener('click', e => {
    const modal = document.getElementById('event-modal');
    if (e.target === modal) modal.classList.add('hidden');
});
</script>
@endpush
