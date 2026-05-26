<?php
/**
 * fix-panel-text.php
 * Betulkan warna teks (form-label, form-hint) dalam #panel-carian
 * yang masih gelap walaupun panel dah dark gradient.
 *
 * Root cause: html.light .form-label { color: #374151 !important } dalam app.blade.php
 * menang atas inline style="color:#e5e7eb" kerana !important external > non-!important inline.
 *
 * Fix: tambah html.light body #panel-carian .form-label { color: #e5e7eb !important }
 * (specificity 1-2-2) yang lebih tinggi daripada html.light .form-label (0-2-1).
 *
 * URL: https://ibookbptm.great-site.net/fix-panel-text.php?key=ibook2026deploy
 */
if (($_GET['key'] ?? '') !== 'ibook2026deploy') {
    http_response_code(403);
    die('Forbidden');
}

$base   = __DIR__ . '/resources/views';
$errors = [];
$log    = [];

/* ═══════════════════════════════════════════════════════════
   PATCH — ketersediaan/index.blade.php
   ═══════════════════════════════════════════════════════════ */
$file = $base . '/ketersediaan/index.blade.php';
if (!file_exists($file)) {
    $errors[] = "FAIL: $file tidak dijumpai";
    goto output;
}
$content = file_get_contents($file);

if (strpos($content, 'fix-panel-text-v1') !== false) {
    $log[] = 'SKIP sudah ditampal (fix-panel-text-v1)';
    goto output;
}

/* Cari </style> pertama dalam fail (hujung @push('styles')) */
$pos = strpos($content, '</style>');
if ($pos === false) {
    $errors[] = 'WARN </style> tidak dijumpai dalam ketersediaan/index';
    goto output;
}

$newCss = '
/* fix-panel-text-v1: teks cerah dalam #panel-carian walaupun light mode */
/* Specificity 1-2-2 mengatasi html.light .form-label (0-2-1) yang ada !important */
html.light body #panel-carian .form-label { color: #e5e7eb !important; }
html.light body #panel-carian .form-hint  { color: #9ca3af !important; }
html.light body #panel-carian .text-gray-400 { color: #94a3b8 !important; }
html.light body #panel-carian .text-gray-500 { color: #94a3b8 !important; }
';

/* Sisip sebelum </style> pertama */
$content = substr($content, 0, $pos) . $newCss . substr($content, $pos);
file_put_contents($file, $content);
$log[] = 'OK ketersediaan/index.blade.php teks panel dikemas (' . strlen($content) . ' bytes)';

output:
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>fix-panel-text</title>
<style>body{font-family:monospace;padding:24px;background:#0f172a;color:#e2e8f0}
.ok{color:#4ade80}.warn{color:#fb923c}.err{color:#f87171}
h2{color:#f59e0b;margin-bottom:16px}.done{font-size:1.3em;font-weight:bold;margin-top:20px}
</style></head><body>';
echo '<h2>fix-panel-text.php</h2><pre>';
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
