<?php

namespace App\Enums;

enum SesiTempahan: string
{
    case Pagi   = 'pagi';
    case Petang = 'petang';

    public function label(): string
    {
        return match ($this) {
            self::Pagi   => 'Sesi Pagi (9:00 AM – 1:00 PM)',
            self::Petang => 'Sesi Petang (2:00 PM – 6:00 PM)',
        };
    }

    public function masaMula(): string
    {
        return match ($this) {
            self::Pagi   => config('ibook.sesi.pagi.mula', '09:00'),
            self::Petang => config('ibook.sesi.petang.mula', '14:00'),
        };
    }

    public function masaTamat(): string
    {
        return match ($this) {
            self::Pagi   => config('ibook.sesi.pagi.tamat', '13:00'),
            self::Petang => config('ibook.sesi.petang.tamat', '18:00'),
        };
    }

    public static function isValid(string $nilai): bool
    {
        return in_array($nilai, array_column(self::cases(), 'value'), true);
    }

    public static function validasiIn(): string
    {
        return implode(',', array_column(self::cases(), 'value'));
    }
}
