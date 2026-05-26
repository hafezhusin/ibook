<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Masuk - {{ $tetapan['nama_sistem'] ?? 'iBook 2.0' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERwTkw=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
    <style>
        body { font-family: 'Inter', 'Segoe UI', system-ui, sans-serif; }

        /* ── Skip link ── */
        .skip-link {
            position: absolute;
            top: -100%;
            left: 0;
            background: #f59e0b;
            color: #000;
            font-weight: 700;
            padding: 10px 18px;
            border-radius: 0 0 8px 0;
            z-index: 9999;
            text-decoration: none;
            font-size: 14px;
        }
        .skip-link:focus { top: 0; }

        /* ── Focus ring ── */
        *:focus-visible {
            outline: 3px solid #f59e0b;
            outline-offset: 2px;
        }

        .form-input { width:100%; border:1.5px solid rgba(255,255,255,0.2); border-radius:8px; padding:11px 14px; font-size:14px; outline:none; transition:border .2s; background:rgba(255,255,255,0.08); color:white; }
        .form-input::placeholder { color: rgba(255,255,255,0.35); }
        .form-input:focus { border-color:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.2); }
        .left-panel {
            background-color: #1a1a2e; /* fallback jika gambar belum ada */
            background-image:
                linear-gradient(160deg, rgba(15,20,40,0.82) 0%, rgba(20,30,60,0.75) 50%, rgba(10,30,60,0.85) 100%),
                url('/images/bptm-bg.webp');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
        }
        /* Fallback ke JPG jika WebP tidak disokong */
        @supports not (background-image: url('x.webp')) {
            .left-panel {
                background-image:
                    linear-gradient(160deg, rgba(15,20,40,0.82) 0%, rgba(20,30,60,0.75) 50%, rgba(10,30,60,0.85) 100%),
                    url('/images/bptm-bg.jpg');
            }
        }
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

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition-duration: 0.01ms !important; animation-duration: 0.01ms !important; }
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden bg-gray-50">

{{-- Skip link --}}
<a href="#borang-log-masuk" class="skip-link">Langkau ke borang log masuk</a>

