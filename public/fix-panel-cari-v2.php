<?php
/**
 * fix-panel-cari-v2.php
 * Betulkan html.light #panel-carian dalam app.blade.php:
 *   - Tukar background dari flat #f1f5f9 ke dark gradient (ketersediaan panel kekal gelap)
 *   - Tambah .panel-cari override supaya senarai tempahan panel juga kekal gelap
 *   - Kemaskini ketersediaan/index.blade.php: flat bg → gradient
 *   - Kemaskini tempahan/index.blade.php: tambah class panel-cari + CSS
 *
 * URL: https://ibookbptm.great-site.net/fix-panel-cari-v2.php?key=ibook2026deploy
 */
if (($_GET['key'] ?? '') !== 'ibook2026deploy') {
    http_response_code(403);
    die('Forbidden');
}

$base   = __DIR__ . '/resources/views';
$errors = [];
$log    = [];

/* ══════════════════════════════════════════════════════
   PATCH A — app.blade.php: tukar html.light #panel-carian
   ══════════════════════════════════════════════════════ */
$fileA = $base . '/layouts/app.blade.php';
if (!file_exists($fileA)) {
    $errors[] = "FAIL: $fileA tidak dijumpai";
    goto patchB;
}
$cA = file_get_contents($fileA);

if (strpos($cA, 'panel-cari-v2-done') !== false) {
    $log[] = 'SKIP [A] app.blade.php sudah ditampal v2';
    goto patchB;
}

/* A1: tukar flat bg ke gradient */
$oldA1 = "        html.light #panel-carian { background: #f1f5f9 !important; }";
$newA1 = "        /* panel-cari-v2-done */ html.light #panel-carian { background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%) !important; }";
if (strpos($cA, $oldA1) !== false) {
    $cA = str_replace($oldA1, $newA1, $cA);
    $log[] = 'OK [A1] html.light #panel-carian → gradient gelap';
} elseif (strpos($cA, '#0f3460') !== false && strpos($cA, '#panel-carian') !== false) {
    $log[] = 'SKIP [A1] sudah gradient';
} else {
    /* Cuba variasi dengan spacing berbeza */
    $patternA1 = '/html\.light #panel-carian \{ background: #f1f5f9 !important; \}/';
    if (preg_match($patternA1, $cA)) {
        $cA = preg_replace($patternA1,
            '/* panel-cari-v2-done */ html.light #panel-carian { background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%) !important; }',
            $cA);
        $log[] = 'OK [A1] html.light #panel-carian → gradient [regex]';
    } else {
        $errors[] = 'WARN [A1] html.light #panel-carian tidak dijumpai';
    }
}

/* A2: kemaskini form-label dan form-hint color supaya sesuai latar gelap */
$oldA2 = "        html.light #panel-carian .form-label { color: #374151 !important; }\n        html.light #panel-carian .form-hint { color: #6b7280 !important; }";
$newA2 = "        html.light #panel-carian .form-label { color: #94a3b8 !important; }\n        html.light #panel-carian .form-hint { color: #64748b !important; }";
if (strpos($cA, $oldA2) !== false) {
    $cA = str_replace($oldA2, $newA2, $cA);
    $log[] = 'OK [A2] form-label/hint color dikemas untuk latar gelap';
} else {
    $log[] = 'INFO [A2] form-label/hint — mungkin sudah betul';
}

/* A3: tambah .panel-cari override di hujung blok (sebelum </style> terakhir) */
if (strpos($cA, 'html.light .panel-cari') === false) {
    $addCss = "\n/* panel-cari: senarai tempahan filter panel kekal gelap dalam light mode */\nhtml.light .panel-cari { background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%) !important; }\n";
    $pos = strrpos($cA, '</style>');
    if ($pos !== false) {
        $cA = substr($cA, 0, $pos) . $addCss . substr($cA, $pos);
        $log[] = 'OK [A3] html.light .panel-cari override ditambah';
    } else {
        $errors[] = 'WARN [A3] </style> tidak dijumpai';
    }
} else {
    $log[] = 'SKIP [A3] html.light .panel-cari sudah ada';
}

file_put_contents($fileA, $cA);
$log[] = 'OK app.blade.php disimpan (' . strlen($cA) . ' bytes)';

patchB:
/* ══════════════════════════════════════════════════════
   PATCH B — ketersediaan/index.blade.php: flat bg → gradient
   ══════════════════════════════════════════════════════ */
$fileB = $base . '/ketersediaan/index.blade.php';
if (!file_exists($fileB)) {
    $errors[] = "FAIL: $fileB tidak dijumpai";
    goto patchC;
}
$cB = file_get_contents($fileB);

$oldBg = '#panel-carian { background: #1a1a2e; border-radius: 16px; padding: 28px 32px; }';
$newBg = '#panel-carian { background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%); border-radius: 16px; padding: 28px 32px; }';

