<?php
/**
 * fix-light-mode-v4.php
 * Pendekatan baru: inject <style> blok terus sebelum </head>.
 * Blok ini adalah CSS TERAKHIR dalam <head> → mengatasi semua CSS lain.
 * Meliputi: header, sidebar, ketersediaan, pengguna, umum.
 *
 * URL: https://ibookbptm.great-site.net/fix-light-mode-v4.php?key=ibook2026deploy
 */
if (($_GET['key'] ?? '') !== 'ibook2026deploy') {
    http_response_code(403);
    die('Forbidden');
}

$file   = __DIR__ . '/resources/views/layouts/app.blade.php';
$errors = [];
$log    = [];

if (!file_exists($file)) {
    $errors[] = "FAIL: $file tidak dijumpai";
    goto output;
}

$content = file_get_contents($file);

/* Semak jika sudah ditampal */
if (strpos($content, 'ibook-lm-v4') !== false) {
    $log[] = 'SKIP sudah ditampal (ibook-lm-v4)';
    goto output;
}

/* ── CSS blok baru ──────────────────────────────────────────── */
$css = <<<'ENDCSS'
{{-- ibook-lm-v4: Light mode overrides — diletakkan terakhir supaya mengatasi semua CSS lain --}}
<style id="ibook-lm-v4">
/* ═══ HEADER putih dalam html.light ═══════════════════════════ */
html.light header[role="banner"] {
    background: #ffffff !important;
    box-shadow: 0 1px 0 #e5e7eb, 0 2px 8px rgba(0,0,0,0.04) !important;
}
html.light header[role="banner"] input[type="search"] {
    background: #f3f4f6 !important; color: #1f2937 !important;
}
html.light header[role="banner"] input[type="search"]::placeholder { color: #9ca3af !important; }
html.light header[role="banner"] .text-gray-800,
html.light header[role="banner"] .text-gray-900 { color: #111827 !important; }
html.light header[role="banner"] .text-gray-600,
html.light header[role="banner"] .text-gray-500 { color: #6b7280 !important; }
html.light header[role="banner"] .text-gray-400 { color: #9ca3af !important; }
html.light header[role="banner"] .hover\:bg-gray-100:hover { background: #f3f4f6 !important; }

/* ═══ SIDEBAR cerah dalam html.light ══════════════════════════ */
html.light .sidebar { background: #f8fafc !important; border-right: 1px solid #e2e8f0 !important; }
html.light .sidebar-link { color: #374151 !important; }
html.light .sidebar-link:hover { background: rgba(245,158,11,.12) !important; color: #b45309 !important; }
html.light .sidebar-link[aria-current="page"] {
    background: rgba(245,158,11,.18) !important;
    color: #b45309 !important;
    border-right-color: #f59e0b !important;
}
html.light #sidebar-utama .text-white  { color: #1e293b !important; }
html.light #sidebar-utama .text-slate-300 { color: #475569 !important; }
html.light #sidebar-utama .text-slate-400 { color: #64748b !important; }
html.light #sidebar-utama .text-slate-500 { color: #6b7280 !important; }
html.light #sidebar-utama .border-b { border-color: #e2e8f0 !important; }

/* ═══ KETERSEDIAAN — Panel carian ═════════════════════════════ */
html.light #panel-carian {
    background: #f1f5f9 !important;
    border-radius: 16px;
}
html.light #panel-carian .form-label { color: #374151 !important; }
html.light #panel-carian .form-hint  { color: #6b7280 !important; }

/* ═══ KETERSEDIAAN — Navigasi minggu ══════════════════════════ */
html.light .nav-btn-minggu {
    background: #f3f4f6 !important;
    border-color: #d1d5db !important;
    color: #374151 !important;
}
html.light .nav-btn-minggu:hover {
    background: #e5e7eb !important;
    border-color: #9ca3af !important;
    color: #111827 !important;
}
html.light .nav-btn-minggu.ini {
    background: #fef3c7 !important;
    border-color: #f59e0b !important;
    color: #b45309 !important;
}

/* ═══ KETERSEDIAAN — Jadual minggu ════════════════════════════ */
html.light #tbl-minggu th,
html.light #tbl-minggu td { border-color: #e2e8f0 !important; }

html.light .bilik-header {
    background: #eef2f7 !important;
    color: #374151 !important;
}
html.light .bilik-subheader { background: #eef2f7 !important; }

html.light .bilik-nama-cell {
    background: #ffffff !important;
    color: #1e293b !important;
    border-right-color: #e2e8f0 !important;
}
html.light .row-alt .bilik-nama-cell {
    background: #f8fafc !important;
    color: #1e293b !important;
}
html.light .slot-cell            { background: #ffffff !important; }
html.light .row-alt .slot-cell   { background: #f8fafc !important; }

html.light .hari-header          { background: #dde3ec !important; }
html.light .hari-nama            { color: #1e293b !important; }
html.light .hari-tarikh          { color: #64748b !important; }
html.light .hari-header.hari-ini { background: #dbeafe !important; }
html.light .hari-header.hari-ini .hari-nama   { color: #1d4ed8 !important; }
html.light .hari-header.hari-ini .hari-tarikh { color: #3b82f6 !important; }

html.light .sesi-subheader  {
    background: #eef2f7 !important;
    color: #64748b !important;
}
html.light .sehari-subheader {
    background: #eef2f7 !important;
    color: #94a3b8 !important;
    border-left-color: #bfdbfe !important;
}
html.light .slot-cell.sehari-col { border-left-color: #bfdbfe !important; }

/* ═══ KETERSEDIAAN — Slot chips ═══════════════════════════════ */
html.light .slot-chip.kosong {
    background: #dcfce7 !important; color: #16a34a !important; border-color: #86efac !important;
}
html.light .slot-chip.kosong:hover {
    background: #16a34a !important; color: #fff !important;
}
html.light .slot-chip.penuh {
    background: #fee2e2 !important; color: #dc2626 !important; border-color: #fca5a5 !important;
}
html.light .slot-chip.tiada {
    background: #f1f5f9 !important; color: #9ca3af !important; border-color: #e2e8f0 !important;
}
html.light .slot-chip.sehari {
    background: #dbeafe !important; color: #1d4ed8 !important; border-color: #93c5fd !important;
}
html.light .slot-chip.sehari:hover { background: #2563eb !important; color: #fff !important; }
html.light .slot-chip.sehari-off {
    background: #f1f5f9 !important; color: #cbd5e1 !important; border-color: #e2e8f0 !important;
}

/* ═══ PENGGUNA — Tab aktif ════════════════════════════════════ */
html.light .tab-btn.aktif-tab { background: #1e293b !important; color: #f8fafc !important; }
html.light .tab-btn:hover:not(.aktif-tab) { background: #f3f4f6 !important; color: #374151 !important; }
</style>
</head>
ENDCSS;

/* Gantikan </head> dengan blok CSS baru + </head> */
if (strpos($content, '</head>') === false) {
    $errors[] = 'WARN: </head> tidak dijumpai dalam fail';
} else {
    $content = str_replace('</head>', $css, $content);
    file_put_contents($file, $content);
    $log[] = 'OK app.blade.php (' . strlen($content) . ' bytes) — blok css v4 ditambah';
}

output:
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>fix-light-mode-v4</title>
<style>body{font-family:monospace;padding:24px;background:#0f172a;color:#e2e8f0}
.ok{color:#4ade80}.warn{color:#fb923c}.err{color:#f87171}
h2{color:#f59e0b;margin-bottom:16px}.done{font-size:1.3em;font-weight:bold;margin-top:20px}
</style></head><body>';
echo '<h2>fix-light-mode-v4.php</h2><pre>';
foreach ($log as $l) {
    $cls = str_starts_with($l, 'OK') ? 'ok' : 'warn';
    echo '<span class="'.$cls.'">'.htmlspecialchars($l).'</span>'."\n";
}
foreach ($errors as $e) {
    $cls = str_starts_with($e, 'FAIL') ? 'err' : 'warn';
    echo '<span class="'.$cls.'">'.htmlspecialchars($e).'</span>'."\n";
}
echo '</pre>';
$ok = empty($errors);
echo '<div class="done" style="color:'.($ok?'#4ade80':'#fb923c').'">'
    .($ok?'&#10003; BERJAYA':'&#9888; ADA AMARAN').'</div>';
echo '</body></html>';
