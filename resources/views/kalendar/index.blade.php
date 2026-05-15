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
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm mx-4">
        <h2 id="ev-modal-heading" class="font-bold text-gray-800 text-lg mb-3" id="ev-title"></h2>
        <dl class="space-y-2 text-sm">
            <div class="flex items-center gap-2">
                <dt><i class="fa-solid fa-door-open text-amber-400 w-5" aria-hidden="true"></i><span class="sr-only">Bilik:</span></dt>
                <dd id="ev-bilik"></dd>
            </div>
            <div class="flex items-center gap-2">
                <dt><i class="fa-solid fa-clock text-amber-400 w-5" aria-hidden="true"></i><span class="sr-only">Masa:</span></dt>
                <dd id="ev-masa"></dd>
            </div>
            <div class="flex items-center gap-2">
                <dt><i class="fa-solid fa-users text-amber-400 w-5" aria-hidden="true"></i><span class="sr-only">Peserta:</span></dt>
                <dd><span id="ev-peserta"></span> peserta</dd>
            </div>
            <div>
                <dt class="sr-only">Status:</dt>
                <dd><span id="ev-status" role="status"></span></dd>
            </div>
        </dl>
        <button type="button"
            onclick="document.getElementById('event-modal').classList.add('hidden')"
            class="mt-5 w-full btn-secondary justify-center"
            aria-label="Tutup butiran acara">
            Tutup
        </button>
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

            document.getElementById('ev-title').textContent = info.event.title;
            document.getElementById('ev-bilik').textContent = p.bilik;
            document.getElementById('ev-masa').textContent =
                start.toLocaleTimeString('ms-MY', {hour:'2-digit',minute:'2-digit'}) + ' - ' +
                (end ? end.toLocaleTimeString('ms-MY', {hour:'2-digit',minute:'2-digit'}) : '');
            document.getElementById('ev-peserta').textContent = p.peserta;

            const statusEl = document.getElementById('ev-status');
            statusEl.className = p.status === 'diluluskan' ? 'badge-lulus' : 'badge-menunggu';
            statusEl.textContent = p.status === 'diluluskan' ? 'Diluluskan' : 'Menunggu Kelulusan';

            modal.classList.remove('hidden');
            // Focus the close button for keyboard/screen reader users
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