{{-- ===== PANEL KIRI: LOGIN ===== --}}
<main id="borang-log-masuk" class="left-panel w-full lg:w-[380px] xl:w-[420px] flex-shrink-0 flex flex-col justify-between p-8 overflow-y-auto" aria-label="Borang log masuk iBook 2.0">
    <div>
        {{-- Branding Jabatan --}}
        @php
            $namaSistemLogin = $tetapan['nama_sistem'] ?? 'iBook 2.0';
            $namaJabatanLogin = $tetapan['nama_jabatan'] ?? '';
            $logoJabatanLogin = $tetapan['logo_jabatan'] ?? '/images/jata-negara.png';
            if (empty($logoJabatanLogin)) $logoJabatanLogin = '/images/jata-negara.png';
        @endphp

        {{-- Jata Negara + Jabatan --}}
        <div class="flex flex-col items-center text-center mb-8">
            <img src="{{ $logoJabatanLogin }}" alt="Logo Jabatan"
                 class="object-contain mb-3" style="height:96px; width:auto"
                 onerror="this.style.display='none'">
            <p class="text-amber-400 font-semibold leading-tight" style="font-size:11px; letter-spacing:0.05em; text-transform:uppercase">
                {{ $namaJabatanLogin ?: 'Jabatan Akauntan Negara Malaysia' }}
            </p>
            <p class="text-white font-bold mt-1" style="font-size:18px; letter-spacing:-0.01em">
                {{ $namaSistemLogin }}
            </p>
            <p class="text-slate-400" style="font-size:11px; margin-top:2px">Sistem Tempahan Bilik Mesyuarat</p>
        </div>

        <h1 class="text-white font-bold mb-1" style="font-size:1.6rem; letter-spacing:-0.02em">Selamat Datang</h1>
        <p class="text-slate-400 text-sm mb-8">Log masuk untuk membuat tempahan bilik mesyuarat</p>

        {{-- Mesej berjaya set semula kata laluan --}}
        @if(session('success_reset'))
        <div role="alert" aria-live="polite"
            class="bg-green-900/40 border border-green-500/40 text-green-300 rounded-lg p-3 mb-5 text-sm flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-green-400" aria-hidden="true"></i>
            <span>{{ session('success_reset') }}</span>
        </div>
        @endif

        {{-- Mesej ralat --}}
        @if($errors->any())
        <div role="alert" aria-live="assertive"
            class="bg-red-900/40 border border-red-500/40 text-red-300 rounded-lg p-3 mb-5 text-sm flex items-center gap-2"
            id="ralat-log-masuk">
            <i class="fa-solid fa-circle-xmark text-red-400" aria-hidden="true"></i>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif
        @if(session('error'))
        <div role="alert" aria-live="assertive"
            class="bg-red-900/40 border border-red-500/40 text-red-300 rounded-lg p-3 mb-5 text-sm">
            {{ session('error') }}
        </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('login.post') }}" novalidate>
            @csrf

            {{-- Emel --}}
            <div class="mb-4">
                <label for="emel" class="block text-sm font-semibold text-slate-300 mb-2">
                    <i class="fa-solid fa-envelope text-amber-400 mr-1" aria-hidden="true"></i>
                    Emel
                </label>
                <input type="email" id="emel" name="email"
                    value="{{ old('email') }}"
                    required
                    aria-required="true"
                    autocomplete="email"
                    @if($errors->has('email')) aria-invalid="true" aria-describedby="ralat-log-masuk" @endif
                    class="form-input"
                    placeholder="nama@jabatan.gov.my">
            </div>

            {{-- Kata Laluan --}}
            <div class="mb-5">
                <label for="kata-laluan" class="block text-sm font-semibold text-slate-300 mb-2">
                    <i class="fa-solid fa-lock text-amber-400 mr-1" aria-hidden="true"></i>
                    Kata Laluan
                </label>
                <div class="relative">
                    <input type="password" id="kata-laluan" name="password"
                        required
                        aria-required="true"
                        autocomplete="current-password"
                        class="form-input pr-10"
                        placeholder="••••••••">
                    <button type="button"
                        aria-label="Tunjuk atau sembunyikan kata laluan"
                        aria-pressed="false"
                        id="btn-toggle-pwd"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 rounded">
                        <i class="fa-solid fa-eye" id="eye-icon" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            {{-- Ingat saya + Lupa kata laluan --}}
            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center gap-2 text-sm text-slate-400 cursor-pointer" for="ingat-saya">
                    <input type="checkbox" id="ingat-saya" name="remember" style="accent-color:#f59e0b">
                    Ingat saya
                </label>
                <a href="{{ route('password.request') }}" class="text-xs text-slate-400 hover:text-amber-400 transition-colors">
                    Lupa kata laluan?
                </a>
            </div>

            <button type="submit"
                class="w-full font-bold py-3 rounded-lg transition-all text-white shadow-lg"
                style="background:#f59e0b"
                onmouseover="this.style.background='#d97706'"
                onmouseout="this.style.background='#f59e0b'">
                <i class="fa-solid fa-right-to-bracket mr-2" aria-hidden="true"></i>
                Log Masuk
            </button>
        </form>

        {{-- Pemisah --}}
        <div class="flex items-center gap-3 my-5">
            <div class="flex-1 h-px" style="background:rgba(255,255,255,0.1)"></div>
            <span class="text-xs text-slate-500 font-medium">atau</span>
            <div class="flex-1 h-px" style="background:rgba(255,255,255,0.1)"></div>
        </div>

        {{-- Log Masuk Google --}}
        <a href="{{ route('auth.google') }}"
           class="flex items-center justify-center gap-3 w-full py-3 rounded-lg font-semibold text-sm transition-all"
           style="background:rgba(255,255,255,0.08); border:1.5px solid rgba(255,255,255,0.15); color:#e2e8f0"
           onmouseover="this.style.background='rgba(255,255,255,0.14)'; this.style.borderColor='rgba(255,255,255,0.3)'"
           onmouseout="this.style.background='rgba(255,255,255,0.08)'; this.style.borderColor='rgba(255,255,255,0.15)'">
            <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
                <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                <path fill="#FF3D00" d="m6.306 14.691 6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/>
                <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/>
                <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
            </svg>
            Log Masuk dengan Google Workspace
        </a>
        <p class="text-center text-xs text-slate-600 mt-2">Hanya akaun <span class="text-slate-400">@anm.gov.my</span> dibenarkan</p>

        {{-- Info --}}
        <aside class="mt-8 p-4 rounded-xl" style="background:rgba(255,255,255,0.06)" aria-label="Maklumat tambahan">
            <p class="text-xs text-slate-400 mb-2 font-semibold uppercase tracking-wider">
                <i class="fa-solid fa-circle-info text-amber-400 mr-1" aria-hidden="true"></i>
                Maklumat
            </p>
            <p class="text-xs text-slate-400">
                Anda boleh menyemak ketersediaan bilik mesyuarat di sebelah kanan
                <span class="text-amber-400 font-semibold">tanpa perlu log masuk</span>.
            </p>
        </aside>

        {{-- Pautan Manual Pengguna Staf --}}
        <a href="/docs/Manual_Pengguna_Staf_iBook2.pdf"
            target="_blank"
            rel="noopener noreferrer"
            class="mt-5 flex items-center gap-3 p-4 rounded-xl border transition-all group"
            style="background:rgba(22,163,74,0.08); border-color:rgba(22,163,74,0.3)"
            onmouseover="this.style.borderColor='rgba(22,163,74,0.6)'"
            onmouseout="this.style.borderColor='rgba(22,163,74,0.3)'"
            aria-label="Buka Manual Pengguna Staf iBook 2.0 dalam format PDF (buka dalam tab baharu)">
            <span class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background:rgba(22,163,74,0.2)">
                <i class="fa-solid fa-file-pdf text-green-400 text-lg group-hover:scale-110 transition-transform" aria-hidden="true"></i>
            </span>
            <span>
                <span class="block text-sm font-semibold text-green-400">Manual Pengguna</span>
                <span class="block text-xs text-slate-400 mt-0.5">Panduan lengkap cara menggunakan sistem {{ $tetapan['nama_sistem'] ?? 'iBook 2.0' }}</span>
            </span>
            <i class="fa-solid fa-arrow-up-right-from-square text-slate-500 group-hover:text-green-400 ml-auto text-xs transition-colors" aria-hidden="true"></i>
        </a>
    </div>

    <p class="text-center text-xs text-slate-600 mt-6">
        <small>{{ $tetapan['nama_sistem'] ?? 'iBook 2.0' }} &copy; {{ date('Y') }} &mdash; Hak Cipta Terpelihara</small>
    </p>