if (strpos($cB, '#0f3460') !== false) {
    $log[] = 'SKIP [B] ketersediaan panel-carian sudah gradient';
} elseif (strpos($cB, $oldBg) !== false) {
    $cB = str_replace($oldBg, $newBg, $cB);
    file_put_contents($fileB, $cB);
    $log[] = 'OK [B] ketersediaan panel-carian → gradient (' . strlen($cB) . ' bytes)';
} else {
    $errors[] = 'WARN [B] #panel-carian flat bg tidak dijumpai — semak manual';
}

patchC:
/* ══════════════════════════════════════════════════════
   PATCH C — tempahan/index.blade.php: tambah panel-cari CSS + HTML
   ══════════════════════════════════════════════════════ */
$fileC = $base . '/tempahan/index.blade.php';
if (!file_exists($fileC)) {
    $errors[] = "FAIL: $fileC tidak dijumpai";
    goto output;
}
$cC = file_get_contents($fileC);

if (strpos($cC, '.panel-cari {') !== false && strpos($cC, 'panel-cari mb-5') !== false) {
    $log[] = 'SKIP [C] tempahan/index sudah ada panel-cari';
    goto output;
}

/* C1: tambah CSS */
$oldRelTime = "/* ── Relative time ───────────────────────────────────────── */\n.rel-time { font-size:11px; color:#9ca3af; }\n.rel-edit  { font-size:10px; color:#d97706; }";
if (strpos($cC, '.panel-cari {') === false && strpos($cC, $oldRelTime) !== false) {
    $newRelTime = "/* ── Panel carian gelap ──────────────────────────────────── */\n.panel-cari {\n    background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);\n    border-radius: 16px;\n    padding: 20px 24px;\n}\n.panel-cari-title {\n    color: #f59e0b;\n    font-size: 12px;\n    font-weight: 700;\n    letter-spacing: 0.05em;\n    margin-bottom: 14px;\n    display: flex;\n    align-items: center;\n    gap: 8px;\n}\n.panel-cari label,\n.panel-cari .text-xs,\n.panel-cari .text-gray-500 { color: #94a3b8 !important; }\n.panel-cari .btn-secondary {\n    background: rgba(255,255,255,0.08) !important;\n    color: #e2e8f0 !important;\n    border-color: rgba(255,255,255,0.15) !important;\n}\n.panel-cari .btn-secondary:hover { background: rgba(255,255,255,0.14) !important; }\n.panel-cari #btn-lanjutan { color: #94a3b8 !important; }\n.panel-cari #btn-lanjutan:hover { color: #f59e0b !important; }\n.panel-cari .border-t { border-color: rgba(255,255,255,0.1) !important; }\n.panel-cari .text-amber-400 { color: #fbbf24 !important; }\n\n/* ── Relative time ───────────────────────────────────────── */\n.rel-time { font-size:11px; color:#9ca3af; }\n.rel-edit  { font-size:10px; color:#d97706; }";
    $cC = str_replace($oldRelTime, $newRelTime, $cC);
    $log[] = 'OK [C1] CSS panel-cari ditambah ke tempahan/index';
}

/* C2: tukar section HTML */
$oldSection = '<section class="bg-white rounded-xl shadow-sm p-4 mb-5" aria-labelledby="heading-filter">' . "\n" . '    <h2 id="heading-filter" class="sr-only">Tapis Senarai Tempahan</h2>' . "\n\n" . '    <form method="GET" role="search" aria-label="Cari dan tapis tempahan">';
$newSection = '<section class="panel-cari mb-5" aria-labelledby="heading-filter">' . "\n" . '    <div class="panel-cari-title">' . "\n" . '        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>' . "\n" . '        CARI &amp; TAPIS TEMPAHAN' . "\n" . '    </div>' . "\n" . '    <h2 id="heading-filter" class="sr-only">Tapis Senarai Tempahan</h2>' . "\n\n" . '    <form method="GET" role="search" aria-label="Cari dan tapis tempahan">';

if (strpos($cC, 'panel-cari mb-5') === false && strpos($cC, $oldSection) !== false) {
    $cC = str_replace($oldSection, $newSection, $cC);
    $log[] = 'OK [C2] section HTML tempahan → panel-cari';
} elseif (strpos($cC, 'panel-cari mb-5') !== false) {
    $log[] = 'SKIP [C2] section sudah panel-cari';
} else {
    $errors[] = 'WARN [C2] section HTML tidak dijumpai — semak manual';
}

file_put_contents($fileC, $cC);
$log[] = 'OK tempahan/index.blade.php disimpan (' . strlen($cC) . ' bytes)';

output:
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>fix-panel-cari-v2</title>
<style>body{font-family:monospace;padding:24px;background:#0f172a;color:#e2e8f0}
.ok{color:#4ade80}.warn{color:#fb923c}.err{color:#f87171}
h2{color:#f59e0b;margin-bottom:16px}.done{font-size:1.3em;font-weight:bold;margin-top:20px}
</style></head><body>';
echo '<h2>fix-panel-cari-v2.php</h2><pre>';
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
