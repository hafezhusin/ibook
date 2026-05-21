<?php
/**
 * iBook Deployment Script
 * Tarik fail terkini dari GitHub dan tulis ke hosting.
 * PADAM fail ini selepas deployment berjaya.
 *
 * Guna: https://ibookbptm.great-site.net/deploy.php?key=ibook2026deploy
 */

define('DEPLOY_KEY', 'ibook2026deploy');
define('GITHUB_RAW',  'https://raw.githubusercontent.com/hafezhusin/ibook/main');
define('BASE_PATH',   dirname(__DIR__)); // /htdocs/..  → satu folder atas public/

// ── Auth ───────────────────────────────────────────────────────────────────
if (($_GET['key'] ?? '') !== DEPLOY_KEY) {
    http_response_code(403);
    die('Akses ditolak.');
}

// ── Senarai fail untuk ditarik ─────────────────────────────────────────────
$files = [
    // [ github_path, local_path_relative_to_BASE_PATH ]
    ['resources/views/auth/login.blade.php',               'resources/views/auth/login.blade.php'],
    ['resources/views/layouts/app.blade.php',              'resources/views/layouts/app.blade.php'],
    ['app/Services/DashboardService.php',                  'app/Services/DashboardService.php'],
    ['resources/views/dashboard/index.blade.php',          'resources/views/dashboard/index.blade.php'],
    ['resources/views/dashboard/_item-mesyuarat.blade.php','resources/views/dashboard/_item-mesyuarat.blade.php'],
    ['app/Http/Controllers/PenggunaController.php',        'app/Http/Controllers/PenggunaController.php'],
    ['resources/views/pengguna/index.blade.php',           'resources/views/pengguna/index.blade.php'],
    ['public/images/jata-negara.png',                      'public/images/jata-negara.png'],
];

// ── Jalankan ────────────────────────────────────────────────────────────────
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><meta charset="utf-8">
<title>iBook Deploy</title>
<style>
  body{font-family:monospace;background:#1a1a2e;color:#e5e7eb;padding:30px;max-width:800px;margin:0 auto}
  h2{color:#f59e0b}
  .ok{color:#34d399} .fail{color:#f87171} .skip{color:#9ca3af}
  .box{background:#111827;border-radius:8px;padding:16px;margin-top:20px}
  .summary{font-size:1.1em;font-weight:bold;margin-top:24px}
</style></head><body>
<h2>&#128640; iBook GitHub Deployer</h2>
<div class="box"><pre>';

$ok = 0; $fail = 0;
$ctx = stream_context_create(['http' => [
    'timeout'        => 15,
    'ignore_errors'  => true,
    'user_agent'     => 'iBook-Deployer/1.0',
]]);

foreach ($files as [$ghPath, $localRel]) {
    $url       = GITHUB_RAW . '/' . $ghPath;
    $localFull = BASE_PATH  . '/' . $localRel;
    $dir       = dirname($localFull);

    // Pastikan direktori wujud
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    // Tarik dari GitHub
    $content = @file_get_contents($url, false, $ctx);

    // Semak respons
    $respCode = 0;
    if (isset($http_response_header)) {
        foreach ($http_response_header as $h) {
            if (preg_match('/HTTP\/\d+\.?\d*\s+(\d+)/', $h, $m)) {
                $respCode = (int) $m[1];
            }
        }
    }

    if ($content === false || $respCode !== 200) {
        echo "<span class='fail'>[GAGAL]</span> $localRel (HTTP $respCode)\n";
        $fail++;
        continue;
    }

    // Tulis ke disk
    $written = file_put_contents($localFull, $content);
    if ($written === false) {
        echo "<span class='fail'>[GAGAL TULIS]</span> $localRel\n";
        $fail++;
    } else {
        echo "<span class='ok'>[OK " . number_format($written) . " bait]</span> $localRel\n";
        $ok++;
    }
}

// ── Padam cache view ────────────────────────────────────────────────────────
$cacheDir = BASE_PATH . '/storage/framework/views';
$cleared  = 0;
if (is_dir($cacheDir)) {
    foreach (glob($cacheDir . '/*.php') as $f) {
        @unlink($f) && $cleared++;
    }
}
echo "\n<span class='ok'>[CACHE]</span> $cleared fail cache view dipadamkan.\n";

// ── Ringkasan ───────────────────────────────────────────────────────────────
echo '</pre></div>';
$colour = $fail === 0 ? '#34d399' : '#f87171';
echo "<p class='summary' style='color:$colour'>Selesai: $ok berjaya, $fail gagal.</p>";

if ($fail === 0) {
    echo '<p style="color:#9ca3af;font-size:.85em">&#9888;&#65039; INGAT: Padam fail ini dari server selepas deployment!</p>';
    echo '<form method="post" action="?key=' . DEPLOY_KEY . '&delete=1">
          <button style="background:#991b1b;color:#fff;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;margin-top:8px">
          Padam deploy.php sekarang</button></form>';
}

// ── Padam diri sendiri bila diminta ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['delete'] ?? '') === '1') {
    @unlink(__FILE__);
    echo '<p style="color:#34d399"><b>deploy.php telah dipadamkan.</b></p>';
}

echo '</body></html>';
