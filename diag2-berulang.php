<?php
define('KEY', 'ibook2026deploy');
if (($_GET['key'] ?? '') !== KEY) { http_response_code(403); die('Akses ditolak.'); }

$ef = null;
foreach ([__DIR__.'/.env', __DIR__.'/../.env'] as $l) { if (file_exists($l)) { $ef = $l; break; } }
$dh=$dn=$du=$dp='';
foreach(file($ef) as $b) { $b=trim($b);
    if(str_starts_with($b,'DB_HOST='))     $dh=trim(substr($b,8)," \t\"'");
    if(str_starts_with($b,'DB_DATABASE=')) $dn=trim(substr($b,12)," \t\"'");
    if(str_starts_with($b,'DB_USERNAME=')) $du=trim(substr($b,12)," \t\"'");
    if(str_starts_with($b,'DB_PASSWORD=')) $dp=trim(substr($b,12)," \t\"'");
}
$pdo = new PDO("mysql:host={$dh};dbname={$dn};charset=utf8mb4",$du,$dp,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><style>
body{font-family:monospace;background:#1a1a2e;color:#e5e7eb;padding:20px}
table{border-collapse:collapse;width:100%;font-size:13px}
th,td{border:1px solid #334155;padding:8px 12px}th{background:#0f172a;color:#60a5fa}
.ok{color:#34d399}.fail{color:#f87171}.warn{color:#fbbf24}
</style></head><body><h2 style="color:#f59e0b">Semak Tempahan Berulang dalam DB</h2>';

// Semua tempahan berulang dengan ulid mereka
$rows = $pdo->query("
    SELECT t.id, t.ulid, t.nama_mesyuarat, t.tempahan_berulang_id, t.tarikh, t.status,
           tb.ulid as kumpulan_ulid,
           (SELECT COUNT(*) FROM tempahan WHERE tempahan_berulang_id = t.tempahan_berulang_id AND status != 'ditolak') as jumlah_dalam_kumpulan
    FROM tempahan t
    LEFT JOIN tempahan_berulang tb ON tb.id = t.tempahan_berulang_id
    WHERE t.tempahan_berulang_id IS NOT NULL
    ORDER BY t.tempahan_berulang_id, t.tarikh
")->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    echo '<p class="warn">Tiada tempahan dengan tempahan_berulang_id dalam DB.</p>';
} else {
    echo '<table><tr><th>id</th><th>ulid (14 aksara)</th><th>nama_mesyuarat</th><th>tarikh</th><th>tempahan_berulang_id</th><th>kumpulan_ulid</th><th>jumlah_dalam_kumpulan</th><th>data-padam-berulang (PHP)</th></tr>';
    foreach ($rows as $r) {
        $attr = $r['tempahan_berulang_id'] ? '1' : '0';
        $cls = $attr === '1' ? 'ok' : 'fail';
        echo '<tr>';
        echo '<td>'.$r['id'].'</td>';
        echo '<td>'.substr($r['ulid'],0,14).'...</td>';
        echo '<td>'.htmlspecialchars(substr($r['nama_mesyuarat'],0,35)).'</td>';
        echo '<td>'.$r['tarikh'].'</td>';
        echo '<td class="ok">'.$r['tempahan_berulang_id'].'</td>';
        echo '<td>'.substr($r['kumpulan_ulid']??'',0,14).'...</td>';
        echo '<td>'.$r['jumlah_dalam_kumpulan'].'</td>';
        echo '<td class="'.$cls.'"><b>'.$attr.'</b></td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '<p class="ok" style="margin-top:12px">Jumlah: '.count($rows).' tempahan berulang dalam DB.</p>';
    echo '<p style="color:#94a3b8;font-size:12px">Jika data-padam-berulang = 1, modal SEPATUTNYA tunjuk scope section.</p>';
}

echo '</body></html>';