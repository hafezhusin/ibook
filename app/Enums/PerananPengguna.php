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

enum PerananPengguna: string
{
    case PentadbirSistem = 'pentadbir_sistem';
    case UrusSetia = 'urus_setia';
    case Staf = 'staf';

    public function label(): string
    {
        return match ($this) {
            self::PentadbirSistem => 'Pentadbir Sistem',
            self::UrusSetia => 'Urus Setia',
            self::Staf => 'Staf',
        };
    }

    public function warnaBadge(): string
    {
        return match ($this) {
            self::PentadbirSistem => 'bg-red-100 text-red-700',
            self::UrusSetia => 'bg-amber-100 text-amber-700',
            self::Staf => 'bg-blue-100 text-blue-700',
        };
    }

    /** Boleh luluskan / urus semua tempahan */
    public function bolehUrusSemua(): bool
    {
        return in_array($this, [self::PentadbirSistem, self::UrusSetia], true);
    }

    /** Boleh akses tetapan sistem */
    public function bolehAksesTetapan(): bool
    {
        return $this === self::PentadbirSistem;
    }

    public static function isValid(string $nilai): bool
    {
        return in_array($nilai, array_column(self::cases(), 'value'), true);
    }

    /** Untuk rule validasi: 'in:pentadbir_sistem,urus_setia,staf' */
    public static function validasiIn(): string
    {
        return implode(',', array_column(self::cases(), 'value'));
    }
}
