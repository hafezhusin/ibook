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

$kategoriSah = ['mesyuarat','perbincangan','taklimat','bengkel','latihan'];
$placeholders = implode(',', array_fill(0, count($kategoriSah), '?'));

$rows = $pdo->prepare("
    SELECT id, nama_mesyuarat, kategori, tarikh, status
    FROM tempahan
    WHERE kategori NOT IN ($placeholders)
    ORDER BY tarikh DESC
");
$rows->execute($kategoriSah);
$data = $rows->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/plain; charset=utf-8');
echo "=== Rekod kategori tidak sepadan ===\n";
echo "Jumlah: " . count($data) . " rekod\n\n";

// Kira mengikut nilai kategori
$kira = [];
foreach ($data as $r) { $kira[$r['kategori']] = ($kira[$r['kategori']] ?? 0) + 1; }
arsort($kira);
echo "--- Mengikut nilai kategori ---\n";
foreach ($kira as $kat => $bil) {
    echo "  '$kat' : $bil rekod\n";
}

echo "\n--- Senarai rekod ---\n";
printf("%-6s %-36s %-14s %-12s %s\n", "ID", "Nama Mesyuarat", "Kategori", "Tarikh", "Status");
echo str_repeat('-', 90) . "\n";
foreach ($data as $r) {
    printf("%-6s %-36s %-14s %-12s %s\n",
        $r['id'],
        mb_strimwidth($r['nama_mesyuarat'], 0, 34, '..'),
        $r['kategori'],
        $r['tarikh'],
        $r['status']
    );
}