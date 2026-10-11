<?php

namespace App\Support;

use App\Enums\StatusKehadiran;

class Ui
{
    /**
     * Mengembalikan kelas Tailwind LENGKAP untuk badge status kehadiran (solid, berikon + teks).
     * Semua kelas ditulis secara literal agar dikenali compiler Tailwind.
     */
    public static function statusBadge(StatusKehadiran|string|null $status): string
    {
        $val = self::normalizeStatus($status);

        return match ($val) {
            'hadir' => 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-700 text-white dark:bg-emerald-500 dark:text-gray-900',
            'izin' => 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-600 text-white dark:bg-blue-400 dark:text-gray-900',
            'sakit' => 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-400 text-gray-900 dark:bg-amber-400 dark:text-gray-900',
            'alpa' => 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-red-600 text-white dark:bg-red-400 dark:text-gray-900',
            'sudah_diabsen' => 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-700 text-white dark:bg-emerald-500 dark:text-gray-900',
            default => 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-800 border-2 border-dashed border-gray-400 dark:bg-gray-700 dark:text-gray-100 dark:border-gray-500',
        };
    }

    /**
     * Label baku dalam Bahasa Indonesia untuk setiap status kehadiran.
     */
    public static function statusLabel(StatusKehadiran|string|null $status): string
    {
        $val = self::normalizeStatus($status);

        return match ($val) {
            'hadir' => 'Hadir',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alpa' => 'Alpa',
            'sudah_diabsen' => 'Sudah diabsen',
            default => 'Belum diabsen',
        };
    }

    /**
     * Nama identifier ikon Tabler yang bersesuaian dengan status.
     */
    public static function statusIcon(StatusKehadiran|string|null $status): string
    {
        $val = self::normalizeStatus($status);

        return match ($val) {
            'hadir', 'sudah_diabsen' => 'check',
            'izin' => 'info',
            'sakit' => 'plus',
            'alpa' => 'x',
            default => 'clock',
        };
    }

    /**
     * Mengembalikan kelas warna teks persentase kehadiran berdasarkan batas kehadiran rendah.
     * Menggunakan config('absensi.batas_kehadiran_rendah', 75).
     */
    public static function persenWarna(float $persen): string
    {
        $batasRendah = (float) config('absensi.batas_kehadiran_rendah', 75);
        $batasKuning = max(0.0, $batasRendah - 5.0);

        if ($persen >= $batasRendah) {
            return 'text-emerald-700 dark:text-emerald-400';
        }

        if ($persen >= $batasKuning) {
            return 'text-amber-600 dark:text-amber-400';
        }

        return 'text-red-600 dark:text-red-400';
    }

    /**
     * Kelas background badge persentase kehadiran (pill/badge).
     */
    public static function persenBadge(float $persen): string
    {
        $batasRendah = (float) config('absensi.batas_kehadiran_rendah', 75);
        $batasKuning = max(0.0, $batasRendah - 5.0);

        if ($persen >= $batasRendah) {
            return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700';
        }

        if ($persen >= $batasKuning) {
            return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-300 dark:border-amber-700';
        }

        return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 border border-red-300 dark:border-red-700';
    }

    /**
     * Mengembalikan kelas badge hari (Senin s.d. Sabtu) dengan 6 variasi warna kontras berbeda.
     */
    public static function badgeHari(string $hari): string
    {
        $h = strtolower(trim($hari));

        return match ($h) {
            'senin' => 'inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-sky-100 text-sky-800 border border-sky-300 dark:bg-sky-900/40 dark:text-sky-300 dark:border-sky-800',
            'selasa' => 'inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-800',
            'rabu' => 'inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-purple-100 text-purple-800 border border-purple-300 dark:bg-purple-900/40 dark:text-purple-300 dark:border-purple-800',
            'kamis' => 'inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-800',
            'jumat' => 'inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-teal-100 text-teal-800 border border-teal-300 dark:bg-teal-900/40 dark:text-teal-300 dark:border-teal-800',
            'sabtu' => 'inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300 dark:bg-rose-900/40 dark:text-rose-300 dark:border-rose-800',
            default => 'inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-gray-100 text-gray-800 border border-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
        };
    }

