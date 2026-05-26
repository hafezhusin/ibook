<?php
/**
 * PATCH: fix-header-light-mode.php  v3
 * Light mode penuh: header putih + sidebar cerah + ketersediaan & pengguna pages.
 * Jalankan: https://ibookbptm.great-site.net/fix-header-light-mode.php?key=ibook2026deploy
 */
if (($_GET['key'] ?? '') !== 'ibook2026deploy') {
    http_response_code(403);
    die('Forbidden');
}

$viewPath = __DIR__ . '/resources/views';
$errors   = [];
$log      = [];

$file = $viewPath . '/layouts/app.blade.php';
if (!file_exists($file)) {
    $errors[] = "FAIL: $file tidak dijumpai";
    goto output;
}

$content = file_get_contents($file);

/* ════════════════════════════════════════════════════════════
   PATCH A — Header light mode: dark→putih
   ════════════════════════════════════════════════════════════ */
if (strpos($content, 'html.light header[role="banner"] { background: #ffffff') !== false) {
    $log[] = 'SKIP [A] header sudah putih';
} else {
    $newA = '            html.light header[role="banner"] { background: #ffffff !important; box-shadow: 0 1px 0 #e5e7eb, 0 2px 8px rgba(0,0,0,0.04) !important; }
            html.light .bg-white { background: #ffffff !important; }';

    // Versi 1: header dark tanpa overrides (sebelum patch pertama)
    $oldA1 = '            html.light header[role="banner"] { background: #1a1a2e !important; box-shadow: 0 1px 3px rgba(0,0,0,.1) !important; }
            html.light .bg-white { background: #ffffff !important; }';

    if (strpos($content, $oldA1) !== false) {
        $content = str_replace($oldA1, $newA, $content);
        $log[] = 'OK [A] header tukar ke putih';
    } else {
        // Versi 2: ada text overrides dari patch pertama — guna regex
        $pattern = '/            html\.light header\[role="banner"\] \{ background: #1a1a2e.*?html\.light \.bg-white \{ background: #ffffff !important; \}/s';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $newA, $content);
            $log[] = 'OK [A] header tukar ke putih [regex]';
        } else {
            $errors[] = 'WARN [A] header — rentetan tidak dijumpai, mungkin sudah betul';
        }
    }
}

/* ════════════════════════════════════════════════════════════
   PATCH B — Sidebar light mode CSS
   ════════════════════════════════════════════════════════════ */
if (strpos($content, 'LIGHT MODE SIDEBAR') !== false) {
    $log[] = 'SKIP [B] sidebar CSS sudah ada';
} else {
    $sidebarCss = '
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
        html.light #panel-carian { background: #f1f5f9 !important; }
        html.light #panel-carian .form-label { color: #374151 !important; }
        html.light #panel-carian .form-hint { color: #6b7280 !important; }

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
    </style>';

    // Gantikan </style> TERAKHIR dalam fail
    $pos = strrpos($content, '    </style>');
    if ($pos !== false) {
        $content = substr($content, 0, $pos) . $sidebarCss . substr($content, $pos + strlen('    </style>'));
        $log[] = 'OK [B] sidebar + page overrides ditambah';
    } else {
        $errors[] = 'WARN [B] </style> tidak dijumpai';
    }
}

/* ════════════════════════════════════════════════════════════
   PATCH C — Tambah page overrides jika sidebar ada tapi page CSS tidak
   ════════════════════════════════════════════════════════════ */
if (strpos($content, 'Halaman spesifik') !== false) {
    $log[] = 'SKIP [C] page overrides sudah ada';
} elseif (strpos($content, 'LIGHT MODE SIDEBAR') !== false) {
    // Sidebar ada tapi tiada page overrides — tambah sebelum </style>
    $pageCss = '
        /* ══════════════════════════════════════════════════════════
           LIGHT MODE — Halaman spesifik (ketersediaan, pengguna)
           ══════════════════════════════════════════════════════════ */
        html.light #panel-carian { background: #f1f5f9 !important; }
        html.light #panel-carian .form-label { color: #374151 !important; }
        html.light #panel-carian .form-hint { color: #6b7280 !important; }
        html.light .nav-btn-minggu { background: #f3f4f6 !important; border-color: #d1d5db !important; color: #374151 !important; }
        html.light .nav-btn-minggu:hover { background: #e5e7eb !important; border-color: #9ca3af !important; color: #111827 !important; }
        html.light .nav-btn-minggu.ini { background: #fef3c7 !important; border-color: #f59e0b !important; color: #b45309 !important; }
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
        html.light #tbl-minggu th, html.light #tbl-minggu td { border-color: #e2e8f0 !important; }
        html.light .slot-chip.kosong { background: #dcfce7 !important; color: #16a34a !important; border-color: #86efac !important; }
        html.light .slot-chip.kosong:hover { background: #16a34a !important; color: #fff !important; }
        html.light .slot-chip.penuh { background: #fee2e2 !important; color: #dc2626 !important; border-color: #fca5a5 !important; }
        html.light .slot-chip.tiada { background: #f1f5f9 !important; color: #9ca3af !important; border-color: #e2e8f0 !important; }
        html.light .slot-chip.sehari { background: #dbeafe !important; color: #1d4ed8 !important; border-color: #93c5fd !important; }
        html.light .slot-chip.sehari:hover { background: #2563eb !important; color: #fff !important; }
        html.light .slot-chip.sehari-off { background: #f1f5f9 !important; color: #cbd5e1 !important; border-color: #e2e8f0 !important; }
        html.light .tab-btn.aktif-tab { background: #1e293b !important; color: #f8fafc !important; }
        html.light .tab-btn:hover:not(.aktif-tab) { background: #f3f4f6 !important; color: #374151 !important; }
    </style>';

    $pos = strrpos($content, '    </style>');
    if ($pos !== false) {
        $content = substr($content, 0, $pos) . $pageCss . substr($content, $pos + strlen('    </style>'));
        $log[] = 'OK [C] page overrides ditambah berasingan';
    } else {
        $errors[] = 'WARN [C] </style> tidak dijumpai';
    }
} else {
    $log[] = 'SKIP [C] tidak berkaitan';
}

/* ── Simpan fail ─────────────────────────────────────────────── */
file_put_contents($file, $content);
$log[] = 'OK app.blade.php disimpan (' . strlen($content) . ' bytes)';

output:
/* ── Output ──────────────────────────────────────────────────── */
echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
<title>fix-header-light-mode v3</title>
<style>body{font-family:monospace;padding:20px;background:#0f172a;color:#e2e8f0}
.ok{color:#4ade80}.warn{color:#fb923c}.err{color:#f87171}.done{font-size:1.2em;font-weight:bold;margin-top:16px}
</style></head><body>';
echo '<h2>fix-header-light-mode.php v3</h2><pre>';
foreach ($log as $l) {
    $cls = str_starts_with($l, 'OK') ? 'ok' : 'warn';
    echo '<span class="' . $cls . '">' . htmlspecialchars($l) . '</span>' . "\n";
}
foreach ($errors as $e) {
    $cls = str_starts_with($e, 'FAIL') ? 'err' : 'warn';
    echo '<span class="' . $cls . '">' . htmlspecialchars($e) . '</span>' . "\n";
}
echo '</pre>';
$ok = empty($errors);
echo '<div class="done" style="color:' . ($ok ? '#4ade80' : '#fb923c') . '">'
    . ($ok ? '&#10003; BERJAYA' : '&#9888; SELESAI DENGAN AMARAN')
    . '</div>';
echo '</body></html>';