</main>

{{-- ===== PANEL KANAN: KALENDAR AWAM ===== --}}
<section class="hidden lg:flex flex-1 flex-col overflow-hidden" aria-label="Kalendar ketersediaan bilik mesyuarat">

    {{-- Header --}}
    <header class="bg-white border-b border-gray-200 px-6 py-3.5 flex items-center justify-between flex-shrink-0 shadow-sm">
        <div>
            <h2 class="font-bold text-gray-800">
                <i class="fa-solid fa-calendar-days text-amber-500 mr-2" aria-hidden="true"></i>
                Ketersediaan Bilik Mesyuarat
            </h2>
            <p class="text-gray-400 text-xs mt-0.5">Semak jadual tempahan bilik — tanpa perlu log masuk</p>
        </div>
        <div class="flex items-center gap-5 text-xs" aria-label="Petunjuk warna">
            <span class="flex items-center gap-1.5">
                <span class="legend-dot" style="background:#dc2626" role="img" aria-label="Merah"></span>
                <span class="text-gray-500">Bilik Ditempah</span>
            </span>
            <span class="flex items-center gap-1.5">
                <span class="legend-dot" style="background:#16a34a" role="img" aria-label="Hijau"></span>
                <span class="text-gray-500">Bilik Tersedia</span>
            </span>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">

        {{-- Sidebar Bilik --}}
        <aside class="w-52 bg-white border-r border-gray-100 flex flex-col flex-shrink-0" aria-label="Tapis bilik mesyuarat">
            <div class="p-4 overflow-y-auto flex-1">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3" id="label-tapis-bilik">Tapis Bilik</p>
                <ul role="list" id="bilik-list" class="space-y-2" aria-labelledby="label-tapis-bilik">
                    <li>
                        <button type="button"
                            id="btn-semua-awam"
                            class="bilik-card selected rounded-lg p-3 w-full text-left"
                            aria-pressed="true">
                            <div class="font-semibold text-sm text-gray-800">Semua Bilik</div>
                            <div class="text-xs text-gray-400">Papar semua tempahan</div>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Status Hari Ini --}}
            <div class="p-4 border-t border-gray-100 bg-gray-50 flex-shrink-0">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Status Hari Ini</p>
                <div id="status-hari-ini" class="space-y-2 text-xs" aria-live="polite" aria-label="Status bilik hari ini">
                    <div class="flex items-center gap-2 text-gray-400">
                        <i class="fa-solid fa-spinner fa-spin text-amber-400" aria-hidden="true"></i>
                        <span>Memuatkan...</span>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Kalendar --}}
        <div class="flex-1 p-5 overflow-auto bg-gray-50">
            <div class="bg-white rounded-xl shadow-sm p-5" style="min-height: calc(100vh - 130px)">
                <div id="calendar" role="application" aria-label="Kalendar tempahan bilik mesyuarat"></div>
            </div>
        </div>
    </div>
