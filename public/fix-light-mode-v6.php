<?php
/**
 * fix-light-mode-v6.php — Perbaikan menyeluruh light mode
 * Meliputi: dashboard welcome panel, tab bar, wl-card (senarai tempahan),
 * tapis-chip, action buttons/dropdown, status badges, modal.
 *
 * URL: https://ibookbptm.great-site.net/fix-light-mode-v6.php?key=ibook2026deploy
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
if (strpos($content, 'ibook-lm-v6') !== false) {
    $log[] = 'SKIP sudah ditampal (ibook-lm-v6)';
    goto output;
}

/* CSS tambahan — dimasukkan sebelum </style> terakhir */
$css = '
/* ibook-lm-v6 ══════════════════════════════════════════════════════
   Dashboard, Senarai Tempahan, Pengguna — light mode fixes
   ════════════════════════════════════════════════════════════════ */

/* ── Dashboard: Welcome panel (tukar kepada biru gelap yang lebih cerah) */
html.light .quick-check-panel {
    background: linear-gradient(135deg, #1e3a5f 0%, #1d4ed8 100%) !important;
}

/* ── Dashboard/Pengguna: Tab bar ─────────────────────────────────── */
html.light .tab-bar { background: #ffffff !important; box-shadow: 0 1px 3px rgba(0,0,0,.08) !important; }
html.light .tab-btn { color: #6b7280 !important; background: transparent !important; }
html.light .tab-btn.aktif-tab {
    background: #f59e0b !important;
    color: #1a1a2e !important;
    box-shadow: 0 2px 8px rgba(245,158,11,.3) !important;
}
html.light .tab-btn:hover:not(.aktif-tab) { background: #fef3c7 !important; color: #92400e !important; }

/* ── Senarai Tempahan: Worklist cards ────────────────────────────── */
html.light .wl-card { background: #ffffff !important; border-color: #e5e7eb !important; color: #1f2937 !important; }
html.light .wl-card.aktif { background: #fffbeb !important; }
html.light .wl-lbl { color: #6b7280 !important; }

/* ── Senarai Tempahan: Tapis chip (tarikh) ───────────────────────── */
html.light .tapis-chip { background: #ffffff !important; border-color: #e5e7eb !important; color: #6b7280 !important; }
html.light .tapis-chip:hover { border-color: #f59e0b !important; color: #d97706 !important; background: #fef3c7 !important; }
html.light .tapis-chip.aktif { background: #1a1a2e !important; border-color: #1a1a2e !important; color: #f59e0b !important; }

/* ── Senarai Tempahan: Butang tindakan ───────────────────────────── */
html.light .action-trigger { background: #f3f4f6 !important; border-color: #e5e7eb !important; color: #374151 !important; }
html.light .action-trigger:hover { background: #e5e7eb !important; border-color: #d1d5db !important; }
html.light .action-dd { background: #ffffff !important; border-color: #e5e7eb !important; box-shadow: 0 8px 24px rgba(0,0,0,.08) !important; }
html.light .action-dd a, html.light .action-dd button { color: #374151 !important; background: transparent !important; }
html.light .action-dd a:hover, html.light .action-dd button:hover { background: #f9fafb !important; }
html.light .action-dd .dd-divider { background: #f3f4f6 !important; }

/* ── Senarai Tempahan: Badge status ──────────────────────────────── */
html.light .st-sah     { background: #dcfce7 !important; color: #15803d !important; }
html.light .st-ditolak { background: #fee2e2 !important; color: #b91c1c !important; }

/* ── Senarai Tempahan: Modal ─────────────────────────────────────── */
html.light #modal-pindah > div { background: #ffffff !important; }

/* ── Rel time ────────────────────────────────────────────────────── */
html.light .rel-time { color: #9ca3af !important; }
html.light .rel-edit  { color: #d97706 !important; }

/* ── Panel carian gelap (panel-cari + ketersediaan) — kekal gelap dalam light mode */
/* Panel carian sengaja kekal dark gradient — tiada override light mode */
';

/* Cari </style> TERAKHIR (hujung blok v4/v5) */
$pos = strrpos($content, '</style>');
if ($pos === false) {
    $errors[] = 'WARN: </style> tidak dijumpai';
    goto output;
}

$content = substr($content, 0, $pos) . $css . substr($content, $pos);
file_put_contents($file, $content);
$log[] = 'OK app.blade.php (' . strlen($content) . ' bytes) — v6 css ditambah';

output:
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>fix-light-mode-v6</title>
<style>body{font-family:monospace;padding:24px;background:#0f172a;color:#e2e8f0}
.ok{color:#4ade80}.warn{color:#fb923c}.err{color:#f87171}
h2{color:#f59e0b;margin-bottom:16px}.done{font-size:1.3em;font-weight:bold;margin-top:20px}
</style></head><body>';
echo '<h2>fix-light-mode-v6.php</h2><pre>';
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