    /**
     * Mengembalikan kelas badge semester (Ganjil / Genap).
     */
    public static function badgeSemester(string $semester): string
    {
        $s = strtolower(trim($semester));

        return match ($s) {
            'ganjil' => 'inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-800',
            'genap' => 'inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200 dark:bg-purple-900/40 dark:text-purple-300 dark:border-purple-800',
            default => 'inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-gray-100 text-gray-800 border border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
        };
    }

    /**
     * Kelas Tailwind untuk tombol (varian: primary, secondary, danger).
     * Memastikan target ketuk minimal 44x44px di mobile dan fokus ring jelas.
     */
    public static function tombol(string $varian = 'primary'): string
    {
        $base = 'inline-flex items-center justify-center min-h-[44px] min-w-[44px] px-4 py-2.5 text-base sm:text-sm font-semibold rounded-xl shadow-xs transition duration-150 ease-in-out focus-visible:outline-none focus-visible:ring-4 disabled:opacity-50 disabled:cursor-not-allowed';

        return match ($varian) {
            'primary' => $base.' bg-blue-600 hover:bg-blue-700 active:bg-blue-800 dark:bg-blue-600 dark:hover:bg-blue-500 text-white focus-visible:ring-blue-500/50',
            'secondary' => $base.' bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 focus-visible:ring-blue-500/50',
            'danger' => $base.' bg-red-600 hover:bg-red-700 active:bg-red-800 dark:bg-red-600 dark:hover:bg-red-500 text-white focus-visible:ring-red-500/50',
            'emerald' => $base.' bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white focus-visible:ring-emerald-500/50',
            default => $base.' bg-gray-800 hover:bg-gray-900 text-white dark:bg-gray-200 dark:text-gray-800 focus-visible:ring-gray-500/50',
        };
    }

    /**
     * Kelas dasar kartu konten (border-2, terang border-gray-300, gelap dark:border-gray-600).
     */
    public static function kartu(): string
    {
        return 'bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-xl shadow-xs';
    }

    /**
     * Border aksen sisi kiri (border-l-8) untuk kartu statistik (stat-card).
     */
    public static function statBorder(string $warna = 'blue'): string
    {
        return match (strtolower($warna)) {
            'hadir', 'emerald', 'hijau', 'success' => 'border-l-8 border-l-emerald-600 dark:border-l-emerald-500',
            'izin', 'blue', 'biru', 'info' => 'border-l-8 border-l-blue-600 dark:border-l-blue-500',
            'sakit', 'amber', 'kuning', 'warning' => 'border-l-8 border-l-amber-400 dark:border-l-amber-400',
            'alpa', 'red', 'merah', 'danger' => 'border-l-8 border-l-red-600 dark:border-l-red-500',
            'indigo', 'purple', 'ungu' => 'border-l-8 border-l-indigo-600 dark:border-l-indigo-500',
            'teal' => 'border-l-8 border-l-teal-600 dark:border-l-teal-500',
            default => 'border-l-8 border-l-gray-400 dark:border-l-gray-500',
        };
    }

    /**
     * Kelas bingkai dan pembatas baris tabel.
     */
    public static function table(): string
    {
        return 'w-full border-2 border-gray-300 dark:border-gray-600 divide-y-2 divide-gray-200 dark:divide-gray-700';
    }

    /**
     * Kelas header tabel.
     */
    public static function tableHeader(): string
    {
        return 'border-b-2 border-gray-400 dark:border-gray-500 bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100 font-bold';
    }

    /**
     * Normalisasi string atau enum StatusKehadiran.
     */
    private static function normalizeStatus(StatusKehadiran|string|null $status): string
    {
        if ($status instanceof StatusKehadiran) {
            return $status->value;
        }

        $s = strtolower(trim((string) $status));

        return match ($s) {
            'hadir', 'h' => 'hadir',
            'izin', 'i' => 'izin',
            'sakit', 's' => 'sakit',
            'alpa', 'a' => 'alpa',
            'sudah diabsen', 'sudah_diabsen', 'sudah' => 'sudah_diabsen',
            default => 'belum_diabsen',
        };
    }
}
