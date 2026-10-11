<?php

namespace App\Support;

use App\Enums\StatusKehadiran;

class Ui
{
    /**
     * Mengembalikan kelas Tailwind LENGKAP untuk badge status kehadiran (tinted, berikon + teks).
     * Semua kelas ditulis secara literal agar dikenali compiler Tailwind.
     */
    public static function statusBadge(StatusKehadiran|string|null $status): string
    {
        $val = self::normalizeStatus($status);

        return match ($val) {
            'hadir', 'sudah_diabsen' => 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 border border-emerald-400 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/50',
            'izin' => 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-900 border border-blue-400 dark:bg-blue-500/15 dark:text-blue-300 dark:border-blue-500/50',
            'sakit' => 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-400 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/50',
            'alpa' => 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-900 border border-red-400 dark:bg-red-500/15 dark:text-red-300 dark:border-red-500/50',
            default => 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-800 border-2 border-dashed border-slate-400 dark:bg-slate-700/50 dark:text-slate-200 dark:border-slate-400',
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
            return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 border border-emerald-400 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/50';
        }

        if ($persen >= $batasKuning) {
            return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-400 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/50';
        }

        return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-900 border border-red-400 dark:bg-red-500/15 dark:text-red-300 dark:border-red-500/50';
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
            default => 'inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-800 border border-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
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
            default => 'inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
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
            'primary' => $base.' bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950 focus-visible:ring-emerald-500',
            'secondary' => $base.' bg-white dark:bg-slate-800 border-2 border-slate-400 dark:border-slate-500 text-slate-900 dark:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-700 focus-visible:ring-emerald-500',
            'danger' => $base.' bg-red-700 hover:bg-red-800 active:bg-red-900 text-white dark:bg-red-400 dark:hover:bg-red-300 dark:text-slate-950 focus-visible:ring-red-500',
            default => $base.' bg-emerald-700 hover:bg-emerald-800 text-white dark:bg-emerald-400 dark:text-slate-950 focus-visible:ring-emerald-500',
        };
    }

    /**
     * Kelas dasar kartu konten (border-2, terang border-slate-300, gelap dark:border-slate-600).
     */
    public static function kartu(): string
    {
        return 'bg-white dark:bg-slate-900 border-2 border-slate-300 dark:border-slate-600 rounded-xl shadow-xs';
    }

    /**
     * Kelas kartu elevated (panel, item jadwal, kartu di atas latar belakang utama).
     */
    public static function kartuElevated(): string
    {
        return 'bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-xl shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5';
    }

    /**
     * Kelas header kelompok hari atau tanggal.
     */
    public static function headerGrup(): string
    {
        return 'bg-slate-100 text-slate-900 border-2 border-slate-300 dark:bg-slate-800 dark:text-white dark:border-slate-600 font-bold rounded-xl';
    }

    /**
     * Border aksen sisi kiri (border-l-8) untuk kartu statistik (stat-card).
     */
    public static function statBorder(string $warna = 'blue'): string
    {
        return match (strtolower($warna)) {
            'hadir', 'emerald', 'hijau', 'success' => 'border-l-8 border-l-emerald-600 dark:border-l-emerald-400',
            'izin', 'blue', 'biru', 'info' => 'border-l-8 border-l-blue-600 dark:border-l-blue-400',
            'sakit', 'amber', 'kuning', 'warning' => 'border-l-8 border-l-amber-500 dark:border-l-amber-400',
            'alpa', 'red', 'merah', 'danger' => 'border-l-8 border-l-red-600 dark:border-l-red-400',
            'indigo', 'purple', 'ungu' => 'border-l-8 border-l-indigo-600 dark:border-l-indigo-400',
            'teal' => 'border-l-8 border-l-teal-600 dark:border-l-teal-400',
            default => 'border-l-8 border-l-slate-400 dark:border-l-slate-500',
        };
    }

    /**
     * Kelas pembungkus kartu strip rekap H/I/S/A (4 sel tinted dengan border-l-8).
     */
    public static function rekapCard(string $status): string
    {
        $val = self::normalizeStatus($status);

        return match ($val) {
            'hadir' => 'bg-emerald-100 dark:bg-emerald-500/15 border-2 border-slate-300 dark:border-slate-600 border-l-8 border-l-emerald-600 dark:border-l-emerald-400 rounded-xl p-4 shadow-xs',
            'izin' => 'bg-blue-100 dark:bg-blue-500/15 border-2 border-slate-300 dark:border-slate-600 border-l-8 border-l-blue-600 dark:border-l-blue-400 rounded-xl p-4 shadow-xs',
            'sakit' => 'bg-amber-100 dark:bg-amber-500/15 border-2 border-slate-300 dark:border-slate-600 border-l-8 border-l-amber-500 dark:border-l-amber-400 rounded-xl p-4 shadow-xs',
            'alpa' => 'bg-red-100 dark:bg-red-500/15 border-2 border-slate-300 dark:border-slate-600 border-l-8 border-l-red-600 dark:border-l-red-400 rounded-xl p-4 shadow-xs',
            default => 'bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 border-l-8 border-l-slate-400 dark:border-l-slate-500 rounded-xl p-4 shadow-xs',
        };
    }

    /**
     * Kelas angka nilai strip rekap H/I/S/A (-900 terang / -300 gelap).
     */
    public static function rekapAngka(string $status): string
    {
        $val = self::normalizeStatus($status);

        return match ($val) {
            'hadir' => 'text-emerald-900 dark:text-emerald-300 font-extrabold text-2xl sm:text-3xl tabular-nums block',
            'izin' => 'text-blue-900 dark:text-blue-300 font-extrabold text-2xl sm:text-3xl tabular-nums block',
            'sakit' => 'text-amber-900 dark:text-amber-300 font-extrabold text-2xl sm:text-3xl tabular-nums block',
            'alpa' => 'text-red-900 dark:text-red-300 font-extrabold text-2xl sm:text-3xl tabular-nums block',
            default => 'text-slate-900 dark:text-white font-extrabold text-2xl sm:text-3xl tabular-nums block',
        };
    }

    /**
     * Kelas label strip rekap H/I/S/A.
     */
    public static function rekapLabel(): string
    {
        return 'text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider block';
    }

    /**
     * Kelas bingkai dan pembatas baris tabel.
     */
    public static function table(): string
    {
        return 'w-full border-2 border-slate-300 dark:border-slate-600 divide-y-2 divide-slate-200 dark:divide-slate-700';
    }

    /**
     * Kelas header tabel.
     */
    public static function tableHeader(): string
    {
        return 'border-b-2 border-slate-400 dark:border-slate-500 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white font-bold';
    }

    /**
     * Kelas token literal untuk kotak/chip pertemuan absensi (tinted per status dan berpasangan dark:).
     */
    public static function pertemuanChip(StatusKehadiran|string|null $status): string
    {
        $val = self::normalizeStatus($status);

        return match ($val) {
            'hadir' => 'bg-emerald-100 text-emerald-900 border-2 border-emerald-400 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/50',
            'izin' => 'bg-blue-100 text-blue-900 border-2 border-blue-400 dark:bg-blue-500/15 dark:text-blue-300 dark:border-blue-500/50',
            'sakit' => 'bg-amber-100 text-amber-900 border-2 border-amber-400 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/50',
            'alpa' => 'bg-red-100 text-red-900 border-2 border-red-400 dark:bg-red-500/15 dark:text-red-300 dark:border-red-500/50',
            default => 'bg-slate-100 text-slate-800 border-2 border-dashed border-slate-400 dark:bg-slate-700/50 dark:text-slate-200 dark:border-slate-400',
        };
    }

    /**
     * Mengembalikan huruf inisial status kehadiran (H, I, S, A, atau -).
     */
    public static function statusHuruf(StatusKehadiran|string|null $status): string
    {
        $val = self::normalizeStatus($status);

        return match ($val) {
            'hadir' => 'H',
            'izin' => 'I',
            'sakit' => 'S',
            'alpa' => 'A',
            default => '-',
        };
    }

    /**
     * Kelas Tailwind literal untuk ukuran ikon eksplisit (sm=w-4 h-4, md=w-5 h-5, lg=w-6 h-6).
     */
    public static function iconClass(string $size = 'md'): string
    {
        return match (strtolower(trim($size))) {
            'sm' => 'w-4 h-4 shrink-0',
            'lg' => 'w-6 h-6 shrink-0',
            default => 'w-5 h-5 shrink-0',
        };
    }

    /**
     * Mengembalikan nama bulan Indonesia untuk angka 1..12.
     */
    public static function namaBulanIndo(int $bulan): string
    {
        $nama = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $nama[$bulan] ?? '';
    }

    /**
     * Membentuk daftar 6 bulan pada periode terpilih (Ganjil: Juli-Desember tahun pertama,
     * Genap: Januari-Juni tahun kedua) secara murni tanpa query database.
     * Jika ada bulan terpilih valid di luar 6 bulan itu, tambahkan agar opsi tidak hilang.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function getDaftarBulanPeriode(?string $tahunAjaran, ?string $semester, ?string $selected = null): array
    {
        $ta = trim((string) $tahunAjaran);
        $sm = strtolower(trim((string) $semester));

        // Default tahun ajaran jika kosong/tidak valid
        if (preg_match('/^(\d{4})\/(\d{4})$/', $ta, $m)) {
            $thn1 = (int) $m[1];
            $thn2 = (int) $m[2];
        } else {
            $thnNow = (int) date('Y');
            $thn1 = $thnNow;
            $thn2 = $thnNow + 1;
        }

        $daftar = [];

        if ($sm === 'genap') {
            // Genap = Januari s.d. Juni tahun kedua
            for ($b = 1; $b <= 6; $b++) {
                $val = sprintf('%04d-%02d', $thn2, $b);
                $daftar[] = [
                    'value' => $val,
                    'label' => self::namaBulanIndo($b).' '.$thn2,
                ];
            }
        } else {
            // Ganjil = Juli s.d. Desember tahun pertama
            for ($b = 7; $b <= 12; $b++) {
                $val = sprintf('%04d-%02d', $thn1, $b);
                $daftar[] = [
                    'value' => $val,
                    'label' => self::namaBulanIndo($b).' '.$thn1,
                ];
            }
        }

        // Jika request membawa bulan valid di luar enam bulan itu, tambahkan sebagai opsi terpilih
        if (! empty($selected) && preg_match('/^(\d{4})-(\d{2})$/', $selected, $selMatches)) {
            $selThn = (int) $selMatches[1];
            $selBln = (int) $selMatches[2];

            if ($selBln >= 1 && $selBln <= 12) {
                $existingValues = array_column($daftar, 'value');
                if (! in_array($selected, $existingValues, true)) {
                    $daftar[] = [
                        'value' => $selected,
                        'label' => self::namaBulanIndo($selBln).' '.$selThn,
                    ];
                }
            }
        }

        return $daftar;
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
