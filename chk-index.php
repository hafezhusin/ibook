<?php
define('KEY', 'ibook2026deploy');
if (($_GET['key'] ?? '') !== KEY) { http_response_code(403); die('Akses ditolak.'); }
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><style>body{font-family:monospace;background:#1a1a2e;color:#e5e7eb;padding:20px}.ok{color:#34d399}.fail{color:#f87171}.warn{color:#fbbf24}pre{background:#0f172a;padding:10px;border-radius:6px;overflow-x:auto;white-space:pre-wrap}</style></head><body>';

$f = __DIR__ . '/resources/views/tempahan/index.blade.php';
echo '<h3>Saiz fail: ' . number_format(filesize($f)) . ' bytes | Tarikh: ' . date('Y-m-d H:i:s', filemtime($f)) . '</h3>';

// Cari baris berulang
$lines = file($f);
$found = [];
foreach ($lines as $n => $line) {
    if (str_contains($line, 'padam-berulang') || str_contains($line, 'padam-kumpulan') || str_contains($line, 'skop_padam') || str_contains($line, 'bukaModalPadam') || str_contains($line, 'padam-skop-wrap')) {
        $found[] = ($n+1) . ': ' . htmlspecialchars(trim($line));
    }
}

if (empty($found)) {
    echo '<p class="fail">⚠ Tiada kod berulang dalam index.blade.php — fail LAMA masih ada di server!</p>';
} else {
    echo '<p class="ok">✓ Kod berulang dijumpai (' . count($found) . ' baris):</p><pre>' . implode("\n", $found) . '</pre>';
}
echo '</body></html>';