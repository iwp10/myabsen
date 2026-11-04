<?php

namespace App\Support;

class Kembali
{
    /**
     * Daftar prefix URL relatif bawaan yang diizinkan untuk redirect.
     *
     * @var array<int, string>
     */
    protected const DEFAULT_ALLOWED_PREFIXES = [
        '/admin/jadwal',
        '/admin/kelas',
    ];

    /**
     * Memeriksa apakah URL tujuan aman dan valid.
     *
     * Kriteria aman:
     * - Berupa string non-kosong dengan panjang maksimal 2000 karakter.
     * - Tidak memuat karakter kontrol (ASCII 0-31 dan 127).
     * - Tidak memuat backslash (\).
     * - Tidak diawali dengan "//" (skema relatif open-redirect).
     * - Tidak memuat skema (http, https, javascript, dll) dan tidak memuat host.
     * - Merupakan jalur relatif yang diawali "/" dan jalurnya (path) diawali salah satu prefix yang diizinkan.
     *
     * @param  array<int, string>  $allowedPrefixes
     */
    public static function isValid(?string $target, array $allowedPrefixes = self::DEFAULT_ALLOWED_PREFIXES): bool
    {
        if (! is_string($target)) {
            return false;
        }

        $target = trim($target);

        if ($target === '' || strlen($target) > 2000) {
            return false;
        }

        // Tolak karakter kontrol ASCII
        if (preg_match('/[\x00-\x1F\x7F]/', $target)) {
            return false;
        }

        // Tolak backslash
        if (str_contains($target, '\\')) {
            return false;
        }

        // Tolak double slash awalan
        if (str_starts_with($target, '//')) {
            return false;
        }

        // Tolak bila memuat skema separator "://"
        if (str_contains($target, '://')) {
            return false;
        }

        $parsed = parse_url($target);
        if ($parsed === false) {
            return false;
        }

        // Tolak bila memiliki scheme atau host
        if (! empty($parsed['scheme']) || ! empty($parsed['host'])) {
            return false;
        }

        $path = $parsed['path'] ?? '';
        if ($path === '' || ! str_starts_with($path, '/')) {
            return false;
        }

        // Cocokkan path dengan daftar awalan yang diizinkan
        foreach ($allowedPrefixes as $allowed) {
            $normalizedAllowed = '/'.ltrim(rtrim($allowed, '/'), '/');

            // Cocok persis atau diawali prefix + '/'
            if ($path === $normalizedAllowed || str_starts_with($path, $normalizedAllowed.'/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mengembalikan URL tujuan aman bila valid, atau URL fallback bawaan bila tidak aman.
     *
     * @param  array<int, string>  $allowedPrefixes
     */
    public static function tujuan(?string $target, string $fallbackUrl, array $allowedPrefixes = self::DEFAULT_ALLOWED_PREFIXES): string
    {
        if (self::isValid($target, $allowedPrefixes)) {
            return trim((string) $target);
        }

        return $fallbackUrl;
    }
}
