<?php

namespace App\Support;

class KelasMapel
{
    /**
     * Pola regex untuk format kelas_mapel: "{kelas_id}-{mapel_id}".
     * Hanya menerima angka positif tanpa awalan nol dipisah tanda minus.
     */
    public const REGEX_PATTERN = '/^[1-9]\d*-[1-9]\d*$/';

    /**
     * Memeriksa apakah string kelas_mapel memiliki format yang valid.
     */
    public static function isValid(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return preg_match(self::REGEX_PATTERN, $value) === 1;
    }

    /**
     * Memecah string "{kelas_id}-{mapel_id}" menjadi array kelas_id dan mapel_id.
     * Mengembalikan null jika format tidak valid.
     *
     * @return array{kelas_id: int, mapel_id: int, 0: int, 1: int}|null
     */
    public static function parse(?string $value): ?array
    {
        if (! self::isValid($value)) {
            return null;
        }

        $parts = explode('-', $value);

        $kelasId = (int) $parts[0];
        $mapelId = (int) $parts[1];

        return [
            'kelas_id' => $kelasId,
            'mapel_id' => $mapelId,
            0 => $kelasId,
            1 => $mapelId,
        ];
    }

    /**
     * Membentuk string "{kelas_id}-{mapel_id}" dari kelas_id dan mapel_id.
     */
    public static function make(int|string $kelasId, int|string $mapelId): string
    {
        return "{$kelasId}-{$mapelId}";
    }
}
