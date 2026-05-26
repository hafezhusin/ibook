<?php
/**
 * fix-light-mode-v5.php
 * Tambah peraturan CSS khusus untuk mengatasi inline style pada div dalam .bilik-nama-cell.
 * Root cause: JS membina jadual dengan <div style="color:#f1f5f9"> (inline style).
 * CSS pada parent <td> tidak mempengaruhi inline style anak — mesti target div secara langsung.
 *
 * URL: https://ibookbptm.great-site.net/fix-light-mode-v5.php?key=ibook2026deploy
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
if (strpos($content, 'ibook-lm-v5') !== false) {
    $log[] = 'SKIP sudah ditampal (ibook-lm-v5)';
    goto output;
}

/* Pastikan v4 ada */
if (strpos($content, 'ibook-lm-v4') === false) {
    $errors[] = 'WARN: blok v4 tidak dijumpai — jalankan fix-light-mode-v4.php dahulu';
    goto output;
}

/* CSS tambahan */
$extraCss = '
/* ibook-lm-v5: override inline style pada div dalam bilik-nama-cell (JS-generated) */
html.light .bilik-nama-cell > div:first-child { color: #1e293b !important; }
html.light .bilik-nama-cell > div:nth-child(2) { color: #64748b !important; }
html.light #jadual-grid { border-color: #e2e8f0 !important; }
';

/* Cari </style> TERAKHIR dalam fail (hujung blok v4) */
$pos = strrpos($content, '</style>');
if ($pos === false) {
    $errors[] = 'WARN: </style> tidak dijumpai';
    goto output;
}

/* Sisip CSS baru SEBELUM </style> terakhir */
$content = substr($content, 0, $pos) . $extraCss . substr($content, $pos);
file_put_contents($file, $content);
$log[] = 'OK app.blade.php (' . strlen($content) . ' bytes) — v5 css ditambah';

output:
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>fix-light-mode-v5</title>
<style>body{font-family:monospace;padding:24px;background:#0f172a;color:#e2e8f0}
.ok{color:#4ade80}.warn{color:#fb923c}.err{color:#f87171}
h2{color:#f59e0b;margin-bottom:16px}.done{font-size:1.3em;font-weight:bold;margin-top:20px}
</style></head><body>';
echo '<h2>fix-light-mode-v5.php</h2><pre>';
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
