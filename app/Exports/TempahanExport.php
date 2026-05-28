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

use App\Models\Tempahan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TempahanExport implements FromCollection, WithColumnWidths, WithHeadings, WithMapping, WithStyles, WithTitle
{
    /** Instance-level counter — selamat untuk panggilan berulang */
    private int $bil = 0;

    public function __construct(private array $filters = []) {}

    public function collection(): Collection
    {
        $user = Auth::user();
        $query = Tempahan::query()->with(['bilik:id,nama,lokasi', 'pengguna:id,name,jabatan']);

        // Hak akses — staf hanya rekod unit sendiri (sama seperti unitQuery() controller)
        if ($user->isStaf()) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if ($user->jabatan) {
                    $q->orWhereHas('pengguna', fn ($q2) => $q2->where('jabatan', $user->jabatan));
                }
            });
        }

        // ── Filter ──
        if (! empty($this->filters['bilik_id'])) {
            $query->where('bilik_id', $this->filters['bilik_id']);
        }
        if (! empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (! empty($this->filters['kategori'])) {
            $query->where('kategori', $this->filters['kategori']);
        }
        if (! empty($this->filters['tarikh_dari'])) {
            $query->whereDate('tarikh', '>=', $this->filters['tarikh_dari']);
        }
        if (! empty($this->filters['tarikh_hingga'])) {
            $query->whereDate('tarikh', '<=', $this->filters['tarikh_hingga']);
        }
        if (! empty($this->filters['carian'])) {
            $query->where('nama_mesyuarat', 'like', '%'.$this->filters['carian'].'%');
        }

        return $query->orderByDesc('tarikh')->get();
    }

    public function headings(): array
    {
        return [
            '#',
            'Nama Mesyuarat',
            'Tarikh',
            'Sesi',
            'Masa',
            'Bilik',
            'Kategori',
            'Pengerusi',
            'Bil. Peserta',
            'Pemohon',
            'Unit',
            'Status',
        ];
    }

    public function map($t): array
    {
        $this->bil++;

        $statusLabel = match ($t->status) {
            'diluluskan' => 'Diluluskan',
            'ditolak' => 'Ditolak',
            default => 'Menunggu',
        };

        return [
            $this->bil,
            $t->nama_mesyuarat,
            $t->tarikh->format('d/m/Y'),
            $t->sesi === 'pagi' ? 'Pagi' : 'Petang',
            $t->masa_mula.' - '.$t->masa_tamat,
            $t->bilik->nama ?? '-',
            $t->kategori,
            $t->nama_pengerusi,
            $t->bilangan_peserta,
            $t->pengguna->name ?? '-',
            $t->pengguna->jabatan ?? '-',
            $statusLabel,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Baris pengepala: latar gelap, teks putih tebal
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1A1A2E'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Senarai Tempahan';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 36,
            'C' => 12,
            'D' => 8,
            'E' => 16,
            'F' => 22,
            'G' => 22,
            'H' => 28,
            'I' => 10,
            'J' => 28,
            'K' => 32,
            'L' => 14,
        ];
    }
}
