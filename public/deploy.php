<?php
/**
 * iBook Deployment Script v4
 * Struktur InfinityFree: seluruh app Laravel dalam /htdocs/
 * Guna: https://ibookbptm.great-site.net/deploy.php?key=ibook2026deploy
 */

define('DEPLOY_KEY', 'ibook2026deploy');
define('GITHUB_RAW', 'https://raw.githubusercontent.com/hafezhusin/ibook/main');

if (($_GET['key'] ?? '') !== DEPLOY_KEY) {
    http_response_code(403); die('Akses ditolak.');
}

// Seluruh app ada dalam htdocs/ — __DIR__ adalah base untuk semua fail
$base = __DIR__;  // /home/vol9_5/infinityfree.com/if0_41932644/htdocs

// [github_path_in_repo, local_path_relative_to_base]
// Nota: public/images/* → terus dalam htdocs/images/* (tiada subfolder public/)
$files = [
    ['resources/views/auth/login.blade.php',
        'resources/views/auth/login.blade.php'],
    ['resources/views/layouts/app.blade.php',
        'resources/views/layouts/app.blade.php'],
    ['app/Services/DashboardService.php',
        'app/Services/DashboardService.php'],
    ['resources/views/dashboard/index.blade.php',
        'resources/views/dashboard/index.blade.php'],
    ['resources/views/dashboard/_item-mesyuarat.blade.php',
        'resources/views/dashboard/_item-mesyuarat.blade.php'],
    ['app/Http/Controllers/PenggunaController.php',
        'app/Http/Controllers/PenggunaController.php'],
    ['resources/views/pengguna/index.blade.php',
        'resources/views/pengguna/index.blade.php'],
    // public/images/ dalam repo = terus images/ dalam htdocs/
    ['public/images/jata-negara.png',
        'images/jata-negara.png'],
];

header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html><html><head><meta charset="utf-8">
<title>iBook Deploy v4</title>
<style>
body{font-family:monospace;background:#1a1a2e;color:#e5e7eb;padding:30px;max-width:900px;margin:0 auto}
h2{color:#f59e0b}.box{background:#111827;border-radius:8px;padding:16px;margin-top:16px}
pre{margin:0;white-space:pre-wrap;word-break:break-all}
.ok{color:#34d399}.fail{color:#f87171}.info{color:#60a5fa}.warn{color:#fbbf24}
.btn{display:inline-block;background:#f59e0b;color:#1a1a2e;font-weight:bold;padding:10px 24px;border-radius:8px;text-decoration:none;margin-top:12px}
.btn-del{background:#991b1b;color:#fff;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;margin-top:8px;font-family:monospace}
</style></head><body>
<h2>&#128640; iBook Deployer v4</h2>
<div class="box"><pre>
<span class="info">Base (htdocs/) :</span> <?= $base ?>

<?php
$check = $base . '/app/Services/DashboardService.php';
if (file_exists($check)) {
    echo "<span class='ok'>[OK] App root disahkan.</span>\n";
} else {
    echo "<span class='warn'>[AMARAN] DashboardService.php tidak jumpa. Akan cuba cipta fail baru.</span>\n";
}
$canWrite = @file_put_contents($base . '/.wtest', 'x') !== false;
@unlink($base . '/.wtest');
echo $canWrite
    ? "<span class='ok'>[OK] Direktori htdocs/ boleh ditulis.</span>\n"
    : "<span class='fail'>[GAGAL] htdocs/ TIDAK boleh ditulis! Semak permission.</span>\n";
?></pre></div>

<div class="box"><pre>
<?php
$ok = 0; $fail = 0;
$ctx = stream_context_create(['http' => [
    'timeout' => 20, 'ignore_errors' => true, 'user_agent' => 'iBook-Deployer/4.0',
]]);

foreach ($files as [$ghPath, $localRel]) {
    $localFull = $base . '/' . $localRel;
    $dir = dirname($localFull);

    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    if ( is_dir($dir)) @chmod($dir, 0755);

    $content  = @file_get_contents(GITHUB_RAW . '/' . $ghPath, false, $ctx);
    $respCode = 0;
    foreach (($http_response_header ?? []) as $h) {
        if (preg_match('/HTTP\/\S+\s+(\d+)/', $h, $m)) $respCode = (int)$m[1];
    }

    if ($content === false || $respCode !== 200) {
        echo "<span class='fail'>[GAGAL TARIK HTTP $respCode]</span> $localRel\n";
        $fail++; continue;
    }

    if (file_exists($localFull)) @chmod($localFull, 0644);
    $written = @file_put_contents($localFull, $content);

    if ($written === false) {
        $dw = is_writable($dir)       ? 'dir OK' : 'dir TIDAK BOLEH TULIS';
        $fw = file_exists($localFull) ? (is_writable($localFull) ? 'fail OK':'fail READ-ONLY') : 'fail baru';
        echo "<span class='fail'>[GAGAL TULIS]</span> $localRel ($dw / $fw)\n";
        $fail++;
    } else {
        echo "<span class='ok'>[OK " . number_format($written) . " bait]</span> $localRel\n";
        $ok++;
    }
    @ob_flush(); flush();
}

// Padam cache view
$cacheDir = $base . '/storage/framework/views';
$cleared = 0;
if (is_dir($cacheDir)) {
    foreach (glob($cacheDir . '/*.php') as $f) @unlink($f) && $cleared++;
}
echo "\n<span class='ok'>[CACHE]</span> $cleared fail cache view dipadamkan.\n";
?>
</pre></div>

<?php $colour = $fail === 0 ? '#34d399' : '#f87171'; ?>
<p style="color:<?= $colour ?>;font-size:1.1em;font-weight:bold;margin-top:20px">
    Selesai: <?= $ok ?> berjaya, <?= $fail ?> gagal.
</p>

<?php if ($fail === 0): ?>
<form method="post" action="?key=<?= DEPLOY_KEY ?>&delete=1">
    <button class="btn-del">&#128465; Padam deploy.php sekarang</button>
</form>
<?php endif; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['delete'] ?? '') === '1') {
    @unlink(__FILE__);
    echo '<p style="color:#34d399"><b>&#10003; deploy.php telah dipadamkan.</b></p>';
}
?>
</body></html>