</section>

{{-- Popup Event --}}
<div id="event-popup"
    class="hidden fixed z-50 bg-white rounded-xl shadow-2xl border border-gray-100 p-4 w-64 text-sm"
    role="tooltip"
    aria-live="polite">
    <div class="font-bold text-gray-800 mb-1 leading-tight" id="popup-nama"></div>
    <div class="text-xs text-gray-500 space-y-1">
        <div>
            <i class="fa-solid fa-clock text-amber-400 w-4" aria-hidden="true"></i>
            <span id="popup-sesi"></span>
        </div>
        <div class="mt-2 pt-2 border-t border-gray-100">
            <span class="text-xs font-semibold text-red-500">
                <i class="fa-solid fa-ban mr-1" aria-hidden="true"></i>Bilik tidak tersedia
            </span>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales/ms.global.min.js"></script>
<script nonce="{{ $cspNonce }}">
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
            const li = document.createElement('li');
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'bilik-card rounded-lg p-3 w-full text-left';
            btn.setAttribute('aria-pressed', 'false');
            btn.onclick = function() { filterBilik(b.id, this); };
            btn.innerHTML = `
                <div class="font-semibold text-sm text-gray-800">${b.nama}</div>
                <div class="text-xs text-gray-400"><i class="fa-solid fa-users text-amber-400 mr-1" aria-hidden="true"></i>${b.kapasiti} orang</div>
            `;
            li.appendChild(btn);
            list.appendChild(li);
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
            const nama = p.nama || info.event.title || '-';
            const sesiLabel = p.sesi_key === 'pagi' ? 'Sesi Pagi (9:00 - 13:00)' : 'Sesi Petang (14:00 - 18:00)';
            showPopup(info.jsEvent, nama, p.bilik, sesiLabel);
        },
        eventDidMount: function(info) {
            const p = info.event.extendedProps;
            const nama = p.nama || info.event.title || '-';
            const sesiLabel = p.sesi_key === 'pagi' ? 'Sesi Pagi (9:00 - 13:00)' : 'Sesi Petang (14:00 - 18:00)';
            info.el.style.borderRadius = '4px';
            info.el.style.fontSize = '11px';
            info.el.title = nama + ' — ' + sesiLabel;
            info.el.setAttribute('aria-label', nama + ', ' + sesiLabel);
        },
        height: 'auto',
        dayMaxEvents: 3,
    });
    calendar.render();
});

function fetchEvents(info, success, failure) {
    const params = new URLSearchParams({ start: info.startStr, end: info.endStr });
    if (selectedBilik) params.append('bilik_id', selectedBilik);
    fetch(`/awam/events?${params}`)
        .then(r => r.json())
        .then(success)
        .catch(failure);
}

