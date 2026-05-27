<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Cegah FOUC: pakai tema tersimpan sebelum CSS dimuatkan --}}
    <script nonce="{{ $cspNonce ?? '' }}">
    (function(){try{var t=localStorage.getItem('ibook-theme');if(t==='dark'||t==='light')document.documentElement.classList.add(t);}catch(e){}})();
    </script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $tetapan['nama_sistem'] ?? 'iBook 2.0' }} — Sistem Tempahan Bilik Mesyuarat">
    <title>@yield('title', $tetapan['nama_sistem'] ?? 'iBook 2.0') — {{ $tetapan['nama_jabatan'] ?? 'Sistem Tempahan Bilik Mesyuarat' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERwTkw=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    @stack('styles')
    <style>
        /* ── Pembolehubah CSS ───────────────────────────────────── */
        :root {
            --sidebar-bg: #1a1a2e;
            --accent: #f59e0b;
            --accent-dark: #d97706;
            --focus-ring: 0 0 0 3px rgba(245,158,11,.5);
        }

        /* ── Tipografi & Asas ───────────────────────────────────── */
        body { font-family: 'Inter', 'Segoe UI', system-ui, sans-serif; background: #f3f4f6; color: #1f2937; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Inter', system-ui, sans-serif; font-weight: 700; letter-spacing: -0.02em; }
        .page-title { font-size: 1.5rem; font-weight: 800; color: #0f172a; letter-spacing: -0.03em; line-height: 1.2; }
        .page-subtitle { font-size: 0.875rem; color: #6b7280; font-weight: 400; margin-top: 2px; }

        /* ── Skip Navigation (WCAG 2.4.1) ──────────────────────── */
        .skip-link {
            position: absolute;
            top: -100px;
            left: 8px;
            z-index: 9999;
            background: var(--accent);
            color: #1a1a2e;
            font-weight: 700;
            padding: 10px 18px;
            border-radius: 0 0 8px 8px;
            text-decoration: none;
            transition: top .15s;
        }
        .skip-link:focus { top: 0; outline: 3px solid #1a1a2e; outline-offset: 2px; }

        /* ── Focus Visible (WCAG 2.4.7) ────────────────────────── */
        *:focus-visible {
            outline: 3px solid var(--accent);
            outline-offset: 2px;
        }
        a:focus-visible, button:focus-visible, input:focus-visible,
        select:focus-visible, textarea:focus-visible {
            outline: 3px solid var(--accent);
            outline-offset: 2px;
            border-radius: 4px;
        }

        /* ── Sidebar ────────────────────────────────────────────── */
        .sidebar { background: var(--sidebar-bg); min-height: 100vh; width: 260px; flex-shrink: 0; }
        .sidebar-link {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 20px; color: #cbd5e1; border-radius: 8px;
            margin: 2px 8px; transition: all .2s;
            text-decoration: none; font-size: 14px;
        }
        .sidebar-link:hover { background: rgba(245,158,11,.15); color: var(--accent); }
        .sidebar-link[aria-current="page"] {
            background: rgba(245,158,11,.15);
            color: var(--accent);
            border-right: 3px solid var(--accent);
            font-weight: 600;
        }
        .sidebar-link:focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: -2px;
        }

        /* ── Komponen Umum ─────────────────────────────────────── */
        .badge-lulus    { background:#d1fae5; color:#064e3b; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600; }
        .badge-tolak    { background:#fee2e2; color:#7f1d1d; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600; }

        /* Butang — contrast ratio 4.5:1+ (WCAG 1.4.3) */
        .btn-primary {
            background: var(--accent); color: #1a1a2e;
            padding: 8px 20px; border-radius: 8px; font-weight: 700;
            border: 2px solid var(--accent); cursor: pointer;
            display: inline-flex; align-items: center; gap: 6px;
            text-decoration: none; transition: background .2s, color .2s;
        }
        .btn-primary:hover, .btn-primary:focus-visible {
            background: var(--accent-dark); border-color: var(--accent-dark);
        }
        .btn-danger {
            background: #dc2626; color: #fff;
            padding: 6px 14px; border-radius: 6px; font-weight: 600;
            border: 2px solid #dc2626; cursor: pointer; font-size: 13px;
        }
        .btn-danger:hover { background: #b91c1c; border-color: #b91c1c; }
        .btn-success {
            background: #15803d; color: #fff;
            padding: 6px 14px; border-radius: 6px; font-weight: 600;
            border: 2px solid #15803d; cursor: pointer; font-size: 13px;
        }
        .btn-success:hover { background: #166534; border-color: #166534; }
        .btn-secondary {
            background: #e5e7eb; color: #1f2937;
            padding: 8px 20px; border-radius: 8px; font-weight: 600;
            border: 2px solid #d1d5db; cursor: pointer;
            display: inline-flex; align-items: center; gap: 6px;
            text-decoration: none;
        }
        .btn-secondary:hover { background: #d1d5db; }

        /* ── Borang ─────────────────────────────────────────────── */
        .stat-card { background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,.1); }
        .form-input {
            width: 100%; border: 2px solid #d1d5db; border-radius: 8px;
            padding: 10px 14px; font-size: 14px; outline: none; transition: border .2s;
            background: #fff; color: #1f2937;
        }
        .form-input:focus { border-color: var(--accent); box-shadow: var(--focus-ring); }
        .form-input[aria-invalid="true"] { border-color: #dc2626; }
        .form-label { font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 6px; display: block; }
        .form-hint { font-size: 12px; color: #6b7280; margin-top: 3px; }
        .form-error { font-size: 12px; color: #dc2626; margin-top: 4px; display: flex; align-items: center; gap: 4px; }

        /* ── Bar kemajuan ────────────────────────────────────────── */
        .progress-bar { height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden; }
        .progress-fill { height: 100%; background: var(--accent); border-radius: 4px; transition: width .5s; }

        /* ── Alert ──────────────────────────────────────────────── */
        .alert-success {
            background: #d1fae5; border: 1px solid #6ee7b7; color: #064e3b;
            padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;
        }
        .alert-error {
            background: #fee2e2; border: 1px solid #fca5a5; color: #7f1d1d;
            padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;
        }

        /* ── Jadual ─────────────────────────────────────────────── */
        .table-header { background: #f9fafb; }
        .table th {
            padding: 12px 16px; text-align: left;
            font-size: 13px; font-weight: 600; color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        .table td { padding: 14px 16px; font-size: 14px; color: #374151; border-bottom: 1px solid #f3f4f6; }
        .table tr:hover td { background: #fafafa; }
        .table caption { font-size: 13px; color: #6b7280; padding: 8px 16px; text-align: left; }

        /* ── Badge notifikasi ───────────────────────────────────── */
        .notification-badge {
            background: #ef4444; color: #fff; border-radius: 50%;
            width: 18px; height: 18px; font-size: 10px;
            display: flex; align-items: center; justify-content: center;
            position: absolute; top: -4px; right: -4px;
            border: 2px solid #fff;
        }

        /* ══════════════════════════════════════════════════════════
           MOBILE RESPONSIVENESS
           ══════════════════════════════════════════════════════════ */

        /* Overlay gelap bila sidebar dibuka pada mobile */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.55);
            z-index: 25;
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
        }
        #sidebar-overlay.aktif { display: block; }

        /* Mobile: sidebar tersembunyi di luar skrin */
        @media (max-width: 1023px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.28s cubic-bezier(.4,0,.2,1);
                z-index: 30;
            }
            .sidebar.mobile-open {
                transform: translateX(0);
                box-shadow: 6px 0 24px rgba(0,0,0,.45);
            }
        }

        /* Desktop: sidebar sentiasa kelihatan */
        @media (min-width: 1024px) {
            .sidebar { transform: none !important; transition: none !important; }
            #sidebar-overlay { display: none !important; }
            #btn-hamburger { display: none !important; }
        }

        /* ── Mode tinggi kontras (forced-colors) ────────────────── */
        @media (forced-colors: active) {
            .btn-primary, .btn-danger, .btn-success, .btn-secondary { border: 2px solid ButtonText; }
            .sidebar-link[aria-current="page"] { border: 2px solid Highlight; }
        }

        /* ── Animasi hanya jika pengguna benarkan ───────────────── */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; }
        }

        /* ══════════════════════════════════════════════════════════
           DARK MODE — Auto ikut tetapan sistem pengguna
           ══════════════════════════════════════════════════════════ */
        @media (prefers-color-scheme: dark) {

            /* ── Asas ────────────────────────────────────────────── */
            body { background: #0f172a !important; color: #e2e8f0 !important; }

            /* ── Top header ──────────────────────────────────────── */
            header[role="banner"] {
                background: #1e293b !important;
                box-shadow: 0 1px 3px rgba(0,0,0,.4) !important;
            }
            header[role="banner"] input[type="search"] {
                background: #334155 !important;
                color: #f1f5f9 !important;
            }
            header[role="banner"] input[type="search"]::placeholder { color: #64748b !important; }

            /* ── Kad / Panel putih ───────────────────────────────── */
            .bg-white { background: #1e293b !important; }
            .bg-gray-50 { background: #0f172a !important; }
            .bg-gray-100 { background: #334155 !important; }
            .shadow-sm { box-shadow: 0 1px 4px rgba(0,0,0,.35) !important; }
            .rounded-xl.shadow-sm, .rounded-xl.shadow { box-shadow: 0 2px 8px rgba(0,0,0,.4) !important; }

            /* ── Teks ────────────────────────────────────────────── */
            .text-gray-800, .text-gray-900 { color: #f1f5f9 !important; }
            .text-gray-700 { color: #e2e8f0 !important; }
            .text-gray-600 { color: #cbd5e1 !important; }
            .text-gray-500 { color: #94a3b8 !important; }
            .text-gray-400 { color: #64748b !important; }
            .text-gray-300 { color: #475569 !important; }

            /* ── Border ──────────────────────────────────────────── */
            .border-gray-100 { border-color: #334155 !important; }
            .border-gray-200 { border-color: #475569 !important; }
            .border-b, .border-t { border-color: #334155 !important; }
            .divide-gray-50 > * + * { border-color: #334155 !important; }
            .divide-y > * + * { border-color: #334155 !important; }

            /* ── Borang ──────────────────────────────────────────── */
            .form-input {
                background: #0f172a !important;
                border-color: #475569 !important;
                color: #f1f5f9 !important;
            }
            .form-input:focus { border-color: #f59e0b !important; }
            .form-input::placeholder { color: #64748b !important; }
            .form-label { color: #e2e8f0 !important; }
            .form-hint { color: #94a3b8 !important; }
            .form-error { color: #f87171 !important; }
            select.form-input option { background: #1e293b; color: #f1f5f9; }

            /* ── Jadual ──────────────────────────────────────────── */
            .table-header { background: #0f172a !important; }
            .table th { color: #cbd5e1 !important; border-bottom-color: #334155 !important; }
            .table td { color: #e2e8f0 !important; border-bottom-color: #1e293b !important; }
            .table tr:hover td { background: #273447 !important; }

            /* ── Hover ───────────────────────────────────────────── */
            .hover\:bg-gray-50:hover { background: #273447 !important; }
            .hover\:bg-amber-50:hover { background: #1c1200 !important; }

            /* ── Butang ──────────────────────────────────────────── */
            .btn-secondary {
                background: #334155 !important;
                color: #e2e8f0 !important;
                border-color: #475569 !important;
            }
            .btn-secondary:hover { background: #475569 !important; }

            /* ── Stat cards ──────────────────────────────────────── */
            .stat-card { background: #1e293b !important; }
            .stat-card-v2 { background: #1e293b !important; }
            .stat-action { border-top-color: #334155 !important; }
            .stat-action:hover { background: #273447 !important; }

            /* ── Progress bar ────────────────────────────────────── */
            .progress-bar { background: #334155 !important; }

            /* ── Badge ───────────────────────────────────────────── */
            .badge-lulus    { background: #14532d !important; color: #86efac !important; }
            .badge-tolak    { background: #7f1d1d !important; color: #fca5a5 !important; }

            /* ── Alert ───────────────────────────────────────────── */
            .alert-success { background: #052e16 !important; border-color: #166534 !important; color: #86efac !important; }
            .alert-error   { background: #450a0a !important; border-color: #991b1b 100% !important; color: #fca5a5 !important; }

            /* ── Modal ───────────────────────────────────────────── */
            #event-modal > div { background: #1e293b !important; }
            #event-modal dl { background: #1e293b !important; }
            #event-modal .px-6.py-4 { background: #1e293b !important; }

            /* ── Sidebar (sudah gelap, tapi perbaiki hover) ──────── */
            .sidebar-link:hover { background: rgba(245,158,11,.2) !important; }

            /* ── Butang tapis bilik (kalendar sidebar) ───────────── */
            .bilik-btn {
                background: #1e293b !important;
                border-color: #334155 !important;
                color: #e2e8f0 !important;
            }
            .bilik-btn:hover {
                background: #1c1a00 !important;
                border-color: #f59e0b !important;
                color: #f59e0b !important;
            }
            .bilik-btn.aktif {
                background: #1c1a00 !important;
                border-color: #f59e0b !important;
                color: #f59e0b !important;
            }
            .bilik-btn .text-gray-800 { color: #f1f5f9 !important; }
            .bilik-btn .text-gray-400 { color: #94a3b8 !important; }

            /* ── Ketersediaan bilik cards ─────────────────────────── */
            .bilik-card { background: #1e293b !important; }
            .kemudahan-tag { background: #334155 !important; color: #e2e8f0 !important; }

            /* ── Flatpickr calendar ──────────────────────────────── */
            .flatpickr-calendar {
                background: #1e293b !important;
                box-shadow: 0 4px 20px rgba(0,0,0,.5) !important;
            }
            .flatpickr-day { color: #e2e8f0 !important; }
            .flatpickr-day:hover { background: #334155 !important; }
            .flatpickr-day.selected, .flatpickr-day.selected:hover {
                background: #f59e0b !important;
                border-color: #f59e0b !important;
                color: #1a1a2e !important;
            }
            .flatpickr-day.today { border-color: #f59e0b !important; }
            .flatpickr-day.disabled { color: #475569 !important; }
            .flatpickr-months .flatpickr-month,
            .flatpickr-weekdays,
            span.flatpickr-weekday {
                background: #0f172a !important;
                color: #94a3b8 !important;
                fill: #94a3b8 !important;
            }
            .flatpickr-current-month input,
            .flatpickr-current-month .numInputWrapper,
            .flatpickr-current-month span.arrowUp,
            .flatpickr-current-month span.arrowDown { color: #f1f5f9 !important; }
            .numInputWrapper:hover { background: #334155 !important; }
            .flatpickr-prev-month svg, .flatpickr-next-month svg { fill: #94a3b8 !important; }
            .flatpickr-prev-month:hover svg, .flatpickr-next-month:hover svg { fill: #f59e0b !important; }

            /* ── Footer ─────────────────────────────────────────── */
            footer[role="contentinfo"] {
                background: #0f172a !important;
                border-color: #1e293b !important;
                color: #475569 !important;
            }
            footer[role="contentinfo"] a:hover { color: #f59e0b !important; }

            /* ── FullCalendar ────────────────────────────────────── */
            .fc { color: #e2e8f0 !important; }
            .fc-scrollgrid { border-color: #334155 !important; }
            .fc-scrollgrid-sync-table td, .fc-scrollgrid-sync-table th { border-color: #334155 !important; }
            .fc-col-header-cell { background: #0f172a !important; }
            .fc-col-header-cell-cushion { color: #94a3b8 !important; }
            .fc-daygrid-day { background: #1e293b !important; }
            .fc-daygrid-day:hover { background: #273447 !important; }
            .fc-daygrid-day-number { color: #cbd5e1 !important; }
            .fc-day-today { background: #1a2a1a !important; }
            .fc-day-today .fc-daygrid-day-number { color: #f59e0b !important; font-weight: 800; }
            .fc-button { background: #334155 !important; border-color: #475569 !important; color: #e2e8f0 !important; }
            .fc-button:hover { background: #475569 !important; }
            .fc-button-primary:not(:disabled).fc-button-active { background: #f59e0b !important; border-color: #f59e0b !important; color: #1a1a2e !important; }
            .fc-toolbar-title { color: #f1f5f9 !important; }
            .fc-daygrid-more-link { color: #f59e0b !important; }
            .fc-popover { background: #1e293b !important; border-color: #334155 !important; }
            .fc-popover-header { background: #0f172a !important; color: #f1f5f9 !important; }

            /* ── Paksa light mode walaupun OS dalam dark mode ──────── */
            html.light body { background: #f3f4f6 !important; color: #1f2937 !important; }
            html.light header[role="banner"] { background: #ffffff !important; box-shadow: 0 1px 0 #e5e7eb, 0 2px 8px rgba(0,0,0,0.04) !important; }
            html.light .bg-white { background: #ffffff !important; }
            html.light .bg-gray-50 { background: #f9fafb !important; }
            html.light .bg-gray-100 { background: #f3f4f6 !important; }
            html.light .text-gray-800, html.light .text-gray-900 { color: #1f2937 !important; }
            html.light .text-gray-700 { color: #374151 !important; }
            html.light .text-gray-600 { color: #4b5563 !important; }
            html.light .text-gray-500 { color: #6b7280 !important; }
            html.light .text-gray-400 { color: #9ca3af !important; }
            html.light .border-gray-100 { border-color: #f3f4f6 !important; }
            html.light .border-gray-200 { border-color: #e5e7eb !important; }
            html.light .border-b, html.light .border-t { border-color: #e5e7eb !important; }
            html.light .divide-y > * + * { border-color: #e5e7eb !important; }
            html.light .form-input { background: #ffffff !important; border-color: #d1d5db !important; color: #1f2937 !important; }
            html.light .form-input::placeholder { color: #9ca3af !important; }
            html.light .form-label { color: #374151 !important; }
            html.light .form-hint { color: #6b7280 !important; }
            html.light .form-error { color: #dc2626 !important; }
            html.light select.form-input option { background: #ffffff; color: #1f2937; }
            html.light .table-header { background: #f9fafb !important; }
            html.light .table th { color: #374151 !important; border-bottom-color: #e5e7eb !important; }
            html.light .table td { color: #1f2937 !important; border-bottom-color: #f3f4f6 !important; }
            html.light .table tr:hover td { background: #f9fafb !important; }
            html.light .hover\:bg-gray-50:hover { background: #f9fafb !important; }
            html.light .hover\:bg-amber-50:hover { background: #fffbeb !important; }
            html.light .btn-secondary { background: #ffffff !important; color: #374151 !important; border-color: #d1d5db !important; }
            html.light .stat-card { background: #ffffff !important; }
            html.light .stat-card-v2 { background: #ffffff !important; }
            html.light .stat-action { border-top-color: #f3f4f6 !important; }
            html.light .stat-action:hover { background: #f9fafb !important; }
            html.light .progress-bar { background: #e5e7eb !important; }
            html.light .badge-lulus { background: #d1fae5 !important; color: #065f46 !important; }
            html.light .badge-tolak { background: #fee2e2 !important; color: #991b1b !important; }
            html.light .alert-success { background: #f0fdf4 !important; border-color: #86efac !important; color: #166534 !important; }
            html.light .alert-error { background: #fef2f2 !important; border-color: #fca5a5 !important; color: #991b1b !important; }
            html.light #event-modal > div { background: #ffffff !important; }
            html.light .bilik-btn { background: #ffffff !important; border-color: #e5e7eb !important; color: #374151 !important; }
            html.light .bilik-card { background: #ffffff !important; }
            html.light .kemudahan-tag { background: #f3f4f6 !important; color: #374151 !important; }
            html.light .flatpickr-calendar { background: #ffffff !important; box-shadow: 0 4px 20px rgba(0,0,0,.1) !important; }
            html.light .flatpickr-day { color: #1f2937 !important; }
            html.light .flatpickr-day:hover { background: #f3f4f6 !important; }
            html.light .flatpickr-months .flatpickr-month,
            html.light .flatpickr-weekdays,
            html.light span.flatpickr-weekday { background: #f9fafb !important; color: #374151 !important; fill: #374151 !important; }
            html.light .flatpickr-current-month input,
            html.light .flatpickr-current-month .numInputWrapper { color: #1f2937 !important; }
            html.light footer[role="contentinfo"] { background: #f9fafb !important; border-color: #e5e7eb !important; color: #6b7280 !important; }
            html.light .fc { color: #1f2937 !important; }
            html.light .fc-scrollgrid { border-color: #e5e7eb !important; }
            html.light .fc-scrollgrid-sync-table td, html.light .fc-scrollgrid-sync-table th { border-color: #e5e7eb !important; }
            html.light .fc-col-header-cell { background: #f9fafb !important; }
            html.light .fc-col-header-cell-cushion { color: #374151 !important; }
            html.light .fc-daygrid-day { background: #ffffff !important; }
            html.light .fc-daygrid-day-number { color: #374151 !important; }
            html.light .fc-day-today { background: #fef3c7 !important; }
            html.light .fc-button { background: #f3f4f6 !important; border-color: #d1d5db !important; color: #374151 !important; }
            html.light .fc-toolbar-title { color: #111827 !important; }
            html.light .fc-popover { background: #ffffff !important; border-color: #e5e7eb !important; }
            html.light .fc-popover-header { background: #f9fafb !important; color: #111827 !important; }
        }

        /* ══════════════════════════════════════════════════════════
           DARK MODE MANUAL — html.dark class (override OS setting)
           ══════════════════════════════════════════════════════════ */
        html.dark body { background: #0f172a !important; color: #e2e8f0 !important; }
        html.dark header[role="banner"] { background: #1e293b !important; box-shadow: 0 1px 3px rgba(0,0,0,.4) !important; }
        html.dark header[role="banner"] input[type="search"] { background: #334155 !important; color: #f1f5f9 !important; }
        html.dark header[role="banner"] input[type="search"]::placeholder { color: #64748b !important; }
        html.dark .bg-white { background: #1e293b !important; }
        html.dark .bg-gray-50 { background: #0f172a !important; }
        html.dark .bg-gray-100 { background: #334155 !important; }
        html.dark .shadow-sm { box-shadow: 0 1px 4px rgba(0,0,0,.35) !important; }
        html.dark .rounded-xl.shadow-sm, html.dark .rounded-xl.shadow { box-shadow: 0 2px 8px rgba(0,0,0,.4) !important; }
        html.dark .text-gray-800, html.dark .text-gray-900 { color: #f1f5f9 !important; }
        html.dark .text-gray-700 { color: #e2e8f0 !important; }
        html.dark .text-gray-600 { color: #cbd5e1 !important; }
        html.dark .text-gray-500 { color: #94a3b8 !important; }
        html.dark .text-gray-400 { color: #64748b !important; }
        html.dark .text-gray-300 { color: #475569 !important; }
        html.dark .border-gray-100 { border-color: #334155 !important; }
        html.dark .border-gray-200 { border-color: #475569 !important; }
        html.dark .border-b, html.dark .border-t { border-color: #334155 !important; }
        html.dark .divide-gray-50 > * + * { border-color: #334155 !important; }
        html.dark .divide-y > * + * { border-color: #334155 !important; }
        html.dark .form-input { background: #0f172a !important; border-color: #475569 !important; color: #f1f5f9 !important; }
        html.dark .form-input:focus { border-color: #f59e0b !important; }
        html.dark .form-input::placeholder { color: #64748b !important; }
        html.dark .form-label { color: #e2e8f0 !important; }
        html.dark .form-hint { color: #94a3b8 !important; }
        html.dark .form-error { color: #f87171 !important; }
        html.dark select.form-input option { background: #1e293b; color: #f1f5f9; }
        html.dark .table-header { background: #0f172a !important; }
        html.dark .table th { color: #cbd5e1 !important; border-bottom-color: #334155 !important; }
        html.dark .table td { color: #e2e8f0 !important; border-bottom-color: #1e293b !important; }
        html.dark .table tr:hover td { background: #273447 !important; }
        html.dark .hover\:bg-gray-50:hover { background: #273447 !important; }
        html.dark .hover\:bg-amber-50:hover { background: #1c1200 !important; }
        html.dark .btn-secondary { background: #334155 !important; color: #e2e8f0 !important; border-color: #475569 !important; }
        html.dark .btn-secondary:hover { background: #475569 !important; }
        html.dark .stat-card { background: #1e293b !important; }
        html.dark .stat-card-v2 { background: #1e293b !important; }
        html.dark .stat-action { border-top-color: #334155 !important; }
        html.dark .stat-action:hover { background: #273447 !important; }
        html.dark .progress-bar { background: #334155 !important; }
        html.dark .badge-lulus { background: #14532d !important; color: #86efac !important; }
        html.dark .badge-tolak { background: #7f1d1d !important; color: #fca5a5 !important; }
        html.dark .alert-success { background: #052e16 !important; border-color: #166534 !important; color: #86efac !important; }
        html.dark .alert-error { background: #450a0a !important; border-color: #991b1b !important; color: #fca5a5 !important; }
        html.dark #event-modal > div { background: #1e293b !important; }
        html.dark #event-modal dl { background: #1e293b !important; }
        html.dark #event-modal .px-6.py-4 { background: #1e293b !important; }
        html.dark .sidebar-link:hover { background: rgba(245,158,11,.2) !important; }
        html.dark .bilik-btn { background: #1e293b !important; border-color: #334155 !important; color: #e2e8f0 !important; }
        html.dark .bilik-btn:hover { background: #1c1a00 !important; border-color: #f59e0b !important; color: #f59e0b !important; }
        html.dark .bilik-btn.aktif { background: #1c1a00 !important; border-color: #f59e0b !important; color: #f59e0b !important; }
        html.dark .bilik-btn .text-gray-800 { color: #f1f5f9 !important; }
        html.dark .bilik-btn .text-gray-400 { color: #94a3b8 !important; }
        html.dark .bilik-card { background: #1e293b !important; }
        html.dark .kemudahan-tag { background: #334155 !important; color: #e2e8f0 !important; }
        html.dark .flatpickr-calendar { background: #1e293b !important; box-shadow: 0 4px 20px rgba(0,0,0,.5) !important; }
        html.dark .flatpickr-day { color: #e2e8f0 !important; }
        html.dark .flatpickr-day:hover { background: #334155 !important; }
        html.dark .flatpickr-day.selected, html.dark .flatpickr-day.selected:hover { background: #f59e0b !important; border-color: #f59e0b !important; color: #1a1a2e !important; }
        html.dark .flatpickr-day.today { border-color: #f59e0b !important; }
        html.dark .flatpickr-day.disabled { color: #475569 !important; }
        html.dark .flatpickr-months .flatpickr-month,
        html.dark .flatpickr-weekdays,
        html.dark span.flatpickr-weekday { background: #0f172a !important; color: #94a3b8 !important; fill: #94a3b8 !important; }
        html.dark .flatpickr-current-month input,
        html.dark .flatpickr-current-month .numInputWrapper,
        html.dark .flatpickr-current-month span.arrowUp,
        html.dark .flatpickr-current-month span.arrowDown { color: #f1f5f9 !important; }
        html.dark .numInputWrapper:hover { background: #334155 !important; }
        html.dark .flatpickr-prev-month svg, html.dark .flatpickr-next-month svg { fill: #94a3b8 !important; }
        html.dark .flatpickr-prev-month:hover svg, html.dark .flatpickr-next-month:hover svg { fill: #f59e0b !important; }
        html.dark footer[role="contentinfo"] { background: #0f172a !important; border-color: #1e293b !important; color: #475569 !important; }
        html.dark footer[role="contentinfo"] a:hover { color: #f59e0b !important; }
        html.dark .fc { color: #e2e8f0 !important; }
        html.dark .fc-scrollgrid { border-color: #334155 !important; }
        html.dark .fc-scrollgrid-sync-table td, html.dark .fc-scrollgrid-sync-table th { border-color: #334155 !important; }
        html.dark .fc-col-header-cell { background: #0f172a !important; }
        html.dark .fc-col-header-cell-cushion { color: #94a3b8 !important; }
        html.dark .fc-daygrid-day { background: #1e293b !important; }
        html.dark .fc-daygrid-day:hover { background: #273447 !important; }
        html.dark .fc-daygrid-day-number { color: #cbd5e1 !important; }
        html.dark .fc-day-today { background: #1a2a1a !important; }
        html.dark .fc-day-today .fc-daygrid-day-number { color: #f59e0b !important; font-weight: 800; }
        html.dark .fc-button { background: #334155 !important; border-color: #475569 !important; color: #e2e8f0 !important; }
        html.dark .fc-button:hover { background: #475569 !important; }
        html.dark .fc-button-primary:not(:disabled).fc-button-active { background: #f59e0b !important; border-color: #f59e0b !important; color: #1a1a2e !important; }
        html.dark .fc-toolbar-title { color: #f1f5f9 !important; }
        html.dark .fc-daygrid-more-link { color: #f59e0b !important; }
        html.dark .fc-popover { background: #1e293b !important; border-color: #334155 !important; }
        html.dark .fc-popover-header { background: #0f172a !important; color: #f1f5f9 !important; }

        /* ══════════════════════════════════════════════════════════
           LIGHT MODE SIDEBAR — html.light class
           ══════════════════════════════════════════════════════════ */
        html.light .sidebar { background: #f8fafc !important; border-right: 1px solid #e2e8f0 !important; }
        html.light .sidebar-link { color: #374151 !important; }
        html.light .sidebar-link:hover { background: rgba(245,158,11,.12) !important; color: #b45309 !important; }
        html.light .sidebar-link[aria-current="page"] { background: rgba(245,158,11,.18) !important; color: #b45309 !important; border-right-color: #f59e0b !important; }
        html.light #sidebar-utama .text-white { color: #1e293b !important; }
        html.light #sidebar-utama .text-slate-300 { color: #475569 !important; }
        html.light #sidebar-utama .text-slate-400 { color: #64748b !important; }
        html.light #sidebar-utama .text-slate-500 { color: #6b7280 !important; }
        html.light #sidebar-utama .border-b { border-color: #e2e8f0 !important; }

        /* ══════════════════════════════════════════════════════════
           LIGHT MODE — Halaman spesifik (ketersediaan, pengguna)
           ══════════════════════════════════════════════════════════ */

        /* ── Ketersediaan: Panel carian ─────────────────────────── */
        html.light #panel-carian { background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%) !important; }
        html.light #panel-carian .form-label { color: #94a3b8 !important; }
        html.light #panel-carian .form-hint { color: #64748b !important; }

        /* ── Ketersediaan: Navigasi minggu ──────────────────────── */
        html.light .nav-btn-minggu { background: #f3f4f6 !important; border-color: #d1d5db !important; color: #374151 !important; }
        html.light .nav-btn-minggu:hover { background: #e5e7eb !important; border-color: #9ca3af !important; color: #111827 !important; }
        html.light .nav-btn-minggu.ini { background: #fef3c7 !important; border-color: #f59e0b !important; color: #b45309 !important; }

        /* ── Ketersediaan: Jadual minggu ────────────────────────── */
        html.light .bilik-header { background: #eef2f7 !important; color: #374151 !important; }
        html.light .bilik-subheader { background: #eef2f7 !important; }
        html.light .bilik-nama-cell { background: #ffffff !important; color: #1e293b !important; border-right-color: #e2e8f0 !important; }
        html.light .row-alt .bilik-nama-cell { background: #f8fafc !important; color: #1e293b !important; }
        html.light .slot-cell { background: #ffffff !important; }
        html.light .row-alt .slot-cell { background: #f8fafc !important; }
        html.light .hari-header { background: #e2e8f0 !important; }
        html.light .hari-nama { color: #1e293b !important; }
        html.light .hari-tarikh { color: #64748b !important; }
        html.light .hari-header.hari-ini { background: #dbeafe !important; }
        html.light .hari-header.hari-ini .hari-nama { color: #1d4ed8 !important; }
        html.light .hari-header.hari-ini .hari-tarikh { color: #3b82f6 !important; }
        html.light .sesi-subheader { background: #e8edf5 !important; color: #64748b !important; }
        html.light .sehari-subheader { background: #e8edf5 !important; color: #94a3b8 !important; border-left-color: #bfdbfe !important; }
        html.light .slot-cell.sehari-col { border-left-color: #bfdbfe !important; }
        html.light #tbl-minggu th,
        html.light #tbl-minggu td { border-color: #e2e8f0 !important; }

        /* ── Ketersediaan: Slot chips ────────────────────────────── */
        html.light .slot-chip.kosong { background: #dcfce7 !important; color: #16a34a !important; border-color: #86efac !important; }
        html.light .slot-chip.kosong:hover { background: #16a34a !important; color: #fff !important; }
        html.light .slot-chip.penuh { background: #fee2e2 !important; color: #dc2626 !important; border-color: #fca5a5 !important; }
        html.light .slot-chip.tiada { background: #f1f5f9 !important; color: #9ca3af !important; border-color: #e2e8f0 !important; }
        html.light .slot-chip.sehari { background: #dbeafe !important; color: #1d4ed8 !important; border-color: #93c5fd !important; }
        html.light .slot-chip.sehari:hover { background: #2563eb !important; color: #fff !important; }
        html.light .slot-chip.sehari-off { background: #f1f5f9 !important; color: #cbd5e1 !important; border-color: #e2e8f0 !important; }

        /* ── Pengguna: Tab aktif ─────────────────────────────────── */
        html.light .tab-btn.aktif-tab { background: #1e293b !important; color: #f8fafc !important; }
        html.light .tab-btn:hover:not(.aktif-tab) { background: #f3f4f6 !important; color: #374151 !important; }
    </style>
</head>
<body>

    {{-- ── Skip Navigation (WCAG 2.4.1) ──────────────────────────── --}}
    <a href="#kandungan-utama" class="skip-link">Langkau ke kandungan utama</a>
    <a href="#nav-utama" class="skip-link" style="left:220px">Langkau ke navigasi</a>

{{-- Overlay untuk mobile sidebar --}}
<div id="sidebar-overlay" aria-hidden="true"></div>

<div class="flex">

    {{-- ── Sidebar / Navigasi Utama ──────────────────────────────── --}}
    <aside id="sidebar-utama" class="sidebar fixed top-0 left-0 z-30" aria-label="Bar sisi navigasi">

        {{-- Logo & Branding Jabatan --}}
        @php
            $namaSistem  = $tetapan['nama_sistem']  ?? 'iBook 2.0';
            $namaJabatan = $tetapan['nama_jabatan'] ?? '';
            // Guna logo dari tetapan, atau default ke Jata Negara jika tiada
            $logoJabatan = $tetapan['logo_jabatan'] ?? '/images/jata-negara.png';
            if (empty($logoJabatan)) $logoJabatan = '/images/jata-negara.png';
        @endphp

        {{-- Strip jabatan di bahagian atas sidebar --}}
        @if($namaJabatan || $logoJabatan)
        <div class="px-4 py-3 border-b border-slate-700/60" style="background:rgba(245,158,11,0.06)">
            <div class="flex items-center gap-3">
                @if($logoJabatan)
                <img src="{{ $logoJabatan }}" alt="Logo {{ $namaJabatan }}"
                     class="object-contain flex-shrink-0" style="height:48px; width:auto">
                @else
                <div class="rounded flex items-center justify-center flex-shrink-0"
                     style="width:48px;height:48px;background:rgba(245,158,11,0.15)">
                    <i class="fa-solid fa-landmark text-amber-400 text-lg" aria-hidden="true"></i>
                </div>
                @endif
                <span class="text-slate-300 leading-tight font-medium" style="font-size:11px; line-height:1.3">
                    {{ $namaJabatan ?: 'Bahagian Pengurusan Teknologi Maklumat' }}
                </span>
            </div>
        </div>
        @endif

        {{-- Nama sistem --}}
        <div class="p-5 border-b border-slate-700">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3"
               aria-label="{{ $namaSistem }} — Halaman Utama">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--accent)" aria-hidden="true">
                    <i class="fa-solid fa-book-open text-white text-sm"></i>
                </div>
                <div>
                    <span class="text-white font-bold block" style="font-size:14px; letter-spacing:-0.01em">{{ $namaSistem }}</span>
                    <span class="text-slate-400 block" style="font-size:10px; margin-top:1px">Sistem Tempahan Bilik Mesyuarat</span>
                </div>
            </a>
        </div>

        {{-- Nav menu --}}
        <nav id="nav-utama" aria-label="Menu utama">
            <ul role="list" class="py-4 space-y-0.5">

                {{-- ── Kumpulan: Operasi ────────────────────────── --}}
                <li role="separator" aria-hidden="true">
                    <p class="px-8 pb-1 pt-2 text-[10px] text-slate-500 uppercase tracking-widest font-semibold">Operasi</p>
                </li>
                <li>
                    <a href="{{ route('dashboard') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('dashboard') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-table-columns w-5" aria-hidden="true"></i>
                        <span>Papan Pemuka</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('kalendar') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('kalendar*') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-calendar-days w-5" aria-hidden="true"></i>
                        <span>Kalendar</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('tempahan.create') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('tempahan.create') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-circle-plus w-5" aria-hidden="true"></i>
                        <span>Tempahan Baru</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('tempahan.index') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('tempahan.index') || request()->routeIs('tempahan.show') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-list w-5" aria-hidden="true"></i>
                        <span>Senarai Tempahan</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('ketersediaan') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('ketersediaan*') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-magnifying-glass-location w-5" aria-hidden="true"></i>
                        <span>Semak Bilik Kosong</span>
                    </a>
                </li>

                {{-- ── Kumpulan: Analitik ───────────────────────── --}}
                <li role="separator" aria-hidden="true">
                    <p class="px-8 pb-1 pt-3 text-[10px] text-slate-500 uppercase tracking-widest font-semibold">Analitik</p>
                </li>
                <li>
                    <a href="{{ route('laporan') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('laporan*') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-chart-bar w-5" aria-hidden="true"></i>
                        <span>Laporan</span>
                    </a>
                </li>

                @if(auth()->user()->isPentadbir() || auth()->user()->isUrusSetia())
                {{-- ── Kumpulan: Pentadbiran ────────────────────── --}}
                <li role="separator" aria-hidden="true">
                    <p class="px-8 pb-1 pt-3 text-[10px] text-slate-500 uppercase tracking-widest font-semibold">Pentadbiran</p>
                </li>
                @if(auth()->user()->isPentadbir())
                <li>
                    <a href="{{ route('bilik.index') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('bilik*') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-door-open w-5" aria-hidden="true"></i>
                        <span>Bilik Mesyuarat</span>
                    </a>
                </li>
                @endif
                <li>
                    <a href="{{ route('pengguna.index') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('pengguna*') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-users w-5" aria-hidden="true"></i>
                        <span>Pengguna</span>
                    </a>
                </li>
                @if(auth()->user()->isPentadbir())
                <li>
                    <a href="{{ route('audit.index') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('audit*') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-shield-halved w-5" aria-hidden="true"></i>
                        <span>Log Audit</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('tetapan.index') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('tetapan*') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-gear w-5" aria-hidden="true"></i>
                        <span>Tetapan</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('backup.index') }}"
                       class="sidebar-link"
                       {{ request()->routeIs('backup*') ? 'aria-current=page' : '' }}>
                        <i class="fa-solid fa-database w-5" aria-hidden="true"></i>
                        <span>Backup</span>
                        @php
                            try {
                                $backupSvc = app(\App\Services\BackupService::class);
                                if ($backupSvc->adaBackupTertunggak()):
                        @endphp
                        <span class="ml-auto w-2 h-2 rounded-full bg-red-500 animate-pulse flex-shrink-0" aria-label="Backup tertunggak" title="Backup tertunggak"></span>
                        @php endif; } catch (\Throwable $e) {} @endphp
                    </a>
                </li>
                @endif
                @endif
            </ul>
        </nav>
    </aside>

    {{-- ── Kawasan Kandungan Utama ─────────────────────────────── --}}
    <div class="flex-1 lg:ml-[260px]">

        {{-- Top bar --}}
        <header class="bg-white sticky top-0 z-20" role="banner" style="box-shadow:0 1px 0 #e5e7eb, 0 2px 8px rgba(0,0,0,0.04)">

            {{-- Government identity strip --}}
            @if($namaJabatan)
            <div class="px-6 py-1.5 border-b border-gray-100 flex items-center gap-2" style="background:#fafafa">
                @if($logoJabatan)
                <img src="{{ $logoJabatan }}" alt="" class="h-5 w-5 object-contain" aria-hidden="true">
                @else
                <i class="fa-solid fa-landmark text-amber-500 text-xs" aria-hidden="true"></i>
                @endif
                <span class="text-xs font-semibold text-gray-500 tracking-wide uppercase" style="font-size:10px; letter-spacing:0.06em">
                    {{ $namaJabatan }}
                </span>
            </div>
            @endif

            {{-- Main header row --}}
            <div class="px-4 lg:px-6 py-3 flex items-center justify-between gap-3">

            {{-- Hamburger button (mobile sahaja) --}}
            <button type="button" id="btn-hamburger"
                    class="lg:hidden flex-shrink-0 w-9 h-9 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors"
                    aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="sidebar-utama">
                <i class="fa-solid fa-bars text-base" aria-hidden="true"></i>
            </button>

            {{-- Carian Global --}}
            <form method="GET" action="{{ route('carian') }}" role="search" aria-label="Carian sistem merentas semua modul" class="flex-1 lg:flex-none">
                <div class="relative">
                    <label for="carian-global" class="sr-only">Cari tempahan, bilik atau pengguna</label>
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm" aria-hidden="true"></i>
                    <input type="search" id="carian-global" name="q"
                        value="{{ request()->routeIs('carian') ? request('q') : '' }}"
                        placeholder="Cari semua modul…"
                        class="pl-9 pr-16 py-2 bg-gray-100 rounded-lg text-sm w-full lg:w-72 focus:outline-none focus:bg-white focus:ring-2 focus:ring-amber-400 transition-all"
                        aria-label="Carian global — tekan / untuk fokus"
                        autocomplete="off">
                    {{-- Hint pintasan papan kekunci --}}
                    <kbd id="search-hint"
                         class="absolute right-3 top-1/2 -translate-y-1/2 hidden sm:flex items-center gap-0.5 text-[10px] text-gray-400 font-mono border border-gray-300 rounded px-1.5 py-0.5 pointer-events-none select-none"
                         aria-hidden="true"
                         title="Tekan / untuk fokus ke carian">
                        /
                    </kbd>
                </div>
            </form>

            {{-- Profil & tindakan --}}
            <div class="flex items-center gap-4">

                {{-- Toggle Tema: Light / Dark --}}
                <button type="button" id="btn-toggle-tema"
                    class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-400"
                    aria-label="Tukar tema cerah/gelap" title="Tukar tema">
                    <i id="icon-tema" class="fa-solid fa-circle-half-stroke text-sm" aria-hidden="true"></i>
                </button>

                {{-- Maklumat pengguna + dropdown --}}
                <div class="relative flex items-center gap-3" id="profil-dropdown-wrap">
                    <div class="text-right hidden lg:block" aria-hidden="true">
                        <div class="font-semibold text-sm text-gray-800">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-gray-500">{{ auth()->user()->label_peranan }}</div>
                    </div>

                    {{-- Avatar — klik untuk dropdown --}}
                    <button type="button" id="profil-btn"
                            class="w-9 h-9 rounded-full flex items-center justify-center font-bold focus:outline-none focus:ring-2 focus:ring-amber-400"
                            style="background:var(--accent); color:#1a1a2e;"
                            aria-haspopup="menu" aria-expanded="false"
                            aria-label="Menu profil {{ auth()->user()->name }}">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </button>

                    {{-- Dropdown menu --}}
                    <div id="profil-menu"
                         class="hidden absolute right-0 top-12 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
                         role="menu" aria-labelledby="profil-btn">
                        <a href="{{ route('profil') }}"
                           class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition-colors"
                           role="menuitem">
                            <i class="fa-solid fa-user-pen w-4" aria-hidden="true"></i> Profil Saya
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors"
                                    role="menuitem"
                                    aria-label="Log keluar daripada akaun {{ auth()->user()->name }}">
                                <i class="fa-solid fa-right-from-bracket w-4" aria-hidden="true"></i> Log Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            </div>{{-- end main header row --}}
        </header>

        {{-- Kandungan utama --}}
        <main id="kandungan-utama" class="p-4 lg:p-6" tabindex="-1">

            {{-- Alert mesej --}}
            @if(session('success'))
            <div role="alert" aria-live="polite" class="alert-success flex items-center gap-2">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif
            @if(session('error'))
            <div role="alert" aria-live="assertive" class="alert-error flex items-center gap-2">
                <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            @yield('content')
        </main>

        {{-- Footer --}}
        @php
            $namaBahagian  = $tetapan['nama_jabatan'] ?? '';
            $emelPentadbir = $tetapan['emel_pentadbir'] ?? '';
            $tahunSemasa   = date('Y');
        @endphp
        @if($namaBahagian || $emelPentadbir)
        <footer class="border-t border-gray-200 px-6 py-4 text-center"
                style="background:#f9fafb"
                role="contentinfo">
            <div class="flex flex-col sm:flex-row items-center justify-center gap-2 text-xs text-gray-400">
                @if($namaBahagian)
                <span>
                    Hak Cipta &copy; {{ $namaBahagian }} {{ $tahunSemasa }}
                </span>
                @endif
                @if($namaBahagian && $emelPentadbir)
                <span class="hidden sm:inline text-gray-300" aria-hidden="true">|</span>
                @endif
                @if($emelPentadbir)
                <span>
                    <i class="fa-solid fa-envelope text-gray-300 mr-1" aria-hidden="true"></i>
                    <a href="mailto:{{ $emelPentadbir }}"
                       class="hover:text-amber-500 transition-colors"
                       aria-label="Hubungi pentadbir sistem">{{ $emelPentadbir }}</a>
                </span>
                @endif
            </div>
        </footer>
        @endif
    </div>
</div>

@stack('scripts')
<script nonce="{{ $cspNonce }}">
// ── Pintasan papan kekunci: tekan "/" untuk fokus carian global ──
(function () {
    const input = document.getElementById('carian-global');
    const hint  = document.getElementById('search-hint');
    if (!input) return;

    // Sembunyikan hint bila input aktif
    input.addEventListener('focus', () => hint && (hint.style.display = 'none'));
    input.addEventListener('blur',  () => hint && (hint.style.display = ''));

    document.addEventListener('keydown', function (e) {
        // Tekan "/" bila tiada input aktif
        if (e.key === '/' && document.activeElement.tagName !== 'INPUT'
                          && document.activeElement.tagName !== 'TEXTAREA'
                          && document.activeElement.tagName !== 'SELECT') {
            e.preventDefault();
            input.focus();
            input.select();
        }
        // Tekan Esc untuk batalkan fokus
        if (e.key === 'Escape' && document.activeElement === input) {
            input.blur();
        }
    });
})();

// ── Dropdown profil ──────────────────────────────────────
function toggleProfilMenu() {
    const menu = document.getElementById('profil-menu');
    const btn  = document.getElementById('profil-btn');
    const open = menu.classList.toggle('hidden');
    btn.setAttribute('aria-expanded', !open);
}
// Alert dismiss (event delegation — handles all .js-dismiss-alert buttons)
document.addEventListener('click', function(e) {
    if (e.target.closest('.js-dismiss-alert')) {
        e.target.closest('[role=alert]')?.remove();
    }
});

// ── Mobile Sidebar Toggle ────────────────────────────────
(function () {
    const btnHamburger = document.getElementById('btn-hamburger');
    const sidebar      = document.getElementById('sidebar-utama');
    const overlay      = document.getElementById('sidebar-overlay');
    if (!btnHamburger || !sidebar || !overlay) return;

    function bukaMenu() {
        sidebar.classList.add('mobile-open');
        overlay.classList.add('aktif');
        btnHamburger.setAttribute('aria-expanded', 'true');
        btnHamburger.innerHTML = '<i class="fa-solid fa-xmark text-base" aria-hidden="true"></i>';
        document.body.style.overflow = 'hidden'; // Halang skrol latar
    }

    function tutupMenu() {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('aktif');
        btnHamburger.setAttribute('aria-expanded', 'false');
        btnHamburger.innerHTML = '<i class="fa-solid fa-bars text-base" aria-hidden="true"></i>';
        document.body.style.overflow = '';
    }

    btnHamburger.addEventListener('click', function () {
        sidebar.classList.contains('mobile-open') ? tutupMenu() : bukaMenu();
    });

    // Tutup bila klik overlay
    overlay.addEventListener('click', tutupMenu);

    // Tutup bila tekan Esc
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar.classList.contains('mobile-open')) tutupMenu();
    });

    // Tutup bila klik link dalam sidebar (navigasi)
    sidebar.querySelectorAll('.sidebar-link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth < 1024) tutupMenu();
        });
    });
})();

// Wire profil button (CSP-safe — tiada onclick di HTML)
document.getElementById('profil-btn')?.addEventListener('click', function(e) {
    e.stopPropagation();
    toggleProfilMenu();
});
// Tutup bila klik luar
document.addEventListener('click', function(e) {
    const wrap = document.getElementById('profil-dropdown-wrap');
    if (wrap && !wrap.contains(e.target)) {
        document.getElementById('profil-menu')?.classList.add('hidden');
        document.getElementById('profil-btn')?.setAttribute('aria-expanded', 'false');
    }
});

// ── Idle Session Timeout ─────────────────────────────────────────
// Amaran selepas 25 minit tidak aktif, log keluar selepas 30 minit.
(function () {
    const IDLE_WARN_MS   = 25 * 60 * 1000; // 25 minit → tunjuk amaran
    const IDLE_LOGOUT_MS = 30 * 60 * 1000; // 30 minit → log keluar
    const LOGOUT_URL     = '{{ route("logout") }}';
    const CSRF           = document.querySelector('meta[name="csrf-token"]')?.content || '';

    let warnTimer   = null;
    let logoutTimer = null;
    let warnShown   = false;

    // Cipta modal amaran (dimasukkan sekali ke DOM)
    const modalHtml = `
    <div id="idle-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.6);align-items:center;justify-content:center">
        <div style="background:#fff;border-radius:16px;padding:32px;max-width:380px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.3)">
            <div style="width:56px;height:56px;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                <i class="fa-solid fa-clock" style="font-size:24px;color:#f59e0b"></i>
            </div>
            <h3 style="font-weight:700;font-size:18px;color:#1f2937;margin:0 0 8px">Sesi Hampir Tamat</h3>
            <p style="color:#6b7280;font-size:14px;margin:0 0 4px">Anda tidak aktif selama <strong>25 minit</strong>.</p>
            <p id="idle-countdown" style="color:#dc2626;font-size:13px;font-weight:600;margin:0 0 24px">Log keluar dalam 5:00</p>
            <button id="idle-teruskan"
                style="background:#f59e0b;color:#1a1a2e;border:none;border-radius:8px;padding:10px 24px;font-weight:700;font-size:14px;cursor:pointer;width:100%">
                Teruskan Sesi
            </button>
        </div>
    </div>`;
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    const modal     = document.getElementById('idle-modal');
    const btnTerus  = document.getElementById('idle-teruskan');
    const countEl   = document.getElementById('idle-countdown');

    let countdownInterval = null;
    let countdownSecs     = 300; // 5 minit

    function mulaKira() {
        countdownSecs = 300;
        clearInterval(countdownInterval);
        countdownInterval = setInterval(function () {
            countdownSecs--;
            const m = Math.floor(countdownSecs / 60);
            const s = countdownSecs % 60;
            if (countEl) countEl.textContent = 'Log keluar dalam ' + m + ':' + String(s).padStart(2, '0');
        }, 1000);
    }

    function tunjukAmaran() {
        warnShown = true;
        modal.style.display = 'flex';
        mulaKira();
    }

    function sembunyiAmaran() {
        warnShown = false;
        modal.style.display = 'none';
        clearInterval(countdownInterval);
    }

    function logKeluar() {
        // POST ke route logout (CSRF-safe)
        const f = document.createElement('form');
        f.method = 'POST';
        f.action = LOGOUT_URL;
        const t = document.createElement('input');
        t.type = 'hidden'; t.name = '_token'; t.value = CSRF;
        f.appendChild(t);
        document.body.appendChild(f);
        f.submit();
    }

    function resetTimer() {
        if (warnShown) return; // jangan reset jika modal amaran sudah tunjuk
        clearTimeout(warnTimer);
        clearTimeout(logoutTimer);
        warnTimer   = setTimeout(tunjukAmaran, IDLE_WARN_MS);
        logoutTimer = setTimeout(logKeluar,    IDLE_LOGOUT_MS);
    }

    // Rekod aktiviti pengguna
    ['mousemove', 'keydown', 'click', 'touchstart', 'scroll'].forEach(function (ev) {
        document.addEventListener(ev, resetTimer, { passive: true });
    });

    // Butang "Teruskan Sesi"
    if (btnTerus) {
        btnTerus.addEventListener('click', function () {
            sembunyiAmaran();
            resetTimer();
            // Refresh CSRF token secara senyap (optional — elak token expire)
            fetch('/sanctum/csrf-cookie', { credentials: 'same-origin' }).catch(() => {});
        });
    }

    // Mulakan timer
    resetTimer();
})();

// ── Toggle Tema: Light / Dark ─────────────────────────────────────
(function () {
    const html = document.documentElement;
    const btn  = document.getElementById('btn-toggle-tema');
    const icon = document.getElementById('icon-tema');
    const mq   = window.matchMedia('(prefers-color-scheme: dark)');

    function isDarkActive() {
        return html.classList.contains('dark') ||
            (!html.classList.contains('light') && mq.matches);
    }

    function updateIcon() {
        if (!icon) return;
        // Tunjuk ikon bertentangan dengan mod semasa
        icon.className = isDarkActive()
            ? 'fa-solid fa-sun text-sm'    // kini gelap → tunjuk matahari (tukar ke terang)
            : 'fa-solid fa-moon text-sm';  // kini terang → tunjuk bulan (tukar ke gelap)
    }

    function toggleTema() {
        if (isDarkActive()) {
            html.classList.remove('dark');
            html.classList.add('light');
            try { localStorage.setItem('ibook-theme', 'light'); } catch(e) {}
        } else {
            html.classList.remove('light');
            html.classList.add('dark');
            try { localStorage.setItem('ibook-theme', 'dark'); } catch(e) {}
        }
        updateIcon();
    }

    if (btn) btn.addEventListener('click', toggleTema);
    updateIcon();

    // Kemaskini ikon bila OS tukar tema (jika pengguna ikut auto)
    mq.addEventListener('change', updateIcon);
})();
</script>
</body>
</html>
