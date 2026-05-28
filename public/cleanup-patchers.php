<?php

/**
 * cleanup-patchers.php — JALANKAN SEKALI SAHAJA, ia akan padam dirinya sendiri.
 * Membuang semua fail patcher dari public/ dan root projek.
 *
 * URL: https://ibookbptm.great-site.net/cleanup-patchers.php?key=ibook2026deploy
 */
if (($_GET['key'] ?? '') !== 'ibook2026deploy') {
    http_response_code(403);
    exit('Forbidden');
}

$log = [];
$errors = [];

/* ── Fail dalam public/ untuk dipadam ── */
$publicFiles = [
    __DIR__.'/fix-2fa.php',
    __DIR__.'/fix-audit-improvements.php',
    __DIR__.'/fix-chart-lightmode.php',
    __DIR__.'/fix-dark-mode-toggle.php',
    __DIR__.'/fix-header-light-mode.php',
    __DIR__.'/fix-kategori-kursus.php',
    __DIR__.'/fix-kategori.php',
    __DIR__.'/fix-laporan-eksport.php',
    __DIR__.'/fix-light-mode-v4.php',
    __DIR__.'/fix-light-mode-v5.php',
    __DIR__.'/fix-light-mode-v6.php',
    __DIR__.'/fix-panel-cari-v2.php',
    __DIR__.'/fix-panel-cari.php',
    __DIR__.'/fix-panel-dark.php',
    __DIR__.'/fix-panel-text.php',
    __DIR__.'/fix-recovery.php',
    __DIR__.'/fix-security.php',
    __DIR__.'/fix-tempahan-berulang.php',
    __DIR__.'/fix-panel-text.php',
    __DIR__.'/migrate-kategori.php',
    __DIR__.'/diag-kategori.php',
    __DIR__.'/deploy.php',
    __DIR__.'/patch-server.php',
    __DIR__.'/patch-smtp.php',
    __DIR__.'/patch-sesi.php',
    __DIR__.'/patch-session.php',
    __DIR__.'/seeder-pengguna.php',
];

/* ── Fail dalam root projek untuk dipadam ── */
$root = dirname(__DIR__);
$rootFiles = [
    $root.'/chk-index.php',
    $root.'/deploy-berulang.php',
    $root.'/deploy-jadual-minggu.php',
    $root.'/diag-berulang.php',
    $root.'/diag2-berulang.php',
    $root.'/fix-controller.php',
    $root.'/fix-create-loading.php',
    $root.'/fix-index.php',
    $root.'/fix-index2.php',
    $root.'/fix-prefill-tempahan.php',
    $root.'/fix-sehari-penuh.php',
    $root.'/setup-berulang.php',
];

$allFiles = array_merge($publicFiles, $rootFiles);

foreach ($allFiles as $f) {
    $shortName = str_replace([$root.'/', __DIR__.'/'], ['root/', 'public/'], $f);
    if (! file_exists($f)) {
        $log[] = ['skip', $shortName.' — tidak wujud, skip'];

        continue;
    }
    if (@unlink($f)) {
        $log[] = ['ok', 'PADAM: '.$shortName];
    } else {
        $errors[] = 'GAGAL padam: '.$shortName;
    }
}

/* ── Padam diri sendiri (TERAKHIR) ── */
register_shutdown_function(function () {
    @unlink(__FILE__);
});

/* ── Output ── */
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Cleanup Patchers</title>
<style>
body { font-family: monospace; padding: 24px; background: #0f172a; color: #e2e8f0; }
.ok   { color: #4ade80; }
.skip { color: #94a3b8; }
.err  { color: #f87171; }
h2   { color: #f59e0b; margin-bottom: 16px; }
.done { font-size: 1.3em; font-weight: bold; margin-top: 20px; }
.box  { background: #1e293b; border-radius: 8px; padding: 16px; margin-top: 16px; }
</style></head><body>';

echo '<h2>🧹 Cleanup Patchers</h2>';
echo '<div class="box"><pre>';
foreach ($log as [$type, $msg]) {
    echo '<span class="'.$type.'">'.htmlspecialchars($msg).'</span>'."\n";
}
foreach ($errors as $e) {
    echo '<span class="err">'.htmlspecialchars($e).'</span>'."\n";
}
echo '</pre></div>';

$ok = empty($errors);
echo '<div class="done" style="color:'.($ok ? '#4ade80' : '#fb923c').'">'
    .($ok ? '✓ Semua patcher berjaya dipadam. Fail ini juga akan dipadam selepas ini.'
           : '⚠ Ada kegagalan — semak manual').'</div>';

echo '<p style="color:#64748b;margin-top:12px;font-size:12px">Fail ini akan memadam dirinya sendiri secara automatik.</p>';
echo '</body></html>';
