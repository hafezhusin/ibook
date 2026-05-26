<?php
define('KEY', 'ibook2026deploy');
if (($_GET['key'] ?? '') !== KEY) { http_response_code(403); die('Akses ditolak.'); }
header('Content-Type: text/html; charset=utf-8');
$file = __DIR__ . '/resources/views/tempahan/index.blade.php';
$content = file_get_contents($file);
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><style>body{font-family:monospace;background:#1a1a2e;color:#e5e7eb;padding:20px}.ok{color:#34d399}.fail{color:#f87171}.skip{color:#94a3b8}pre{background:#0f172a;padding:10px;border-radius:6px}</style></head><body><h2 style="color:#f59e0b">Fix index.blade.php — Patch Berulang</h2><pre>';

if (str_contains($content, 'data-padam-berulang')) {
    echo "<span class='skip'>[SKIP]</span> data-padam-berulang sudah ada dalam fail.\n";
} else {
    // === PATCH 1: Tambah data attributes ke butang Padam ===
    $insertBlock = base64_decode('CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGRhdGEtcGFkYW0tYmVydWxhbmc9Int7ICR0LT50ZW1wYWhhbl9iZXJ1bGFuZ19pZCA/ICcxJyA6ICcwJyB9fSIKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZGF0YS1wYWRhbS1rdW1wdWxhbi11bGlkPSJ7eyAkdC0+a3VtcHVsYW5CZXJ1bGFuZz8tPnVsaWQgPz8gJycgfX0iCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGRhdGEtcGFkYW0ta3VtcHVsYW4tanVtbGFoPSJ7eyAkdC0+a3VtcHVsYW5CZXJ1bGFuZz8tPnRlbXBhaGFuQWt0aWYoKS0+Y291bnQoKSA/PyAwIH19Ig==');
    $btnNeedle = 'data-padam-nama="{{ addslashes($t->nama_mesyuarat) }}"';
    $classNeedle = 'class="text-red-500 hover:bg-red-50 w-full"';
    if (str_contains($content, $btnNeedle)) {
        $pos = strpos($content, $btnNeedle) + strlen($btnNeedle);
        $posClass = strpos($content, $classNeedle, $pos);
        $between = substr($content, $pos, $posClass - $pos);
        $content = substr($content, 0, $pos) . $insertBlock . "\n" . str_repeat(' ', 36) . substr($content, $posClass);
        echo "<span class='ok'>[OK]</span> Patch 1: data-padam-berulang ditambah ke butang.\n";
    } else {
        echo "<span class='fail'>[FAIL]</span> Patch 1: data-padam-nama tidak dijumpai.\n";
    }

    // === PATCH 2: Tambah scope div ke modal ===
    $scopeDiv = base64_decode('PGRpdiBpZD0icGFkYW0tc2tvcC13cmFwIiBjbGFzcz0iaGlkZGVuIHNwYWNlLXktMiBiZy1ncmF5LTUwIHJvdW5kZWQtbGcgcC0zIj4KICAgICAgICAgICAgPHAgY2xhc3M9InRleHQteHMgZm9udC1zZW1pYm9sZCB0ZXh0LWdyYXktNTAwIG1iLTIiPlBpbGloIHNrb3AgcGVtYWRhbWFuOjwvcD4KICAgICAgICAgICAgPGxhYmVsIGNsYXNzPSJmbGV4IGl0ZW1zLXN0YXJ0IGdhcC0yIGN1cnNvci1wb2ludGVyIj4KICAgICAgICAgICAgICAgIDxpbnB1dCB0eXBlPSJyYWRpbyIgbmFtZT0ic2tvcF9wYWRhbSIgdmFsdWU9ImluaSIgY2hlY2tlZCBjbGFzcz0ibXQtMC41Ij4KICAgICAgICAgICAgICAgIDxzcGFuIGNsYXNzPSJ0ZXh0LXNtIHRleHQtZ3JheS03MDAiPlRlbXBhaGFuIGluaSBzYWhhamE8L3NwYW4+CiAgICAgICAgICAgIDwvbGFiZWw+CiAgICAgICAgICAgIDxsYWJlbCBjbGFzcz0iZmxleCBpdGVtcy1zdGFydCBnYXAtMiBjdXJzb3ItcG9pbnRlciI+CiAgICAgICAgICAgICAgICA8aW5wdXQgdHlwZT0icmFkaW8iIG5hbWU9InNrb3BfcGFkYW0iIHZhbHVlPSJzZW11YSI+CiAgICAgICAgICAgICAgICA8c3BhbiBjbGFzcz0idGV4dC1zbSB0ZXh0LWdyYXktNzAwIj4KICAgICAgICAgICAgICAgICAgICBTZW11YSBkYWxhbSBrdW1wdWxhbgogICAgICAgICAgICAgICAgICAgICg8c3BhbiBpZD0icGFkYW0tanVtbGFoIiBjbGFzcz0iZm9udC1zZW1pYm9sZCI+MDwvc3Bhbj4gdGVtcGFoYW4pCiAgICAgICAgICAgICAgICA8L3NwYW4+CiAgICAgICAgICAgIDwvbGFiZWw+CiAgICAgICAgPC9kaXY+');
    $afterTeks = '<p id="padam-teks" class="text-sm text-gray-600"></p>';
    $formTag = '<form id="form-padam"';
    if (str_contains($content, $afterTeks) && !str_contains($content, 'padam-skop-wrap')) {
        $insertAt = strpos($content, $formTag, strpos($content, $afterTeks));
        $content = substr($content, 0, $insertAt) . $scopeDiv . "\n\n        " . substr($content, $insertAt);
        echo "<span class='ok'>[OK]</span> Patch 2: scope div ditambah ke modal.\n";
    } else {
        echo "<span class='skip'>[SKIP]</span> Patch 2: scope div sudah ada atau padam-teks tidak dijumpai.\n";
    }

    // === PATCH 3: Update bukaModalPadam JS ===
    $newJsFunc = base64_decode('ZnVuY3Rpb24gYnVrYU1vZGFsUGFkYW0odWxpZCwgbmFtYSwgYmVydWxhbmcsIGt1bXB1bGFuVWxpZCwga3VtcHVsYW5KdW1sYWgpIHsKICAgIGNvbnN0IHVybFBhZGFtID0gJy90ZW1wYWhhbi8nICsgdWxpZCArICcvcGFkYW0tYmVydWxhbmcnOwoKICAgIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdwYWRhbS10ZWtzJykudGV4dENvbnRlbnQgPQogICAgICAgIGJlcnVsYW5nCiAgICAgICAgICAgID8gJ0FuZGEgYWthbiBtZW1hZGFtICInICsgbmFtYSArICciLiBQaWxpaCBza29wIHBlbWFkYW1hbjonCiAgICAgICAgICAgIDogJ0FuZGEgcGFzdGkgbWFodSBtZW1hZGFtICInICsgbmFtYSArICciPyBUaW5kYWthbiBpbmkgdGlkYWsgYm9sZWggZGliYXRhbGthbi4nOwoKICAgIGNvbnN0IHNrb3BXcmFwID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3BhZGFtLXNrb3Atd3JhcCcpOwogICAgc2tvcFdyYXAuY2xhc3NMaXN0LnRvZ2dsZSgnaGlkZGVuJywgIWJlcnVsYW5nKTsKCiAgICBpZiAoYmVydWxhbmcpIHsKICAgICAgICBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncGFkYW0tanVtbGFoJykudGV4dENvbnRlbnQgPSBrdW1wdWxhbkp1bWxhaDsKICAgICAgICAvLyBSZXNldCBrZSAnaW5pJwogICAgICAgIGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJ2lucHV0W25hbWU9InNrb3BfcGFkYW0iXScpLmZvckVhY2gociA9PiB7CiAgICAgICAgICAgIHIuY2hlY2tlZCA9IHIudmFsdWUgPT09ICdpbmknOwogICAgICAgIH0pOwogICAgICAgIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdpbnB1dC1za29wLXBhZGFtJykudmFsdWUgPSAnaW5pJzsKICAgIH0KCiAgICBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnZm9ybS1wYWRhbScpLmFjdGlvbiA9IHVybFBhZGFtOwogICAgZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ21vZGFsLXBhZGFtJykuY2xhc3NMaXN0LnJlbW92ZSgnaGlkZGVuJyk7Cn0=');
    if (str_contains($content, 'function bukaModalPadam(ulid, nama)')) {
        $oldFuncStart = strpos($content, 'function bukaModalPadam(ulid, nama)');
        $depth = 0; $oldFuncEnd = $oldFuncStart;
        for ($i = $oldFuncStart; $i < strlen($content); $i++) {
            if ($content[$i] === '{') $depth++;
            if ($content[$i] === '}') { $depth--; if ($depth === 0) { $oldFuncEnd = $i + 1; break; } }
        }
        $content = substr($content, 0, $oldFuncStart) . $newJsFunc . substr($content, $oldFuncEnd);
        echo "<span class='ok'>[OK]</span> Patch 3: bukaModalPadam dikemaskini.\n";
    } elseif (str_contains($content, 'function bukaModalPadam(ulid, nama, berulang')) {
        echo "<span class='skip'>[SKIP]</span> Patch 3: bukaModalPadam sudah dikemaskini.\n";
    } else {
        echo "<span class='fail'>[FAIL]</span> Patch 3: bukaModalPadam tidak dijumpai.\n";
    }

    // Juga patch event listener call
    $oldCall = 'bukaModalPadam(ulid, nama);';
    $newCall = 'bukaModalPadam(ulid, nama, berulang, kumpulanUlid, kumpulanJumlah);';
    if (str_contains($content, $oldCall)) {
        $content = str_replace($oldCall, $newCall, $content);
        echo "<span class='ok'>[OK]</span> Patch 3b: event listener call dikemaskini.\n";
    }
}

if (file_put_contents($file, $content) !== false) {
    echo "<span class='ok'>[OK]</span> index.blade.php ditulis (" . strlen($content) . " bytes).\n";
} else {
    echo "<span class='fail'>[FAIL]</span> Gagal tulis fail!\n";
}

// Clear blade cache
$vd = __DIR__ . '/storage/framework/views'; $cl = 0;
if (is_dir($vd)) foreach (glob($vd . '/*.php') as $f) if (@unlink($f)) $cl++;
echo "<span class='ok'>[OK]</span> $cl blade cache dipadam.\n";
echo '</pre>';
echo '<form method="post" action="?key=ibook2026deploy&delete=1"><button style="background:#991b1b;color:#fff;border:none;padding:8px 18px;border-radius:6px;cursor:pointer;margin-top:12px">&#128465; Padam fix-index2.php</button></form>';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['delete'] ?? '') === '1') { @unlink(__FILE__); echo '<p style="color:#34d399">&#10003; Dipadamkan.</p>'; }
echo '</body></html>';