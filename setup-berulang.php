<?php
/**
 * setup-berulang.php — Cipta jadual tempahan_berulang + FK di production
 * Akses: https://ibookbptm.great-site.net/setup-berulang.php?key=ibook2026deploy
 * PADAM selepas selesai!
 */
define('PATCH_KEY', 'ibook2026deploy');
if (($_GET['key'] ?? '') !== PATCH_KEY) { http_response_code(403); die('Akses ditolak.'); }

header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Setup Berulang</title>
<style>
body{font-family:monospace;background:#1a1a2e;color:#e5e7eb;padding:30px}
h2{color:#f59e0b}
.ok{color:#34d399}.fail{color:#f87171}.info{color:#60a5fa}.skip{color:#94a3b8}
pre{background:#0f172a;padding:20px;border-radius:8px}
</style></head><body>
<h2>&#128295; iBook — Setup Tempahan Berulang (DB)</h2><pre>';

// Cari .env
$envFile = null;
foreach ([__DIR__ . '/.env', __DIR__ . '/../.env'] as $loc) {
    if (file_exists($loc)) { $envFile = realpath($loc); break; }
}
if (!$envFile) { echo "<span class='fail'>[GAGAL]</span> .env tidak dijumpai."; echo '</pre></body></html>'; exit; }

// Parse .env
$dbHost = $dbName = $dbUser = $dbPass = '';
foreach (file($envFile) as $b) {
    $b = trim($b);
    if (str_starts_with($b, 'DB_HOST='))     $dbHost = trim(substr($b, 8),  " \t\"'");
    if (str_starts_with($b, 'DB_DATABASE=')) $dbName = trim(substr($b, 12), " \t\"'");
    if (str_starts_with($b, 'DB_USERNAME=')) $dbUser = trim(substr($b, 12), " \t\"'");
    if (str_starts_with($b, 'DB_PASSWORD=')) $dbPass = trim(substr($b, 12), " \t\"'");
}
echo "<span class='info'>[INFO]</span> DB: {$dbUser}@{$dbHost}/{$dbName}\n\n";

$ok = 0; $fail = 0;

try {
    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // ── STEP 1: Cipta jadual tempahan_berulang ────────────────────────
    echo "── STEP 1: Jadual tempahan_berulang ──\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `tempahan_berulang` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `ulid` VARCHAR(26) NOT NULL,
        `jenis` ENUM('mingguan','bulanan') NOT NULL,
        `setiap_n` TINYINT UNSIGNED NOT NULL DEFAULT 1,
        `hari_dalam_minggu` JSON NULL,
        `tarikh_mula` DATE NOT NULL,
        `tarikh_tamat` DATE NOT NULL,
        `sesi` JSON NOT NULL,
        `bilik_id` BIGINT UNSIGNED NOT NULL,
        `user_id` BIGINT UNSIGNED NOT NULL,
        `nama_mesyuarat` VARCHAR(255) NOT NULL,
        `bilangan_peserta` INT NOT NULL DEFAULT 1,
        `kategori` VARCHAR(255) NOT NULL,
        `nama_pengerusi` VARCHAR(255) NOT NULL,
        `tujuan` TEXT NULL,
        `created_at` TIMESTAMP NULL DEFAULT NULL,
        `updated_at` TIMESTAMP NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `tempahan_berulang_ulid_unique` (`ulid`),
        KEY `idx_tb_bilik` (`bilik_id`),
        KEY `idx_tb_user` (`user_id`),
        CONSTRAINT `fk_tb_bilik` FOREIGN KEY (`bilik_id`) REFERENCES `bilik_mesyuarat`(`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_tb_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "<span class='ok'>[OK]</span> Jadual tempahan_berulang sedia.\n\n";
    $ok++;

    // ── STEP 2: Tambah lajur tempahan_berulang_id ke tempahan ─────────
    echo "── STEP 2: Lajur tempahan_berulang_id ──\n";
    $cols = $pdo->query("SHOW COLUMNS FROM `tempahan` LIKE 'tempahan_berulang_id'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE `tempahan`
            ADD COLUMN `tempahan_berulang_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `ulid`,
            ADD KEY `idx_tempahan_berulang_id` (`tempahan_berulang_id`),
            ADD CONSTRAINT `fk_tempahan_berulang`
                FOREIGN KEY (`tempahan_berulang_id`)
                REFERENCES `tempahan_berulang`(`id`)
                ON DELETE SET NULL");
        echo "<span class='ok'>[OK]</span> Lajur tempahan_berulang_id ditambah ke tempahan.\n\n";
        $ok++;
    } else {
        echo "<span class='skip'>[SKIP]</span> Lajur tempahan_berulang_id sudah wujud.\n\n";
        $ok++;
    }

    // ── STEP 3: Padam migrations cache (bukan run) ────────────────────
    echo "── STEP 3: Semak jadual ──\n";
    $jadual = $pdo->query("SHOW TABLES LIKE 'tempahan_berulang'")->fetchAll();
    echo count($jadual) > 0
        ? "<span class='ok'>[OK]</span> tempahan_berulang wujud dalam DB.\n"
        : "<span class='fail'>[???]</span> tempahan_berulang tidak dijumpai — semak manual.\n";
    $cols2 = $pdo->query("SHOW COLUMNS FROM `tempahan` LIKE 'tempahan_berulang_id'")->fetchAll();
    echo count($cols2) > 0
        ? "<span class='ok'>[OK]</span> tempahan.tempahan_berulang_id wujud.\n\n"
        : "<span class='fail'>[???]</span> tempahan.tempahan_berulang_id tidak dijumpai.\n\n";
    $ok++;

} catch (Exception $e) {
    echo "<span class='fail'>[GAGAL]</span> " . htmlspecialchars($e->getMessage()) . "\n\n";
    $fail++;
}

// Padam config cache
$cc = __DIR__ . '/bootstrap/cache/config.php';
if (file_exists($cc) && @unlink($cc)) echo "<span class='ok'>[OK]</span> Config cache dipadam.\n";

// Clear blade cache
$views = __DIR__ . '/storage/framework/views';
$cl = 0;
if (is_dir($views)) foreach (glob($views . '/*.php') as $f) if (@unlink($f)) $cl++;
echo "<span class='ok'>[OK]</span> $cl fail blade cache dipadam.\n\n";

$warna = $fail > 0 ? '#f87171' : '#34d399';
echo "────────────────────────────────────────────\n";
echo "<span style='color:$warna'><b>Selesai: $ok berjaya, $fail gagal.</b></span>\n";
echo '</pre>';

echo '<form method="post" action="?key=' . PATCH_KEY . '&delete=1" style="margin-top:16px">
<button style="background:#991b1b;color:#fff;border:none;padding:8px 18px;border-radius:6px;cursor:pointer;font-size:14px">
&#128465; Padam setup-berulang.php sekarang
</button></form>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['delete'] ?? '') === '1') {
    @unlink(__FILE__);
    echo '<p style="color:#34d399;margin-top:8px;font-family:monospace">&#10003; setup-berulang.php telah dipadamkan.</p>';
}
echo '</body></html>';
