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
        <i class="fa-solid fa-plus"></i> Tempahan Baru
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm p-6">
    <div id="calendar"></div>
</div>

{{-- Event Detail Modal --}}
<div id="event-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm mx-4">
        <h3 id="ev-title" class="font-bold text-gray-800 text-lg mb-3"></h3>
        <div class="space-y-2 text-sm">
            <div><i class="fa-solid fa-door-open text-amber-400 w-5"></i> <span id="ev-bilik"></span></div>
            <div><i class="fa-solid fa-clock text-amber-400 w-5"></i> <span id="ev-masa"></span></div>
            <div><i class="fa-solid fa-users text-amber-400 w-5"></i> <span id="ev-peserta"></span> peserta</div>
            <div><span id="ev-status" class="badge-lulus"></span></div>
        </div>
        <button onclick="document.getElementById('event-modal').classList.add('hidden')"
            class="mt-5 w-full btn-secondary justify-center">Tutup</button>
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
            document.getElementById('ev-title').textContent = info.event.title;
            document.getElementById('ev-bilik').textContent = p.bilik;
            document.getElementById('ev-masa').textContent =
                start.toLocaleTimeString('ms-MY', {hour:'2-digit',minute:'2-digit'}) + ' - ' +
                (end ? end.toLocaleTimeString('ms-MY', {hour:'2-digit',minute:'2-digit'}) : '');
            document.getElementById('ev-peserta').textContent = p.peserta;
            const statusEl = document.getElementById('ev-status');
            statusEl.className = p.status === 'diluluskan' ? 'badge-lulus' : 'badge-menunggu';
            statusEl.textContent = p.status === 'diluluskan' ? 'Diluluskan' : 'Menunggu Kelulusan';
            document.getElementById('event-modal').classList.remove('hidden');
        },
        eventDidMount: function(info) {
            info.el.style.borderRadius = '4px';
            info.el.style.padding = '2px 4px';
        }
    });
    calendar.render();
});
</script>
@endpush
