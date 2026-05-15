<?php

namespace App\Exports;

use App\Models\Tempahan;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TempahanExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $user = Auth::user();
        $query = Tempahan::with(['bilik', 'pengguna']);

        if ($user->isStaf()) {
            $query->where('user_id', $user->id);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['bilik_id'])) {
            $query->where('bilik_id', $this->filters['bilik_id']);
        }

        return $query->orderByDesc('tarikh')->get();
    }

    public function headings(): array
    {
        return ['#', 'Nama Mesyuarat', 'Tarikh', 'Masa', 'Bilik', 'Pemohon', 'Peserta', 'Kategori', 'Status'];
    }

    public function map($row): array
    {
        static $i = 0;
        $i++;
        return [
            $i,
            $row->nama_mesyuarat,
            $row->tarikh->format('d/m/Y'),
            $row->masa_label,
            $row->bilik->nama ?? '-',
            $row->pengguna->name ?? '-',
            $row->bilangan_peserta,
            $row->kategori_label,
            ucfirst($row->status),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '1a1a2e']], 'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]],
        ];
    }
}
