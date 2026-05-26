<?php
/**
 * fix-panel-cari.php
 * Tambah dark blue gradient background pada:
 *   1) Panel carian/tapis dalam Senarai Tempahan (tempahan/index.blade.php)
 *   2) #panel-carian dalam Ketersediaan (ketersediaan/index.blade.php)
 *
 * URL: https://ibookbptm.great-site.net/fix-panel-cari.php?key=ibook2026deploy
 */
if (($_GET['key'] ?? '') !== 'ibook2026deploy') {
    http_response_code(403);
    die('Forbidden');
}

$base   = __DIR__ . '/resources/views';
$errors = [];
$log    = [];

/* ══════════════════════════════════════════════════════════════════
   PATCH A — tempahan/index.blade.php: tambah CSS + tukar section HTML
   ══════════════════════════════════════════════════════════════════ */
$fileA = $base . '/tempahan/index.blade.php';
if (!file_exists($fileA)) {
    $errors[] = "FAIL: $fileA tidak dijumpai";
} else {
    $cA = file_get_contents($fileA);

    if (strpos($cA, 'panel-cari-fix-v1') !== false) {
        $log[] = 'SKIP [A] tempahan/index sudah ditampal';
    } else {
        /* CSS baru */
        $oldCss = "/* ── Relative time ───────────────────────────────────────── */\n.rel-time { font-size:11px; color:#9ca3af; }\n.rel-edit  { font-size:10px; color:#d97706; }";
        $newCss = "/* panel-cari-fix-v1 */\n/* ── Panel carian gelap ──────────────────────────────────── */\n.panel-cari {\n    background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);\n    border-radius: 16px;\n    padding: 20px 24px;\n}\n.panel-cari-title {\n    color: #f59e0b;\n    font-size: 12px;\n    font-weight: 700;\n    letter-spacing: 0.05em;\n    margin-bottom: 14px;\n    display: flex;\n    align-items: center;\n    gap: 8px;\n}\n.panel-cari label,\n.panel-cari .text-xs,\n.panel-cari .text-gray-500 { color: #94a3b8 !important; }\n.panel-cari .btn-secondary {\n    background: rgba(255,255,255,0.08) !important;\n    color: #e2e8f0 !important;\n    border-color: rgba(255,255,255,0.15) !important;\n}\n.panel-cari .btn-secondary:hover { background: rgba(255,255,255,0.14) !important; }\n.panel-cari #btn-lanjutan { color: #94a3b8 !important; }\n.panel-cari #btn-lanjutan:hover { color: #f59e0b !important; }\n.panel-cari .border-t { border-color: rgba(255,255,255,0.1) !important; }\n.panel-cari .text-amber-400 { color: #fbbf24 !important; }\n.panel-cari .text-amber-500 { color: #f59e0b !important; }\n\n/* ── Relative time ───────────────────────────────────────── */\n.rel-time { font-size:11px; color:#9ca3af; }\n.rel-edit  { font-size:10px; color:#d97706; }";

        if (strpos($cA, $oldCss) !== false) {
            $cA = str_replace($oldCss, $newCss, $cA);
            $log[] = 'OK [A1] CSS panel-cari ditambah';
        } elseif (strpos($cA, '.panel-cari {') !== false) {
            $log[] = 'SKIP [A1] CSS panel-cari sudah ada';
        } else {
            $errors[] = 'WARN [A1] CSS .rel-time tidak dijumpai — mungkin sudah ditampal versi lain';
        }

        /* HTML section */
        $oldHtml = "{{-- ══ Bar Tapis ════════════════════════════════════════════════════ --}}\n<section class=\"bg-white rounded-xl shadow-sm p-4 mb-5\" aria-labelledby=\"heading-filter\">\n    <h2 id=\"heading-filter\" class=\"sr-only\">Tapis Senarai Tempahan</h2>\n\n    <form method=\"GET\" role=\"search\" aria-label=\"Cari dan tapis tempahan\">";
        $newHtml = "{{-- ══ Bar Tapis ════════════════════════════════════════════════════ --}}\n<section class=\"panel-cari mb-5\" aria-labelledby=\"heading-filter\">\n    <div class=\"panel-cari-title\">\n        <i class=\"fa-solid fa-magnifying-glass\" aria-hidden=\"true\"></i>\n        CARI &amp; TAPIS TEMPAHAN\n    </div>\n    <h2 id=\"heading-filter\" class=\"sr-only\">Tapis Senarai Tempahan</h2>\n\n    <form method=\"GET\" role=\"search\" aria-label=\"Cari dan tapis tempahan\">";

        if (strpos($cA, $oldHtml) !== false) {
            $cA = str_replace($oldHtml, $newHtml, $cA);
            $log[] = 'OK [A2] section HTML dikemaskini';
        } elseif (strpos($cA, 'panel-cari mb-5') !== false) {
            $log[] = 'SKIP [A2] section HTML sudah panel-cari';
        } else {
            $errors[] = 'WARN [A2] section HTML tidak dijumpai — semak manual';
        }

        file_put_contents($fileA, $cA);
        $log[] = 'OK tempahan/index.blade.php disimpan (' . strlen($cA) . ' bytes)';
    }
}

