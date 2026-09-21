<?php

namespace App\Enums;

enum StatusKehadiran: string
{
    case HADIR = 'hadir';
    case IZIN = 'izin';
    case SAKIT = 'sakit';
    case ALPA = 'alpa';

    public function label(): string
    {
        return match($this) {
            self::HADIR => 'Hadir',
            self::IZIN => 'Izin',
            self::SAKIT => 'Sakit',
            self::ALPA => 'Alpa',
        };
    }
}

