<?php
/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 * Hak cipta terpelihara. Dilarang meniru, menyalin, mengubah suai, atau
 * mengedar perisian ini tanpa kebenaran bertulis daripada pemilik hak cipta.
 *
 * Pembangun : Mohd Hafez bin Husin (Unit Aplikasi Gunasama)
 *
 * Unauthorized copying, modification, distribution, or use of this software,
 * via any medium, is strictly prohibited. Proprietary and confidential.
 */


namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// ─── Main export ───────────────────────────────────────────────────────────────

class LaporanExport implements WithMultipleSheets
{
    public function __construct(private array $data, private int $tahun) {}

    public function sheets(): array
    {
        return [
            new LaporanRingkasanSheet($this->data, $this->tahun),
            new LaporanBulanSheet($this->data, $this->tahun),
            new LaporanKategoriSheet($this->data, $this->tahun),
            new LaporanUnitSheet($this->data, $this->tahun),
            new LaporanBilikSheet($this->data, $this->tahun),
            new LaporanTop10Sheet($this->data, $this->tahun),
        ];
    }
}

// ─── Shared header style ───────────────────────────────────────────────────────

trait LaporanHeaderStyle
{
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A1A2E']],
            ],
        ];
    }
}

// ─── Sheet 1: Ringkasan ───────────────────────────────────────────────────────

class LaporanRingkasanSheet implements FromArray, WithTitle, WithColumnWidths, WithStyles
{
    public function __construct(private array $data, private int $tahun) {}

    public function array(): array
    {
        $up = $this->data['unitPalingAktif'];
        $bp = $this->data['bilikPalingGuna'];

        return [
            ['LAPORAN STATISTIK TEMPAHAN BILIK MESYUARAT', ''],
            ['iBook 2.0 — BPTM, Akaun Negara Malaysia', ''],
            ['', ''],
            ['Tahun Laporan', $this->tahun],
            ['Tarikh Jana', now()->format('d/m/Y H:i')],
            ['', ''],
            ['RINGKASAN KPI', ''],
            ['Jumlah Tempahan Diluluskan', $this->data['totalDiluluskan']],
            // @phpstan-ignore-next-line nullsafe.neverNull — $up boleh null jika tiada tempahan
            ['Unit Paling Aktif', $up?->unit ?? '—'],
            // @phpstan-ignore-next-line nullsafe.neverNull
            ['Tempahan Unit Paling Aktif', $up?->jumlah ?? '—'],
            ['Bilik Paling Digunakan', $bp ? $bp['nama'] : '—'],
            ['Tempahan Bilik Paling Digunakan', $bp ? $bp['jumlah_tempahan'] : '—'],
            ['Purata Kadar Penggunaan Bilik', ($this->data['purataPenggunaan'] ?? 0) . '%'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            7 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A1A2E']]],
        ];
    }

    public function title(): string { return 'Ringkasan'; }

    public function columnWidths(): array { return ['A' => 42, 'B' => 32]; }
}

// ─── Sheet 2: Mengikut Bulan ──────────────────────────────────────────────────

class LaporanBulanSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    use LaporanHeaderStyle;

    private static array $NAMA_BULAN = [
        'Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun',
        'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember',
    ];

    public function __construct(private array $data, private int $tahun) {}

    public function headings(): array { return ['Bulan', 'Pagi', 'Petang', 'Jumlah']; }

    public function array(): array
    {
        $pagi   = $this->data['dataBulanSesi']['pagi'];
        $petang = $this->data['dataBulanSesi']['petang'];
        $rows   = [];

        foreach (self::$NAMA_BULAN as $i => $nama) {
            $p  = $pagi[$i]   ?? 0;
            $pt = $petang[$i] ?? 0;
            $rows[] = [$nama, $p, $pt, $p + $pt];
        }

        $rows[] = ['JUMLAH', array_sum($pagi), array_sum($petang), array_sum($pagi) + array_sum($petang)];

        return $rows;
    }

    public function title(): string { return "Mengikut Bulan {$this->tahun}"; }

    public function columnWidths(): array { return ['A' => 16, 'B' => 12, 'C' => 12, 'D' => 12]; }
}

// ─── Sheet 3: Mengikut Kategori ───────────────────────────────────────────────

class LaporanKategoriSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    use LaporanHeaderStyle;

    public function __construct(private array $data, private int $tahun) {}

    public function headings(): array { return ['#', 'Kategori', 'Bilangan Tempahan']; }

    public function array(): array
    {
        return $this->data['mengikutKategori']
            ->values()
            ->map(fn ($k, $i) => [$i + 1, $k->kategori, $k->jumlah])
            ->all();
    }

    public function title(): string { return "Mengikut Kategori {$this->tahun}"; }

    public function columnWidths(): array { return ['A' => 6, 'B' => 36, 'C' => 20]; }
}

// ─── Sheet 4: Mengikut Unit ───────────────────────────────────────────────────

class LaporanUnitSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    use LaporanHeaderStyle;

    public function __construct(private array $data, private int $tahun) {}

    public function headings(): array { return ['#', 'Unit / Seksyen', 'Bilangan Tempahan (Diluluskan)']; }

    public function array(): array
    {
        return $this->data['mengikutUnit']
            ->values()
            ->map(fn ($u, $i) => [$i + 1, $u->unit, $u->jumlah])
            ->all();
    }

    public function title(): string { return "Mengikut Unit {$this->tahun}"; }

    public function columnWidths(): array { return ['A' => 6, 'B' => 52, 'C' => 30]; }
}

// ─── Sheet 5: Penggunaan Bilik ────────────────────────────────────────────────

class LaporanBilikSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    use LaporanHeaderStyle;

    public function __construct(private array $data, private int $tahun) {}

    public function headings(): array
    {
        return ['Bilik Mesyuarat', 'Kapasiti', 'Tempahan Diluluskan', 'Kadar Penggunaan (%)'];
    }

    public function array(): array
    {
        return $this->data['bilik']
            ->values()
            ->map(fn ($b) => [
                $b['nama'],
                $b['kapasiti'],
                $b['jumlah_tempahan'],
                $b['peratusan'] . '%',
            ])
            ->all();
    }

    public function title(): string { return "Penggunaan Bilik {$this->tahun}"; }

    public function columnWidths(): array { return ['A' => 34, 'B' => 12, 'C' => 22, 'D' => 22]; }
}

// ─── Sheet 6: Top 10 Pemohon ──────────────────────────────────────────────────

class LaporanTop10Sheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    use LaporanHeaderStyle;

    public function __construct(private array $data, private int $tahun) {}

    public function headings(): array
    {
        return ['#', 'Nama Pemohon', 'Unit', 'Jumlah Permohonan', 'Diluluskan', 'Ditolak'];
    }

    public function array(): array
    {
        return $this->data['top10Pengguna']
            ->values()
            ->map(fn ($p, $i) => [
                $i + 1,
                $p->name,
                $p->jabatan,
                $p->jumlah,
                $p->jumlah_diluluskan,
                $p->jumlah_ditolak,
            ])
            ->all();
    }

    public function title(): string { return "Top 10 Pemohon {$this->tahun}"; }

    public function columnWidths(): array { return ['A' => 6, 'B' => 30, 'C' => 52, 'D' => 18, 'E' => 14, 'F' => 10]; }
}
