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

use App\Models\ActivityLog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AuditLogExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    WithColumnWidths
{
    private int $bil = 0;

    public function __construct(private array $filters = []) {}

    public function collection(): Collection
    {
        $query = ActivityLog::with('pengguna:id,name')
            ->orderByDesc('dicipta_pada')
            ->orderByDesc('id');

        if (!empty($this->filters['tindakan'])) {
            $query->where('tindakan', $this->filters['tindakan']);
        }
        if (!empty($this->filters['pengguna_id'])) {
            $query->where('pengguna_id', $this->filters['pengguna_id']);
        }
        if (!empty($this->filters['tarikh_dari'])) {
            $query->whereDate('dicipta_pada', '>=', $this->filters['tarikh_dari']);
        }
        if (!empty($this->filters['tarikh_hingga'])) {
            $query->whereDate('dicipta_pada', '<=', $this->filters['tarikh_hingga']);
        }
        if (!empty($this->filters['carian'])) {
            $query->where(function ($q) {
                $q->where('penerangan', 'like', '%' . $this->filters['carian'] . '%')
                  ->orWhere('tindakan',  'like', '%' . $this->filters['carian'] . '%')
                  ->orWhere('ip_address','like', '%' . $this->filters['carian'] . '%');
            });
        }

        // Had 5,000 rekod — elak timeout pada InfinityFree shared hosting
        return $query->limit(5000)->get();
    }

    public function headings(): array
    {
        return [
            '#',
            'Tarikh & Masa',
            'Pengguna',
            'Tindakan',
            'Penerangan',
            'Model',
            'ID Model',
            'IP Address',
        ];
    }

    public function map($log): array
    {
        $this->bil++;

        return [
            $this->bil,
            $log->dicipta_pada?->format('d/m/Y H:i:s') ?? '-',
            // @phpstan-ignore-next-line nullsafe.neverNull — pengguna adalah null untuk tindakan sistem
            $log->pengguna?->name ?? 'Sistem',
            $log->tindakan,
            $log->penerangan,
            $log->model_jenis ?? '-',
            $log->model_id    ?? '-',
            $log->ip_address  ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1A1A2E'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Log Audit';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 20,
            'C' => 28,
            'D' => 28,
            'E' => 55,
            'F' => 18,
            'G' => 10,
            'H' => 16,
        ];
    }
}
