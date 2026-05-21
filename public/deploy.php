<?php
/**
 * iBook Deployment Script v2 — dengan diagnostik penuh
 * Guna: https://ibookbptm.great-site.net/deploy.php?key=ibook2026deploy
 */

define('DEPLOY_KEY', 'ibook2026deploy');
define('GITHUB_RAW',  'https://raw.githubusercontent.com/hafezhusin/ibook/main');

// ── Auth ───────────────────────────────────────────────────────────────────
if (($_GET['key'] ?? '') !== DEPLOY_KEY) {
    http_response_code(403);
    die('Akses ditolak.');
}

header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><meta charset="utf-8">
<title>iBook Deploy v2</title>
<style>
  body{font-family:monospace;background:#1a1a2e;color:#e5e7eb;padding:30px;max-width:900px;margin:0 auto}
  h2{color:#f59e0b}
  .ok{color:#34d399}.fail{color:#f87171}.warn{color:#fbbf24}.info{color:#60a5fa}
  .box{background:#111827;border-radius:8px;padding:16px;margin-top:16px}
  pre{margin:0;white-space:pre-wrap;word-break:break-all}
</style></head><body>
<h2>&#128640; iBook Deployer v2 — Diagnostik</h2>';

// ── Diagnostik path ────────────────────────────────────────────────────────
echo '<div class="box"><pre>';
echo "<span class='info'>__FILE__     :</span> " . __FILE__ . "\n";
echo "<span class='info'>__DIR__      :</span> " . __DIR__ . "\n";
echo "<span class='info'>dirname DIR  :</span> " . dirname(__DIR__) . "\n";

// Cuba 3 kemungkinan BASE_PATH
$candidates = [
    dirname(__DIR__),                    // /htdocs  (standard)
    dirname(dirname(__DIR__)),           // satu lagi ke atas
    __DIR__,                             // /htdocs/public
    '/home/' . get_current_user() . '/htdocs',
    realpath(dirname(__DIR__)),
];

$basePath = null;
foreach ($candidates as $c) {
    $test = $c . '/app/Services/DashboardService.php';
    $exists = file_exists($test);
    $mark = $exists ? "<span class='ok'>[WUJUD]</span>" : "<span class='fail'>[TIADA]</span>";
    echo "$mark  $c/app/Services/DashboardService.php\n";
    if ($exists && $basePath === null) {
        $basePath = $c;
    }
}

echo "\n<span class='info'>PHP user     :</span> " . get_current_user() . " / " . (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'N/A') . "\n";
echo "<span class='info'>allow_url_fopen:</span> " . (ini_get('allow_url_fopen') ? 'Ya' : 'Tidak') . "\n";

if ($basePath === null) {
    // Cuba scan manual
    echo "\n<span class='warn'>Tidak jumpa base path. Scan manual...</span>\n";
    foreach (['/htdocs', '/home', '/var/www', '/srv', '/opt'] as $root) {
        if (is_dir($root)) {
            $found = glob($root . '/*/app/Services/DashboardService.php');
            if (!empty($found)) {
                $basePath = dirname(dirname(dirname($found[0])));
                echo "<span class='ok'>Jumpa di: $basePath</span>\n";
                break;
            }
            // Try one level deeper
            foreach (glob($root . '/*', GLOB_ONLYDIR) as $d) {
                $found = glob($d . '/*/app/Services/DashboardService.php');
                if (!empty($found)) {
                    $basePath = dirname(dirname(dirname($found[0])));
                    echo "<span class='ok'>Jumpa di: $basePath</span>\n";
                    break 2;
                }
            }
        }
    }
}

if ($basePath === null) {
    echo "</pre></div><p class='fail'>GAGAL: Tidak dapat kesan base path Laravel. Sila hubungi admin.</p></body></html>";
    exit;
}

echo "\n<span class='ok'>BASE_PATH    : $basePath</span>\n";

// Semak tulis
$testFile = $basePath . '/storage/app/.deploy_test_' . time();
$canWrite = @file_put_contents($testFile, 'test') !== false;
if ($canWrite) {
    @unlink($testFile);
    echo "<span class='ok'>Write test   : BOLEH TULIS</span>\n";
} else {
    echo "<span class='fail'>Write test   : TIDAK BOLEH TULIS ke storage/app/</span>\n";
}

// Semak boleh tulis ke resources/
$testFile2 = $basePath . '/resources/.deploy_test_' . time();
$canWrite2 = @file_put_contents($testFile2, 'test') !== false;
if ($canWrite2) {
    @unlink($testFile2);
    echo "<span class='ok'>Write resources/: BOLEH TULIS</span>\n";
} else {
    echo "<span class='fail'>Write resources/: TIDAK BOLEH TULIS</span>\n";
    // Cuba chmod
    @chmod($basePath . '/resources', 0755);
    $canWrite2 = @file_put_contents($testFile2, 'test') !== false;
    if ($canWrite2) {
        @unlink($testFile2);
        echo "<span class='ok'>Write resources/ selepas chmod: BOLEH TULIS</span>\n";
    }
}
echo '</pre></div>';

// ── Jika ada ?deploy=1, jalankan deployment ────────────────────────────────
if (($_GET['deploy'] ?? '') !== '1') {
    echo '<div class="box" style="margin-top:20px">
        <p style="color:#fbbf24">&#9888; Diagnostik selesai. Untuk jalankan deployment, klik:</p>
        <a href="?key=' . DEPLOY_KEY . '&deploy=1"
           style="display:inline-block;background:#f59e0b;color:#1a1a2e;font-weight:bold;
                  padding:10px 24px;border-radius:8px;text-decoration:none;margin-top:8px">
           &#128640; Jalankan Deployment Sekarang
        </a>
    </div></body></html>';
    exit;
}

// ── Senarai fail ───────────────────────────────────────────────────────────
$files = [
    ['resources/views/auth/login.blade.php',                'resources/views/auth/login.blade.php'],
    ['resources/views/layouts/app.blade.php',               'resources/views/layouts/app.blade.php'],
    ['app/Services/DashboardService.php',                   'app/Services/DashboardService.php'],
    ['resources/views/dashboard/index.blade.php',           'resources/views/dashboard/index.blade.php'],
    ['resources/views/dashboard/_item-mesyuarat.blade.php', 'resources/views/dashboard/_item-mesyuarat.blade.php'],
    ['app/Http/Controllers/PenggunaController.php',         'app/Http/Controllers/PenggunaController.php'],
    ['resources/views/pengguna/index.blade.php',            'resources/views/pengguna/index.blade.php'],
    ['public/images/jata-negara.png',                       'public/images/jata-negara.png'],
];

echo '<div class="box"><pre>';
$ok = 0; $fail = 0;

$ctx = stream_context_create(['http' => [
    'timeout'      => 20,
    'ignore_errors'=> true,
    'user_agent'   => 'iBook-Deployer/2.0',
]]);

foreach ($files as [$ghPath, $localRel]) {
    $url       = GITHUB_RAW . '/' . $ghPath;
    $localFull = $basePath  . '/' . $localRel;
    $dir       = dirname($localFull);

    // Pastikan direktori wujud & boleh tulis
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    if (is_dir($dir)) {
        @chmod($dir, 0755);
    }

    // Tarik dari GitHub
    $content  = @file_get_contents($url, false, $ctx);
    $respCode = 0;
    foreach (($http_response_header ?? []) as $h) {
        if (preg_match('/HTTP\/\S+\s+(\d+)/', $h, $m)) $respCode = (int)$m[1];
    }

    if ($content === false || $respCode !== 200) {
        echo "<span class='fail'>[GAGAL TARIK HTTP $respCode]</span> $localRel\n";
        $fail++; continue;
    }

    // Chmod fail sedia ada supaya boleh overwrite
    if (file_exists($localFull)) {
        @chmod($localFull, 0644);
    }

    $written = @file_put_contents($localFull, $content);
    if ($written === false) {
        // Cuba semak sebab
        $dirWritable  = is_writable($dir)       ? 'boleh tulis' : 'TIDAK boleh tulis';
        $fileWritable = file_exists($localFull)
                      ? (is_writable($localFull) ? 'boleh tulis' : 'TIDAK boleh tulis')
                      : 'fail tiada (baru)';
        echo "<span class='fail'>[GAGAL TULIS]</span> $localRel\n";
        echo "  dir: $dir → $dirWritable\n";
        echo "  fail: $fileWritable\n";
        $fail++;
    } else {
        echo "<span class='ok'>[OK " . number_format($written) . " bait]</span> $localRel\n";
        $ok++;
    }
}

// Padam cache view
$cacheDir = $basePath . '/storage/framework/views';
$cleared  = 0;
if (is_dir($cacheDir)) {
    foreach (glob($cacheDir . '/*.php') as $f) {
        @unlink($f) && $cleared++;
    }
}
echo "\n<span class='ok'>[CACHE]</span> $cleared fail cache view dipadamkan.\n";
echo '</pre></div>';

$colour = $fail === 0 ? '#34d399' : '#f87171';
echo "<p style='color:$colour;font-size:1.1em;font-weight:bold'>Selesai: $ok berjaya, $fail gagal.</p>";

if ($fail === 0) {
    echo '<form method="post" action="?key=' . DEPLOY_KEY . '&delete=1">
          <button style="background:#991b1b;color:#fff;border:none;padding:8px 16px;
                         border-radius:6px;cursor:pointer">Padam deploy.php</button></form>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['delete'] ?? '') === '1') {
    @unlink(__FILE__);
    echo '<p style="color:#34d399"><b>deploy.php dipadamkan.</b></p>';
}
echo '</body></html>';
