<?php
/**
 * fix-panel-dark.php
 * Paksa panel carian kekal dark gradient dalam light mode
 * menggunakan specificity lebih tinggi (html.light body #id beats html.light #id).
 *
 * URL: https://ibookbptm.great-site.net/fix-panel-dark.php?key=ibook2026deploy
 */
if (($_GET['key'] ?? '') !== 'ibook2026deploy') {
    http_response_code(403);
    die('Forbidden');
}

$base   = __DIR__ . '/resources/views';
$errors = [];
$log    = [];

/* ═══════════════════════════════════════════════════════════════
   PATCH A — ketersediaan/index.blade.php
   Tambah rule specificity tinggi supaya #panel-carian kekal gelap
   ═══════════════════════════════════════════════════════════════ */
$fileA = $base . '/ketersediaan/index.blade.php';
if (!file_exists($fileA)) {
    $errors[] = "FAIL: $fileA tidak dijumpai";
    goto patchB;
}
$cA = file_get_contents($fileA);

if (strpos($cA, 'fix-panel-dark-v1') !== false) {
    $log[] = 'SKIP [A] ketersediaan sudah ditampal';
    goto patchB;
}

/* Sisip CSS sebelum </style> pertama dalam blok @push('styles') */
$marker  = '</style>';
$firstPos = strpos($cA, $marker);
if ($firstPos === false) {
    $errors[] = 'WARN [A] </style> tidak dijumpai dalam ketersediaan/index';
    goto patchB;
}

$insertCss = '
/* fix-panel-dark-v1: specificity lebih tinggi utk kalahkan html.light override v4 */
html.light body #panel-carian {
    background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%) !important;
}
/* Gradient base juga */
#panel-carian { background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%); }
';

$cA = substr($cA, 0, $firstPos) . $insertCss . substr($cA, $firstPos);
file_put_contents($fileA, $cA);
$log[] = 'OK [A] ketersediaan #panel-carian dikunci gelap (' . strlen($cA) . ' bytes)';

patchB:
/* ═══════════════════════════════════════════════════════════════
   PATCH B — tempahan/index.blade.php
   Tambah .panel-cari CSS + ubah section HTML
   ═══════════════════════════════════════════════════════════════ */
$fileB = $base . '/tempahan/index.blade.php';
if (!file_exists($fileB)) {
    $errors[] = "FAIL: $fileB tidak dijumpai";
    goto output;
}
$cB = file_get_contents($fileB);

if (strpos($cB, 'fix-panel-dark-v1') !== false) {
    $log[] = 'SKIP [B] tempahan sudah ditampal';
    goto output;
}

/* B1: tambah CSS panel-cari sebelum </style> pertama */
$firstPos2 = strpos($cB, '</style>');
if ($firstPos2 !== false && strpos($cB, '.panel-cari {') === false) {
    $panelCss = '
/* fix-panel-dark-v1 */
.panel-cari {
    background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);
    border-radius: 16px;
    padding: 20px 24px;
}
.panel-cari-title {
    color: #f59e0b;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.05em;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.panel-cari label,
.panel-cari .text-xs,
.panel-cari .text-gray-500 { color: #94a3b8 !important; }
.panel-cari .btn-secondary {
    background: rgba(255,255,255,0.08) !important;
    color: #e2e8f0 !important;
    border-color: rgba(255,255,255,0.15) !important;
}
.panel-cari .btn-secondary:hover { background: rgba(255,255,255,0.14) !important; }
.panel-cari #btn-lanjutan { color: #94a3b8 !important; }
.panel-cari #btn-lanjutan:hover { color: #f59e0b !important; }
.panel-cari .border-t { border-color: rgba(255,255,255,0.1) !important; }
.panel-cari .text-amber-400 { color: #fbbf24 !important; }
/* paksa kekal gelap dalam light mode (specificity tinggi) */
html.light body .panel-cari {
    background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%) !important;
}
';
    $cB = substr($cB, 0, $firstPos2) . $panelCss . substr($cB, $firstPos2);
    $log[] = 'OK [B1] CSS .panel-cari ditambah';
} elseif (strpos($cB, '.panel-cari {') !== false) {
    /* CSS dah ada tapi mungkin tiada html.light body override — tambah */
    if (strpos($cB, 'html.light body .panel-cari') === false) {
        $firstPos2 = strpos($cB, '</style>');
        $addOverride = "\n/* paksa kekal gelap */ html.light body .panel-cari { background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%) !important; }\n";
        $cB = substr($cB, 0, $firstPos2) . $addOverride . substr($cB, $firstPos2);
        $log[] = 'OK [B1b] tambah html.light body .panel-cari override';
    } else {
        $log[] = 'SKIP [B1] CSS panel-cari sudah ada';
    }
}

/* B2: tukar section HTML jika masih bg-white */
$oldSection = '<section class="bg-white rounded-xl shadow-sm p-4 mb-5" aria-labelledby="heading-filter">'
    . "\n" . '    <h2 id="heading-filter" class="sr-only">Tapis Senarai Tempahan</h2>'
    . "\n\n" . '    <form method="GET" role="search" aria-label="Cari dan tapis tempahan">';
$newSection = '<section class="panel-cari mb-5" aria-labelledby="heading-filter">'
    . "\n" . '    <div class="panel-cari-title">'
    . "\n" . '        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>'
    . "\n" . '        CARI &amp; TAPIS TEMPAHAN'
    . "\n" . '    </div>'
    . "\n" . '    <h2 id="heading-filter" class="sr-only">Tapis Senarai Tempahan</h2>'
    . "\n\n" . '    <form method="GET" role="search" aria-label="Cari dan tapis tempahan">';

if (strpos($cB, 'panel-cari mb-5') !== false) {
    $log[] = 'SKIP [B2] section HTML sudah panel-cari';
} elseif (strpos($cB, $oldSection) !== false) {
    $cB = str_replace($oldSection, $newSection, $cB);
    $log[] = 'OK [B2] section HTML → panel-cari';
} else {
    $errors[] = 'WARN [B2] section bg-white tidak dijumpai — semak manual';
}

file_put_contents($fileB, $cB);
$log[] = 'OK tempahan/index.blade.php disimpan (' . strlen($cB) . ' bytes)';

output:
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>fix-panel-dark</title>
<style>body{font-family:monospace;padding:24px;background:#0f172a;color:#e2e8f0}
.ok{color:#4ade80}.warn{color:#fb923c}.err{color:#f87171}
h2{color:#f59e0b;margin-bottom:16px}.done{font-size:1.3em;font-weight:bold;margin-top:20px}
</style></head><body>';
echo '<h2>fix-panel-dark.php</h2><pre>';
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
