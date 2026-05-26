<?php
/**
 * fix-chart-lightmode.php
 * Betulkan warna label/tick carta Chart.js dalam laporan/index.blade.php
 * Root cause: isDarkMode pakai window.matchMedia() (OS setting) bukan
 *             html.light / html.dark class — label jadi putih di atas bg cerah.
 *
 * URL: https://ibookbptm.great-site.net/fix-chart-lightmode.php?key=ibook2026deploy
 */
if (($_GET['key'] ?? '') !== 'ibook2026deploy') {
    http_response_code(403);
    die('Forbidden');
}

$file   = __DIR__ . '/resources/views/laporan/index.blade.php';
$errors = [];
$log    = [];

if (!file_exists($file)) {
    $errors[] = "FAIL: $file tidak dijumpai";
    goto output;
}

$content = file_get_contents($file);

/* ══════════════════════════════════════════════════════════
   PATCH A — Betulkan isDarkMode supaya semak html class dulu
   ══════════════════════════════════════════════════════════ */
$oldA = 'const isDarkMode  = window.matchMedia(\'(prefers-color-scheme: dark)\').matches;';
$newA = 'const _html = document.documentElement;
const isDarkMode = _html.classList.contains(\'dark\') ||
    (!_html.classList.contains(\'light\') && window.matchMedia(\'(prefers-color-scheme: dark)\').matches);';

if (strpos($content, 'fix-chart-lm-v1') !== false) {
    $log[] = 'SKIP sudah ditampal (fix-chart-lm-v1)';
    goto output;
}

if (strpos($content, $oldA) !== false) {
    $content = str_replace($oldA, '/* fix-chart-lm-v1 */ ' . $newA, $content);
    $log[] = 'OK [A] isDarkMode diubah — semak html.light/dark class';
} elseif (strpos($content, '_html.classList.contains') !== false) {
    $log[] = 'SKIP [A] isDarkMode sudah menggunakan classList';
} else {
    $errors[] = 'WARN [A] rentetan isDarkMode tidak dijumpai — periksa fail manual';
}

/* ══════════════════════════════════════════════════════════
   PATCH B — Tambah color: legendColor ke chartSesi ticks
   ══════════════════════════════════════════════════════════ */
$oldB = "            x: { stacked: true, grid: { display: false }, ticks: { font: { size: 11 } } },\n            y: { stacked: true, beginAtZero: true, ticks: { precision: 0, font: { size: 11 } }, grid: { color: '#f3f4f6' } }";
$newB = "            x: { stacked: true, grid: { display: false }, ticks: { font: { size: 11 }, color: legendColor } },\n            y: { stacked: true, beginAtZero: true, ticks: { precision: 0, font: { size: 11 }, color: legendColor }, grid: { color: gridColor } }";

if (strpos($content, $oldB) !== false) {
    $content = str_replace($oldB, $newB, $content);
    $log[] = 'OK [B] chartSesi ticks color ditambah';
} elseif (strpos($content, 'color: legendColor') !== false && strpos($content, 'chartSesi') !== false) {
    $log[] = 'SKIP [B] chartSesi sudah ada legendColor';
} else {
    $log[] = 'INFO [B] chartSesi tick color — rentetan tidak dijumpai, mungkin sudah ok';
}

/* Simpan */
if (!empty($log) && !in_array(true, array_map(fn($l) => str_starts_with($l,'SKIP'), $log), true)
    || (count($log) > 0 && !str_starts_with($log[0], 'SKIP'))) {
    file_put_contents($file, $content);
    $log[] = 'OK laporan/index.blade.php disimpan (' . strlen($content) . ' bytes)';
}

output:
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>fix-chart-lightmode</title>
<style>body{font-family:monospace;padding:24px;background:#0f172a;color:#e2e8f0}
.ok{color:#4ade80}.warn{color:#fb923c}.err{color:#f87171}
h2{color:#f59e0b;margin-bottom:16px}.done{font-size:1.3em;font-weight:bold;margin-top:20px}
</style></head><body>';
echo '<h2>fix-chart-lightmode.php</h2><pre>';
foreach ($log as $l) {
    $cls = str_starts_with($l, 'OK') ? 'ok' : (str_starts_with($l, 'SKIP') ? 'warn' : 'warn');
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