/* ══════════════════════════════════════════════════════════════════
   PATCH B — ketersediaan/index.blade.php: tukar flat bg ke gradient
   ══════════════════════════════════════════════════════════════════ */
$fileB = $base . '/ketersediaan/index.blade.php';
if (!file_exists($fileB)) {
    $errors[] = "FAIL: $fileB tidak dijumpai";
} else {
    $cB = file_get_contents($fileB);

    $oldBg = '#panel-carian { background: #1a1a2e; border-radius: 16px; padding: 28px 32px; }';
    $newBg = '#panel-carian { background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%); border-radius: 16px; padding: 28px 32px; }';

    if (strpos($cB, 'linear-gradient(135deg, #1a1a2e 0%, #0f3460') !== false) {
        $log[] = 'SKIP [B] ketersediaan panel-carian sudah gradient';
    } elseif (strpos($cB, $oldBg) !== false) {
        $cB = str_replace($oldBg, $newBg, $cB);
        file_put_contents($fileB, $cB);
        $log[] = 'OK [B] ketersediaan panel-carian gradient dikemas (' . strlen($cB) . ' bytes)';
    } else {
        $errors[] = 'WARN [B] #panel-carian CSS tidak dijumpai — semak manual';
    }
}

/* ══════════════════════════════════════════════════════════════════
   PATCH C — app.blade.php: override v4 light-mode rule supaya
             #panel-carian dan .panel-cari kekal gradient gelap
   ══════════════════════════════════════════════════════════════════ */
$fileC = __DIR__ . '/resources/views/layouts/app.blade.php';
if (!file_exists($fileC)) {
    $errors[] = "FAIL: $fileC tidak dijumpai";
} else {
    $cC = file_get_contents($fileC);

    if (strpos($cC, 'panel-cari-dark-override') !== false) {
        $log[] = 'SKIP [C] app.blade.php override sudah ada';
    } else {
        /* Sisip SEBELUM </style> terakhir (hujung blok v4/v5/v6) */
        $overrideCss = '
/* panel-cari-dark-override: panel carian kekal gelap walaupun light mode */
html.light #panel-carian {
    background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%) !important;
}
html.light .panel-cari {
    background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%) !important;
}
html.light #panel-carian .form-label { color: #94a3b8 !important; }
html.light #panel-carian .form-hint  { color: #64748b !important; }
';
        $pos = strrpos($cC, '</style>');
        if ($pos === false) {
            $errors[] = 'WARN [C] </style> tidak dijumpai dalam app.blade.php';
        } else {
            $cC = substr($cC, 0, $pos) . $overrideCss . substr($cC, $pos);
            file_put_contents($fileC, $cC);
            $log[] = 'OK [C] app.blade.php override gelap panel-carian ditambah (' . strlen($cC) . ' bytes)';
        }
    }
}

output:
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>fix-panel-cari</title>
<style>body{font-family:monospace;padding:24px;background:#0f172a;color:#e2e8f0}
.ok{color:#4ade80}.warn{color:#fb923c}.err{color:#f87171}
h2{color:#f59e0b;margin-bottom:16px}.done{font-size:1.3em;font-weight:bold;margin-top:20px}
</style></head><body>';
echo '<h2>fix-panel-cari.php</h2><pre>';
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
