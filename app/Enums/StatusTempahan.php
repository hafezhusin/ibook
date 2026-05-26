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


namespace App\Enums;

enum StatusTempahan: string
{
    case Diluluskan = 'diluluskan';
    case Ditolak    = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::Diluluskan => 'Sah',
            self::Ditolak    => 'Ditolak',
        };
    }

    public function warnaBadge(): string
    {
        return match ($this) {
            self::Diluluskan => 'bg-green-100 text-green-700',
            self::Ditolak    => 'bg-red-100 text-red-700',
        };
    }

    public function ikonBadge(): string
    {
        return match ($this) {
            self::Diluluskan => 'fa-circle-check',
            self::Ditolak    => 'fa-ban',
        };
    }

    /** Semak sama ada nilai string sah */
    public static function isValid(string $nilai): bool
    {
        return in_array($nilai, array_column(self::cases(), 'value'), true);
    }
}
