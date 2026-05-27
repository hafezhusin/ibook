<?php
/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 *
 * BackupService — Jana SQL dump terus menggunakan PDO.
 * Tidak menggunakan exec() / shell_exec() — sesuai untuk shared hosting InfinityFree.
 */

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDO;

class BackupService
{
    private const TETAPAN_PATH = 'backup_tetapan.json';
    private const BACKUP_DIR   = 'backups';
    private const CHUNK_ROWS   = 300; // Baris per INSERT — elak overload memori

    // ── Jana SQL dump ────────────────────────────────────────────────

    public function janaDump(): string
    {
        $pdo = DB::connection()->getPdo();
        $pdo->exec('SET NAMES utf8mb4');

        $db = config('database.connections.mysql.database');

        $output  = "-- ============================================================\n";
        $output .= "-- iBook Database Backup\n";
        $output .= "-- Tarikh  : " . now()->format('d M Y, H:i:s') . "\n";
        $output .= "-- Database: {$db}\n";
        $output .= "-- Pelayan : " . (config('database.connections.mysql.host')) . "\n";
        $output .= "-- ============================================================\n\n";
        $output .= "SET NAMES utf8mb4;\n";
        $output .= "SET CHARACTER_SET_CLIENT=utf8mb4;\n";
        $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $output .= "-- ------------------------------------------------------------\n";
            $output .= "-- Jadual: `{$table}`\n";
            $output .= "-- ------------------------------------------------------------\n\n";

            // CREATE TABLE
            $createRow = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_NUM);
            $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $output .= $createRow[1] . ";\n\n";

            // Kiraan baris
            $count = (int) $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            if ($count === 0) continue;

            // Dapatkan nama lajur
            $stmtCols = $pdo->query("SELECT * FROM `{$table}` LIMIT 1");
            $colCount = $stmtCols->columnCount();
            $cols = [];
            for ($i = 0; $i < $colCount; $i++) {
                $cols[] = '`' . $stmtCols->getColumnMeta($i)['name'] . '`';
            }
            $colList = implode(', ', $cols);

            // Ambil baris dalam kelompok
            $offset = 0;
            while ($offset < $count) {
                $rows = $pdo->query(
                    "SELECT * FROM `{$table}` LIMIT " . self::CHUNK_ROWS . " OFFSET {$offset}"
                )->fetchAll(PDO::FETCH_NUM);

                if (empty($rows)) break;

                $output .= "INSERT INTO `{$table}` ({$colList}) VALUES\n";
                $values = [];
                foreach ($rows as $row) {
                    $escaped = array_map(
                        fn($v) => is_null($v) ? 'NULL' : $pdo->quote((string) $v),
                        $row
                    );
                    $values[] = '(' . implode(', ', $escaped) . ')';
                }
                $output .= implode(",\n", $values) . ";\n\n";
                $offset += self::CHUNK_ROWS;
            }
        }

        $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
        return $output;
    }

    // ── Simpan backup ke storage ──────────────────────────────────────

    public function simpan(string $jenis = 'segera'): array
    {
        $sql    = $this->janaDump();
        $nama   = 'ibook_backup_' . now()->format('Ymd_His') . '_' . $jenis . '.sql';
        $path   = self::BACKUP_DIR . '/' . $nama;

        Storage::disk('local')->put($path, $sql);

        // Kira SHA-256 checksum untuk integriti forensik
        $checksum = hash('sha256', $sql);

        return [
            'nama'     => $nama,
            'path'     => $path,
            'saiz'     => strlen($sql),
            'checksum' => $checksum,
        ];
    }

    // ── Tetapan Jadual ────────────────────────────────────────────────

    public function bacaTetapan(): array
    {
        if (Storage::disk('local')->exists(self::TETAPAN_PATH)) {
            $data = json_decode(Storage::disk('local')->get(self::TETAPAN_PATH), true);
            if (is_array($data)) return $data;
        }
        return $this->defaultTetapan();
    }

    public function simpanTetapan(array $tetapan): void
    {
        Storage::disk('local')->put(
            self::TETAPAN_PATH,
            json_encode($tetapan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    private function defaultTetapan(): array
    {
        return [
            'jadual'         => 'tiada',
            'next_backup_at' => null,
            'last_backup_at' => null,
        ];
    }

    // ── Semak jadual tertunggak ───────────────────────────────────────

    public function adaBackupTertunggak(): bool
    {
        $t = $this->bacaTetapan();
        if ($t['jadual'] === 'tiada' || empty($t['next_backup_at'])) return false;
        return now()->isAfter($t['next_backup_at']);
    }

    public function nextBackupCarbon(): ?\Carbon\Carbon
    {
        $t = $this->bacaTetapan();
        return empty($t['next_backup_at']) ? null : \Carbon\Carbon::parse($t['next_backup_at']);
    }

    // ── Kemaskini jadual ──────────────────────────────────────────────

    public function kemaskiniJadual(string $jadual): void
    {
        $t = $this->bacaTetapan();

        $nextRun = match ($jadual) {
            'mingguan' => now()->addWeek()->startOfDay(),
            'bulanan'  => now()->addMonth()->startOfDay(),
            default    => null,
        };

        $this->simpanTetapan([
            'jadual'         => $jadual,
            'next_backup_at' => $nextRun?->toIso8601String(),
            'last_backup_at' => $t['last_backup_at'],
        ]);
    }

    /** Panggil selepas backup berjaya — kemaskini last & next run */
    public function rekodSelesai(): void
    {
        $t      = $this->bacaTetapan();
        $jadual = $t['jadual'];

        $nextRun = match ($jadual) {
            'mingguan' => now()->addWeek()->startOfDay(),
            'bulanan'  => now()->addMonth()->startOfDay(),
            default    => null,
        };

        $this->simpanTetapan([
            'jadual'         => $jadual,
            'next_backup_at' => $nextRun?->toIso8601String(),
            'last_backup_at' => now()->toIso8601String(),
        ]);
    }

    // ── Senarai fail backup dalam storage ────────────────────────────

    public function failWujud(string $namaFail): bool
    {
        return Storage::disk('local')->exists(self::BACKUP_DIR . '/' . $namaFail);
    }

    public function pathFail(string $namaFail): string
    {
        return self::BACKUP_DIR . '/' . $namaFail;
    }

    public function padamFail(string $namaFail): void
    {
        Storage::disk('local')->delete(self::BACKUP_DIR . '/' . $namaFail);
    }
}
