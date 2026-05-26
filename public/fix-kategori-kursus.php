<?php
if (($_GET['key'] ?? '') !== 'ibook2026deploy') { http_response_code(403); exit('Forbidden'); }

$root = __DIR__;
$env  = @file_get_contents($root . '/.env');
preg_match('/DB_HOST=(.+)/',     $env, $h);
preg_match('/DB_PORT=(.+)/',     $env, $p);
preg_match('/DB_DATABASE=(.+)/', $env, $d);
preg_match('/DB_USERNAME=(.+)/', $env, $u);
preg_match('/DB_PASSWORD=(.+)/', $env, $pw);

$pdo = new PDO(
    "mysql:host=" . trim($h[1]) . ";port=" . trim($p[1]??'3306') . ";dbname=" . trim($d[1]) . ";charset=utf8mb4",
    trim($u[1]), trim($pw[1]),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

header('Content-Type: text/plain; charset=utf-8');
echo "=== fix-kategori-kursus.php ===\n";
echo "Masa: " . date('Y-m-d H:i:s') . "\n\n";

// Cari semua rekod nama mesyuarat bermula dengan "kursus" (tidak kira huruf besar/kecil)
$stmt = $pdo->prepare("
    SELECT id, nama_mesyuarat, kategori
    FROM tempahan
    WHERE nama_mesyuarat LIKE 'kursus%'
    ORDER BY tarikh DESC
");
$stmt->execute();
$rekod = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Rekod ditemui (nama bermula 'kursus'): " . count($rekod) . "\n\n";

if (empty($rekod)) {
    echo "Tiada rekod perlu dikemaskini.\n";
    @unlink(__FILE__);
    echo "Dipadam: " . basename(__FILE__) . "\n";
    exit;
}

echo sprintf("%-6s %-42s %-16s %s\n", "ID", "Nama Mesyuarat", "Kategori Lama", "Tindakan");
echo str_repeat('-', 85) . "\n";

$update = $pdo->prepare("UPDATE tempahan SET kategori = 'latihan' WHERE id = ?");
$dikemaskini = 0;
$dilewati    = 0;

foreach ($rekod as $r) {
    if ($r['kategori'] === 'latihan') {
        echo sprintf("%-6s %-42s %-16s %s\n",
            $r['id'],
            mb_strimwidth($r['nama_mesyuarat'], 0, 40, '..'),
            $r['kategori'],
            'SKIP (sudah latihan)'
        );
        $dilewati++;
    } else {
        $update->execute([$r['id']]);
        echo sprintf("%-6s %-42s %-16s %s\n",
            $r['id'],
            mb_strimwidth($r['nama_mesyuarat'], 0, 40, '..'),
            $r['kategori'],
            '→ latihan'
        );
        $dikemaskini++;
    }
}

echo "\n--- Ringkasan ---\n";
echo "Dikemaskini : $dikemaskini rekod\n";
echo "Dilewati    : $dilewati rekod (sudah betul)\n";

echo "\n=== SELESAI ===\n";
@unlink(__FILE__);
echo "Dipadam: " . basename(__FILE__) . "\n";