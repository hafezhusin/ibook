<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Masuk - iBook 2.0</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; }
        .form-input { width:100%; border:1.5px solid rgba(255,255,255,0.2); border-radius:8px; padding:11px 14px; font-size:14px; outline:none; transition:border .2s; background:rgba(255,255,255,0.08); color:white; }
        .form-input::placeholder { color: rgba(255,255,255,0.35); }
        .form-input:focus { border-color:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.2); }
        .left-panel { background: linear-gradient(160deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%); }
        .fc .fc-toolbar-title { font-size: 1rem !important; font-weight: 700; color: #1f2937; }
        .fc .fc-button { background: #f59e0b !important; border-color: #f59e0b !important; font-size: 11px !important; padding: 4px 10px !important; border-radius: 6px !important; }
        .fc .fc-button:hover { background: #d97706 !important; border-color: #d97706 !important; }
        .fc .fc-button-primary:not(:disabled).fc-button-active { background: #d97706 !important; }
        .fc .fc-daygrid-day.fc-day-today { background: rgba(245,158,11,0.08) !important; }
        .fc-event { cursor: pointer; font-size: 11px !important; border: none !important; }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
        .bilik-card { transition: all .2s; cursor: pointer; border: 1.5px solid #e5e7eb; }
        .bilik-card:hover { background: #fef3c7; border-color: #f59e0b; }
        .bilik-card.selected { background: #fef3c7; border-color: #f59e0b; }
    </style>
</head>
<body class="flex h-screen overflow-hidden bg-gray-50">

{{-- ===== PANEL KIRI: LOGIN ===== --}}
<div class="left-panel w-full lg:w-[380px] xl:w-[420px] flex-shrink-0 flex flex-col justify-between p-8 overflow-y-auto">
    <div>
        {{-- Logo --}}
        <div class="flex items-center gap-3 mb-10">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center shadow-lg" style="background:#f59e0b">
                <i class="fa-solid fa-book-open text-white text-lg"></i>
            </div>
            <div>
                <span class="text-white font-bold text-2xl">iBook</span>
                <span style="color:#f59e0b" class="font-bold text-2xl"> 2.0</span>
            </div>
        </div>

        <h2 class="text-white text-2xl font-bold mb-1">Selamat Datang</h2>
        <p class="text-slate-400 text-sm mb-8">Log masuk untuk membuat tempahan bilik mesyuarat</p>

        {{-- Alert --}}
        @if($errors->any())
        <div class="bg-red-900/40 border border-red-500/40 text-red-300 rounded-lg p-3 mb-5 text-sm flex items-center gap-2">
            <i class="fa-solid fa-circle-xmark text-red-400"></i> {{ $errors->first() }}
        </div>
        @endif
        @if(session('error'))
        <div class="bg-red-900/40 border border-red-500/40 text-red-300 rounded-lg p-3 mb-5 text-sm">
            {{ session('error') }}
        </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-300 mb-2">
                    <i class="fa-solid fa-envelope text-amber-400 mr-1"></i> Emel
                </label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="form-input" placeholder="nama@jabatan.gov.my">
            </div>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-slate-300 mb-2">
                    <i class="fa-solid fa-lock text-amber-400 mr-1"></i> Kata Laluan
                </label>
                <div class="relative">
                    <input type="password" name="password" id="password" required
                        class="form-input pr-10" placeholder="••••••••">
                    <button type="button" onclick="togglePwd()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white">
                        <i class="fa-solid fa-eye" id="eye-icon"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center mb-6">
                <label class="flex items-center gap-2 text-sm text-slate-400 cursor-pointer">
                    <input type="checkbox" name="remember" style="accent-color:#f59e0b"> Ingat saya
                </label>
            </div>

            <button type="submit" class="w-full font-bold py-3 rounded-lg transition-all text-white shadow-lg"
                style="background:#f59e0b"
                onmouseover="this.style.background='#d97706'"
                onmouseout="this.style.background='#f59e0b'">
                <i class="fa-solid fa-right-to-bracket mr-2"></i> Log Masuk
            </button>
        </form>

        {{-- Info --}}
        <div class="mt-8 p-4 rounded-xl" style="background:rgba(255,255,255,0.06)">
            <p class="text-xs text-slate-400 mb-2 font-semibold uppercase tracking-wider">
                <i class="fa-solid fa-circle-info text-amber-400 mr-1"></i> Maklumat
            </p>
            <p class="text-xs text-slate-400">
                Anda boleh menyemak ketersediaan bilik mesyuarat di sebelah kanan <span class="text-amber-400 font-semibold">tanpa perlu log masuk</span>.
            </p>
        </div>
    </div>

    <div class="text-center text-xs text-slate-600 mt-6">
        iBook 2.0 &copy; {{ date('Y') }} &mdash; Hak Cipta Terpelihara
    </div>
</div>

{{-- ===== PANEL KANAN: KALENDAR AWAM ===== --}}
<div class="hidden lg:flex flex-1 flex-col overflow-hidden">

    {{-- Header --}}
    <div class="bg-white border-b border-gray-200 px-6 py-3.5 flex items-center justify-between flex-shrink-0 shadow-sm">
        <div>
            <h3 class="font-bold text-gray-800">
                <i class="fa-solid fa-calendar-days text-amber-500 mr-2"></i>
                Ketersediaan Bilik Mesyuarat
            </h3>
            <p class="text-gray-400 text-xs mt-0.5">Semak jadual tempahan bilik — tanpa perlu log masuk</p>
        </div>
        <div class="flex items-center gap-5 text-xs">
            <span class="flex items-center gap-1.5">
                <span class="legend-dot" style="background:#dc2626"></span>
                <span class="text-gray-500">Bilik Ditempah</span>
            </span>
            <span class="flex items-center gap-1.5">
                <span class="legend-dot" style="background:#16a34a"></span>
                <span class="text-gray-500">Bilik Tersedia</span>
            </span>
        </div>
    </div>

    <div class="flex flex-1 overflow-hidden">

        {{-- Sidebar Bilik --}}
        <div class="w-52 bg-white border-r border-gray-100 flex flex-col flex-shrink-0">
            <div class="p-4 overflow-y-auto flex-1">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Tapis Bilik</p>
                <div id="bilik-list" class="space-y-2">
                    <div class="bilik-card selected rounded-lg p-3" onclick="filterBilik(null, this)">
                        <div class="font-semibold text-sm text-gray-800">Semua Bilik</div>
                        <div class="text-xs text-gray-400">Papar semua tempahan</div>
                    </div>
                </div>
            </div>

            {{-- Status Hari Ini --}}
            <div class="p-4 border-t border-gray-100 bg-gray-50 flex-shrink-0">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Status Hari Ini</p>
                <div id="status-hari-ini" class="space-y-2 text-xs">
                    <div class="flex items-center gap-2 text-gray-400">
                        <i class="fa-solid fa-spinner fa-spin text-amber-400"></i> Memuatkan...
                    </div>
                </div>
            </div>
        </div>

        {{-- Kalendar --}}
        <div class="flex-1 p-5 overflow-auto bg-gray-50">
            <div class="bg-white rounded-xl shadow-sm p-5" style="min-height: calc(100vh - 130px)">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

{{-- Popup Event --}}
<div id="event-popup" class="hidden fixed z-50 bg-white rounded-xl shadow-2xl border border-gray-100 p-4 w-60 text-sm">
    <div class="font-bold text-gray-800 mb-1" id="popup-bilik"></div>
    <div class="text-xs text-gray-500 space-y-1">
        <div><i class="fa-solid fa-clock text-amber-400 w-4"></i> <span id="popup-sesi"></span></div>
        <div class="mt-2 pt-2 border-t border-gray-100">
            <span class="text-xs font-semibold text-red-500">
                <i class="fa-solid fa-ban mr-1"></i>Bilik tidak tersedia
            </span>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales/ms.global.min.js"></script>
<script>
let calendar;
let selectedBilik = null;
let allBilik = [];

// Load senarai bilik
fetch('/awam/bilik')
    .then(r => r.json())
    .then(bilik => {
        allBilik = bilik;
        const list = document.getElementById('bilik-list');
        bilik.forEach(b => {
            const div = document.createElement('div');
            div.className = 'bilik-card rounded-lg p-3';
            div.onclick = function() { filterBilik(b.id, this); };
            div.innerHTML = `
                <div class="font-semibold text-sm text-gray-800">${b.nama}</div>
                <div class="text-xs text-gray-400"><i class="fa-solid fa-users text-amber-400 mr-1"></i>${b.kapasiti} orang</div>
            `;
            list.appendChild(div);
        });
        updateStatusHariIni();
    })
    .catch(() => {});

// Init FullCalendar
document.addEventListener('DOMContentLoaded', function() {
    const calEl = document.getElementById('calendar');
    if (!calEl) return;

    calendar = new FullCalendar.Calendar(calEl, {
        initialView: 'dayGridMonth',
        locale: 'ms',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: { today:'Hari Ini', month:'Bulan', week:'Minggu', day:'Hari' },
        events: fetchEvents,
        eventClick: function(info) {
            const p = info.event.extendedProps;
            showPopup(info.jsEvent, p.bilik, p.sesi);
        },
        eventDidMount: function(info) {
            info.el.style.borderRadius = '4px';
            info.el.style.fontSize = '11px';
            info.el.title = info.event.extendedProps.bilik + ' — ' + info.event.extendedProps.sesi;
        },
        height: 'auto',
        dayMaxEvents: 3,
    });
    calendar.render();
});

function fetchEvents(info, success, failure) {
    let url = `/awam/events?start=${info.startStr}&end=${info.endStr}`;
    if (selectedBilik) url += `&bilik_id=${selectedBilik}`;
    fetch(url)
        .then(r => r.json())
        .then(success)
        .catch(failure);
}

function filterBilik(bilikId, el) {
    selectedBilik = bilikId;
    document.querySelectorAll('.bilik-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    if (calendar) calendar.refetchEvents();
}

function updateStatusHariIni() {
    const today = new Date().toISOString().split('T')[0];
    fetch(`/awam/events?start=${today}&end=${today}`)
        .then(r => r.json())
        .then(events => {
            const totalBilik = allBilik.length;
            const bilikDitempah = [...new Set(events.map(e => e.extendedProps?.bilik))].filter(Boolean).length;
            const tersedia = Math.max(0, totalBilik - bilikDitempah);
            const el = document.getElementById('status-hari-ini');
            el.innerHTML = `
                <div class="flex justify-between text-gray-600">
                    <span>Jumlah Bilik</span><span class="font-bold">${totalBilik}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="flex items-center gap-1"><span class="legend-dot" style="background:#dc2626;width:8px;height:8px"></span>Ditempah</span>
                    <span class="font-bold text-red-500">${bilikDitempah}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="flex items-center gap-1"><span class="legend-dot" style="background:#16a34a;width:8px;height:8px"></span>Tersedia</span>
                    <span class="font-bold text-green-600">${tersedia}</span>
                </div>
            `;
        })
        .catch(() => {
            document.getElementById('status-hari-ini').innerHTML = '<span class="text-gray-400 text-xs">Gagal memuatkan</span>';
        });
}

function showPopup(mouseEvent, bilik, sesi) {
    document.getElementById('popup-bilik').textContent = bilik;
    document.getElementById('popup-sesi').textContent = sesi;
    const popup = document.getElementById('event-popup');
    popup.style.top = (mouseEvent.clientY + 10) + 'px';
    popup.style.left = (mouseEvent.clientX + 10) + 'px';
    popup.classList.remove('hidden');
    setTimeout(() => popup.classList.add('hidden'), 3000);
}

function togglePwd() {
    const pwd = document.getElementById('password');
    const icon = document.getElementById('eye-icon');
    pwd.type = pwd.type === 'password' ? 'text' : 'password';
    icon.className = pwd.type === 'text' ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
}

document.addEventListener('click', function(e) {
    const popup = document.getElementById('event-popup');
    if (popup && !popup.contains(e.target)) popup.classList.add('hidden');
});
</script>
</body>
</html>