function filterBilik(bilikId, el) {
    selectedBilik = bilikId;
    document.querySelectorAll('#bilik-list button').forEach(c => {
        c.classList.remove('selected');
        c.setAttribute('aria-pressed', 'false');
    });
    el.classList.add('selected');
    el.setAttribute('aria-pressed', 'true');
    if (calendar) calendar.refetchEvents();
}

function updateStatusHariIni() {
    const today = new Date().toISOString().split('T')[0];
    fetch(`/awam/events?start=${today}&end=${today}`)
        .then(r => r.json())
        .then(events => {
            const totalBilik = allBilik.length;

            // Bilik unik yang ditempah mengikut sesi
            const bilikPagi   = new Set(events.filter(e => e.extendedProps?.sesi_key === 'pagi')
                                              .map(e => e.extendedProps?.bilik_id)).size;
            const bilikPetang = new Set(events.filter(e => e.extendedProps?.sesi_key === 'petang')
                                              .map(e => e.extendedProps?.bilik_id)).size;

            const dot = (c) => `<span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:${c};flex-shrink:0"></span>`;

            const el = document.getElementById('status-hari-ini');
            el.innerHTML = `
                <div class="flex justify-between text-gray-600 pb-1.5 mb-1.5" style="border-bottom:1px solid #f3f4f6">
                    <span>Jumlah Bilik</span><span class="font-bold">${totalBilik}</span>
                </div>
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Sesi Pagi</div>
                <div class="flex justify-between items-center mb-0.5">
                    <span class="flex items-center gap-1">${dot('#dc2626')} Ditempah</span>
                    <span class="font-bold text-red-500">${bilikPagi}</span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="flex items-center gap-1">${dot('#16a34a')} Tersedia</span>
                    <span class="font-bold text-green-600">${Math.max(0, totalBilik - bilikPagi)}</span>
                </div>
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Sesi Petang</div>
                <div class="flex justify-between items-center mb-0.5">
                    <span class="flex items-center gap-1">${dot('#dc2626')} Ditempah</span>
                    <span class="font-bold text-red-500">${bilikPetang}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="flex items-center gap-1">${dot('#16a34a')} Tersedia</span>
                    <span class="font-bold text-green-600">${Math.max(0, totalBilik - bilikPetang)}</span>
                </div>
            `;
        })
        .catch(() => {
            document.getElementById('status-hari-ini').innerHTML = '<span class="text-gray-400 text-xs">Gagal memuatkan</span>';
        });
}

function showPopup(mouseEvent, nama, bilik, sesi) {
    document.getElementById('popup-nama').textContent = nama;
    document.getElementById('popup-bilik').textContent = bilik;
    document.getElementById('popup-sesi').textContent = sesi;
    const popup = document.getElementById('event-popup');
    popup.style.top = (mouseEvent.clientY + 10) + 'px';
    popup.style.left = (mouseEvent.clientX + 10) + 'px';
    popup.classList.remove('hidden');
    setTimeout(() => popup.classList.add('hidden'), 3000);
}

// Wire event listeners (CSP-safe)
document.getElementById('btn-semua-awam')?.addEventListener('click', function() {
    filterBilik(null, this);
});
document.getElementById('btn-toggle-pwd')?.addEventListener('click', togglePwd);

function togglePwd() {
    const pwd = document.getElementById('kata-laluan');
    const icon = document.getElementById('eye-icon');
    const btn = document.getElementById('btn-toggle-pwd');
    const isShowing = pwd.type === 'text';
    pwd.type = isShowing ? 'password' : 'text';
    icon.className = isShowing ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
    btn.setAttribute('aria-pressed', isShowing ? 'false' : 'true');
    btn.setAttribute('aria-label', isShowing ? 'Tunjuk kata laluan' : 'Sembunyikan kata laluan');
}

document.addEventListener('click', function(e) {
    const popup = document.getElementById('event-popup');
    if (popup && !popup.contains(e.target)) popup.classList.add('hidden');
});
</script>
</body>
</html>
