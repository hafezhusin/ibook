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

// ── Kategori sah baharu ──────────────────────────────────────────────
$kategoriSah = ['mesyuarat','perbincangan','taklimat','bengkel','latihan'];
$placeholders = implode(',', array_fill(0, count($kategoriSah), '?'));

// ── Ambil semua rekod tidak sepadan ─────────────────────────────────
$stmt = $pdo->prepare("
    SELECT id, nama_mesyuarat, kategori
    FROM tempahan
    WHERE kategori NOT IN ($placeholders)
");
$stmt->execute($kategoriSah);
$rekod = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Fungsi teka kategori dari nama mesyuarat ─────────────────────────
function tekaKategori(string $nama): string {
    $n = mb_strtolower($nama);
    if (preg_match('/bengkel|workshop|seminar/u', $n))      return 'bengkel';
    if (preg_match('/latihan|kursus|takwim/u', $n))         return 'latihan';
    if (preg_match('/taklimat|brifing|briefing/u', $n))     return 'taklimat';
    if (preg_match('/perbincangan|diskusi|sesi/u', $n))     return 'perbincangan';
    if (preg_match('/mesyuarat|meeting|jawatankuasa|jk\b|majlis/u', $n)) return 'mesyuarat';
    return 'mesyuarat'; // fallback
}

// ── Kira pemetaan dahulu (dry-run) ──────────────────────────────────
$peta = [];
foreach ($rekod as $r) {
    $baharu = tekaKategori($r['nama_mesyuarat']);
    $peta[] = [
        'id'     => $r['id'],
        'nama'   => $r['nama_mesyuarat'],
        'lama'   => $r['kategori'],
        'baharu' => $baharu,
    ];
}

header('Content-Type: text/plain; charset=utf-8');
echo "=== migrate-kategori.php ===\n";
echo "Masa: " . date('Y-m-d H:i:s') . "\n";
echo "Rekod diproses: " . count($peta) . "\n\n";

if (empty($peta)) {
    echo "Tiada rekod perlu dimigrate.\n";
    @unlink(__FILE__);
    echo "Dipadam: " . basename(__FILE__) . "\n";
    exit;
}

// ── Laksana kemaskini ────────────────────────────────────────────────
$update = $pdo->prepare("UPDATE tempahan SET kategori = ? WHERE id = ?");
$kira   = [];

echo sprintf("%-6s %-40s %-14s %s\n", "ID", "Nama Mesyuarat", "Lama", "Baharu");
echo str_repeat('-', 80) . "\n";

foreach ($peta as $p) {
    $update->execute([$p['baharu'], $p['id']]);
    $kira[$p['lama']][$p['baharu']] = ($kira[$p['lama']][$p['baharu']] ?? 0) + 1;
    echo sprintf("%-6s %-40s %-14s %s\n",
        $p['id'],
        mb_strimwidth($p['nama'], 0, 38, '..'),
        $p['lama'],
        $p['baharu']
    );
}

echo "\n--- Ringkasan pemetaan ---\n";
foreach ($kira as $lama => $baharu) {
    foreach ($baharu as $kat => $bil) {
        echo "  '$lama' → '$kat' : $bil rekod\n";
    }
}

echo "\n=== SELESAI ===\n";
@unlink(__FILE__);
echo "Dipadam: " . basename(__FILE__) . "\n";