@extends('layouts.app')

@section('title', 'Kalendar')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
@endpush

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Kalendar</h1>
        <p class="text-gray-500 text-sm mt-1">Paparan tempahan mengikut bulan</p>
    </div>
    <a href="{{ route('tempahan.create') }}" class="btn-primary">
        <i class="fa-solid fa-plus" aria-hidden="true"></i> Tempahan Baru
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm p-6">

    {{-- Legend warna --}}
    <div class="flex flex-wrap items-center gap-4 mb-4 pb-4 border-b border-gray-100 text-xs text-gray-500">
        <span class="font-semibold text-gray-600">Kod Warna:</span>
        <span class="flex items-center gap-1.5">
            <span class="inline-block w-3 h-3 rounded-sm" style="background:#16a34a"></span>
            Tempahan Saya (Diluluskan)
        </span>
        <span class="flex items-center gap-1.5">
            <span class="inline-block w-3 h-3 rounded-sm" style="background:#2563eb"></span>
            Tempahan Lain (Diluluskan)
        </span>
        <span class="flex items-center gap-1.5">
            <span class="inline-block w-3 h-3 rounded-sm" style="background:#d97706"></span>
            Menunggu Kelulusan
        </span>
        <span class="text-gray-400 italic">Klik pada acara untuk lihat butiran</span>
    </div>

    <div id="calendar"
        role="application"
        aria-label="Kalendar tempahan bilik mesyuarat — klik pada tarikh atau acara untuk butiran">
    </div>
</div>

{{-- Modal Butiran Acara --}}
<div id="event-modal"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    role="dialog"
    aria-modal="true"
    aria-labelledby="ev-modal-heading">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 overflow-hidden">

        {{-- Header modal --}}
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
                    aria-label="Tutup butiran acara">
                    <i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i>
                </button>
            </div>
            <div class="mt-3">
                <span id="ev-status" role="status" class="text-xs font-bold px-2 py-1 rounded-full"></span>
            </div>
        </div>

        {{-- Body modal --}}
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
                <dd class="text-gray-700">
                    <span id="ev-peserta"></span> peserta
                </dd>
            </div>

            <div class="flex items-center gap-3">
                <dt class="flex-shrink-0 w-5">
                    <i class="fa-solid fa-person text-amber-400" aria-hidden="true"></i>
                    <span class="sr-only">Pemohon:</span>
                </dt>
                <dd class="text-gray-700">
                    Pemohon: <span id="ev-pemohon"></span>
                </dd>
            </div>

            <div id="ev-tujuan-row" class="flex items-start gap-3">
                <dt class="flex-shrink-0 w-5 mt-0.5">
                    <i class="fa-solid fa-align-left text-amber-400" aria-hidden="true"></i>
                    <span class="sr-only">Tujuan:</span>
                </dt>
                <dd class="text-gray-600 text-xs leading-relaxed" id="ev-tujuan"></dd>
            </div>

        </dl>

        {{-- Footer --}}
        <div class="px-6 pb-5">
            <button type="button"
                onclick="document.getElementById('event-modal').classList.add('hidden')"
                class="w-full btn-secondary justify-center"
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
document.addEventListener('DOMContentLoaded', function() {
    const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        locale: 'ms',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulan',
            week: 'Minggu',
            day: 'Hari'
        },
        events: '{{ route("kalendar.events") }}',
        eventClick: function(info) {
            const p = info.event.extendedProps;
            const start = info.event.start;
            const end = info.event.end;
            const modal = document.getElementById('event-modal');

            // Nama mesyuarat
            document.getElementById('ev-modal-heading').textContent = info.event.title;

            // Kategori
            const kategoriMap = {
                'pengurusan': 'Pengurusan', 'teknikal': 'Teknikal',
                'latihan': 'Latihan / Bengkel', 'perbincangan': 'Perbincangan',
                'taklimat': 'Taklimat', 'lain-lain': 'Lain-lain', 'mesyuarat': 'Mesyuarat'
            };
            document.getElementById('ev-kategori').textContent = kategoriMap[p.kategori] || p.kategori || 'Mesyuarat';

            // Bilik & lokasi
            document.getElementById('ev-bilik').textContent = p.bilik;
            document.getElementById('ev-lokasi').textContent = p.lokasi ? '(' + p.lokasi + ')' : '';

            // Masa
            const fmtMasa = (t) => t ? t.toLocaleTimeString('ms-MY', {hour:'2-digit', minute:'2-digit'}) : '';
            document.getElementById('ev-masa').textContent =
                p.sesi + '  ·  ' + fmtMasa(start) + ' – ' + (end ? fmtMasa(end) : '');

            // Pengerusi, peserta, pemohon
            document.getElementById('ev-pengerusi').textContent = p.nama_pengerusi || '-';
            document.getElementById('ev-peserta').textContent = p.peserta || '0';
            document.getElementById('ev-pemohon').textContent = p.pemohon || '-';

            // Tujuan
            const tujuanRow = document.getElementById('ev-tujuan-row');
            const tujuanEl  = document.getElementById('ev-tujuan');
            if (p.tujuan && p.tujuan.trim()) {
                tujuanEl.textContent = p.tujuan;
                tujuanRow.classList.remove('hidden');
            } else {
                tujuanRow.classList.add('hidden');
            }

            // Status badge
            const statusEl = document.getElementById('ev-status');
            if (p.status === 'diluluskan') {
                statusEl.style.cssText = 'background:#dcfce7;color:#166534';
                statusEl.textContent = '✓ Diluluskan';
            } else {
                statusEl.style.cssText = 'background:#fef3c7;color:#92400e';
                statusEl.textContent = '⏳ Menunggu Kelulusan';
            }

            // Warna header ikut milik sendiri atau orang lain
            const header = document.getElementById('ev-header');
            header.style.background = p.is_own ? '#14532d' : '#1a1a2e';

            modal.classList.remove('hidden');
            setTimeout(() => modal.querySelector('button').focus(), 50);
        },
        eventDidMount: function(info) {
            info.el.style.borderRadius = '4px';
            info.el.style.padding = '2px 4px';
            info.el.setAttribute('aria-label',
                info.event.title + (info.event.extendedProps.bilik ? ', ' + info.event.extendedProps.bilik : ''));
        }
    });
    calendar.render();
});

// Esc key closes modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('event-modal').classList.add('hidden');
    }
});
</script>
@endpush
