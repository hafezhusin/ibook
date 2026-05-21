<?php
/**
 * iBook Deployment Script v3 — path-aware untuk InfinityFree
 * Guna: https://ibookbptm.great-site.net/deploy.php?key=ibook2026deploy
 */

define('DEPLOY_KEY', 'ibook2026deploy');
define('GITHUB_RAW', 'https://raw.githubusercontent.com/hafezhusin/ibook/main');

if (($_GET['key'] ?? '') !== DEPLOY_KEY) {
    http_response_code(403); die('Akses ditolak.');
}

// ── Path detection ──────────────────────────────────────────────────────────
// deploy.php ada dalam web root (htdocs/) — satu level di atas Laravel app root
$webRoot = __DIR__;            // /home/vol9_5/infinityfree.com/if0_41932644/htdocs
$appRoot = dirname(__DIR__);   // /home/vol9_5/infinityfree.com/if0_41932644

// ── Senarai fail: [github_path, absolute_local_path] ───────────────────────
$files = [
    ['resources/views/auth/login.blade.php',
        $appRoot . '/resources/views/auth/login.blade.php'],
    ['resources/views/layouts/app.blade.php',
        $appRoot . '/resources/views/layouts/app.blade.php'],
    ['app/Services/DashboardService.php',
        $appRoot . '/app/Services/DashboardService.php'],
    ['resources/views/dashboard/index.blade.php',
        $appRoot . '/resources/views/dashboard/index.blade.php'],
    ['resources/views/dashboard/_item-mesyuarat.blade.php',
        $appRoot . '/resources/views/dashboard/_item-mesyuarat.blade.php'],
    ['app/Http/Controllers/PenggunaController.php',
        $appRoot . '/app/Http/Controllers/PenggunaController.php'],
    ['resources/views/pengguna/index.blade.php',
        $appRoot . '/resources/views/pengguna/index.blade.php'],
    // Imej: public/ dalam repo = htdocs/ di server
    ['public/images/jata-negara.png',
        $webRoot . '/images/jata-negara.png'],
];

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html><html><head><meta charset="utf-8">
<title>iBook Deploy v3</title>
<style>
body{font-family:monospace;background:#1a1a2e;color:#e5e7eb;padding:30px;max-width:860px;margin:0 auto}
h2{color:#f59e0b}.box{background:#111827;border-radius:8px;padding:16px;margin-top:16px}
pre{margin:0;white-space:pre-wrap;word-break:break-all}
.ok{color:#34d399}.fail{color:#f87171}.info{color:#60a5fa}.warn{color:#fbbf24}
.btn{display:inline-block;background:#f59e0b;color:#1a1a2e;font-weight:bold;
     padding:10px 24px;border-radius:8px;text-decoration:none;margin-top:12px}
.btn-red{background:#991b1b;color:#fff;border:none;padding:8px 16px;
          border-radius:6px;cursor:pointer;margin-top:8px}
</style></head><body>
<h2>&#128640; iBook Deployer v3</h2>
<div class="box"><pre>
<span class="info">Web root (htdocs/) :</span> <?= $webRoot ?>

<span class="info">App root           :</span> <?= $appRoot ?>

<?php
// Sahkan app root betul
$check = $appRoot . '/app/Services/DashboardService.php';
if (file_exists($check)) {
    echo "<span class='ok'>[OK] App root disahkan — DashboardService.php wujud.</span>\n";
} else {
    echo "<span class='fail'>[AMARAN] DashboardService.php tidak jumpa di app root!</span>\n";
    echo "       Path semak: $check\n";
}
?></pre></div>

<?php if (($_GET['deploy'] ?? '') !== '1'): ?>
<div class="box" style="margin-top:16px">
<p style="color:#fbbf24">&#9889; Klik untuk deploy semua 8 fail dari GitHub:</p>
<a class="btn" href="?key=<?= DEPLOY_KEY ?>&deploy=1">&#128640; Deploy Sekarang</a>
</div>
</body></html>
<?php
    exit;
endif;
?>

<div class="box"><pre>
<?php
$ok = 0; $fail = 0;
$ctx = stream_context_create(['http' => [
    'timeout' => 20, 'ignore_errors' => true, 'user_agent' => 'iBook-Deployer/3.0',
]]);

foreach ($files as [$ghPath, $localFull]) {
    $dir = dirname($localFull);

    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    if ( is_dir($dir)) @chmod($dir, 0755);

    // Tarik dari GitHub
    $content  = @file_get_contents(GITHUB_RAW . '/' . $ghPath, false, $ctx);
    $respCode = 0;
    foreach (($http_response_header ?? []) as $h) {
        if (preg_match('/HTTP\/\S+\s+(\d+)/', $h, $m)) $respCode = (int)$m[1];
    }

    if ($content === false || $respCode !== 200) {
        echo "<span class='fail'>[GAGAL TARIK HTTP $respCode]</span> $ghPath\n";
        $fail++; continue;
    }

    if (file_exists($localFull)) @chmod($localFull, 0644);

    $written = @file_put_contents($localFull, $content);
    if ($written === false) {
        $dw = is_writable($dir)       ? 'dir OK' : 'dir TIDAK BOLEH TULIS';
        $fw = file_exists($localFull) ? (is_writable($localFull) ? 'fail OK' : 'fail TIDAK BOLEH TULIS') : 'fail baru';
        echo "<span class='fail'>[GAGAL TULIS]</span> $ghPath ($dw / $fw)\n";
        $fail++;
    } else {
        echo "<span class='ok'>[OK " . number_format($written) . " bait]</span> $ghPath\n";
        $ok++;
    }
    @ob_flush(); flush();
}

// Padam cache view
$cacheDir = $appRoot . '/storage/framework/views';
$cleared = 0;
if (is_dir($cacheDir)) {
    foreach (glob($cacheDir . '/*.php') as $f) @unlink($f) && $cleared++;
}
echo "\n<span class='ok'>[CACHE]</span> $cleared fail cache view dipadamkan.\n";
?>
</pre></div>

<?php $colour = $fail === 0 ? '#34d399' : '#f87171'; ?>
<p style="color:<?= $colour ?>;font-size:1.1em;font-weight:bold">
    Selesai: <?= $ok ?> berjaya, <?= $fail ?> gagal.
</p>

<?php if ($fail === 0): ?>
<form method="post" action="?key=<?= DEPLOY_KEY ?>&delete=1">
    <button class="btn-red">&#128465; Padam deploy.php sekarang</button>
</form>
<?php endif; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['delete'] ?? '') === '1') {
    @unlink(__FILE__);
    echo '<p style="color:#34d399"><b>deploy.php telah dipadamkan.</b></p>';
}
?>
</body></html>
