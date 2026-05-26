<?php
define('KEY', 'ibook2026deploy');
if (($_GET['key'] ?? '') !== KEY) { http_response_code(403); die('Akses ditolak.'); }
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><style>body{font-family:monospace;background:#1a1a2e;color:#e5e7eb;padding:20px}table{border-collapse:collapse;width:100%;font-size:12px}th,td{border:1px solid #334155;padding:6px 10px;text-align:left}th{background:#0f172a;color:#60a5fa}tr:nth-child(even){background:#1e293b}.ok{color:#34d399}.fail{color:#f87171}.warn{color:#fbbf24}</style></head><body>';

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

// 1. Semak lajur tempahan_berulang_id
$cols = $pdo->query("SHOW COLUMNS FROM `tempahan` LIKE 'tempahan_berulang_id'")->fetchAll(PDO::FETCH_ASSOC);
echo '<h3 class="ok">1. Lajur tempahan_berulang_id dalam jadual tempahan:</h3>';
if (empty($cols)) {
    echo '<p class="fail">TIDAK WUJUD!</p>';
} else {
    echo '<pre class="ok">'.print_r($cols[0], true).'</pre>';
}

// 2. 10 tempahan terbaru dengan tempahan_berulang_id
echo '<h3 class="ok">2. 10 tempahan terbaru (id, nama, tempahan_berulang_id):</h3>';
$rows = $pdo->query("SELECT id, ulid, nama_mesyuarat, tempahan_berulang_id, created_at FROM tempahan ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
echo '<table><tr><th>id</th><th>nama_mesyuarat</th><th>tempahan_berulang_id</th><th>created_at</th></tr>';
foreach ($rows as $r) {
    $berulangVal = $r['tempahan_berulang_id'] ?? 'NULL';
    $class = $berulangVal !== 'NULL' && $berulangVal !== null ? 'ok' : 'warn';
    echo '<tr><td>'.$r['id'].'</td><td>'.htmlspecialchars(substr($r['nama_mesyuarat'],0,40)).'</td><td class="'.$class.'">'.$berulangVal.'</td><td>'.$r['created_at'].'</td></tr>';
}
echo '</table>';

// 3. Semak jadual tempahan_berulang
echo '<h3 class="ok">3. Rekod dalam jadual tempahan_berulang:</h3>';
try {
    $rows2 = $pdo->query("SELECT id, ulid, jenis, nama_mesyuarat, created_at FROM tempahan_berulang ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows2)) {
        echo '<p class="warn">Tiada rekod dalam tempahan_berulang.</p>';
    } else {
        echo '<table><tr><th>id</th><th>ulid</th><th>jenis</th><th>nama_mesyuarat</th><th>created_at</th></tr>';
        foreach ($rows2 as $r) {
            echo '<tr><td>'.$r['id'].'</td><td>'.substr($r['ulid'],0,15).'...</td><td>'.$r['jenis'].'</td><td>'.htmlspecialchars(substr($r['nama_mesyuarat'],0,40)).'</td><td>'.$r['created_at'].'</td></tr>';
        }
        echo '</table>';
    }
} catch (Exception $e) { echo '<p class="fail">'.$e->getMessage().'</p>'; }

// 4. Semak fillable Tempahan model
echo '<h3 class="ok">4. Kandungan Tempahan.php (baris fillable):</h3>';
$model = __DIR__.'/app/Models/Tempahan.php';
if (file_exists($model)) {
    $lines = file($model);
    $inFillable = false;
    echo '<pre>';
    foreach ($lines as $n => $line) {
        if (str_contains($line, 'fillable')) $inFillable = true;
        if ($inFillable) {
            echo htmlspecialchars($line);
            if (str_contains($line, '];')) break;
        }
    }
    echo '</pre>';
}

echo '</body></html>';