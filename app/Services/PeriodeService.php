<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Pengaturan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class PeriodeService
{
    /**
     * Mendapatkan saran periode aktif berdasarkan tanggal kalender (Asia/Jakarta).
     * Aturan:
     * - Juli - Desember: Semester Ganjil, Tahun Ajaran tahun / (tahun + 1)
     * - Januari - Juni  : Semester Genap, Tahun Ajaran (tahun - 1) / tahun
     */
    public function getSaranPeriode(?Carbon $tanggal = null): array
    {
        $date = $tanggal ? $tanggal->copy()->setTimezone('Asia/Jakarta') : Carbon::now('Asia/Jakarta');
        $year = $date->year;
        $month = $date->month;

        if ($month >= 7) {
            $semester = 'Ganjil';
            $tahunAjaran = $year.'/'.($year + 1);
        } else {
            $semester = 'Genap';
            $tahunAjaran = ($year - 1).'/'.$year;
        }

        return [
            'tahun_ajaran' => $tahunAjaran,
            'semester' => $semester,
        ];
    }

    /**
     * Mendapatkan periode akademik aktif (tahun ajaran dan semester).
     * Mengambil dari tabel pengaturan; jika belum ada atau kosong, fallback ke saran sistem.
     * Tidak akan error meski tabel pengaturan belum ada atau kosong.
     */
    public function getActivePeriode(): array
    {
        $tahunAjaran = null;
        $semester = null;

        try {
            if (Schema::hasTable('pengaturan')) {
                $tahunAjaran = Pengaturan::where('kunci', 'tahun_ajaran_aktif')->value('nilai');
                $semester = Pengaturan::where('kunci', 'semester_aktif')->value('nilai');
            }
        } catch (\Throwable $e) {
            // Fallback aman jika database belum siap
        }

        $saran = $this->getSaranPeriode();

        return [
            'tahun_ajaran' => ! empty($tahunAjaran) ? $tahunAjaran : $saran['tahun_ajaran'],
            'semester' => ! empty($semester) ? $semester : $saran['semester'],
        ];
    }

    /**
     * Menyimpan periode akademik aktif ke basis data.
     */
    public function setPeriodeAktif(string $tahunAjaran, string $semester): void
    {
        Pengaturan::updateOrCreate(
            ['kunci' => 'tahun_ajaran_aktif'],
            ['nilai' => $tahunAjaran]
        );

        Pengaturan::updateOrCreate(
            ['kunci' => 'semester_aktif'],
            ['nilai' => $semester]
        );
    }

    /**
     * Mengecek apakah periode aktif yang saat ini tersimpan berbeda dengan saran sistem.
     */
    public function isPeriodeBerbedaDenganSaran(): bool
    {
        $aktif = $this->getActivePeriode();
        $saran = $this->getSaranPeriode();

        return $aktif['tahun_ajaran'] !== $saran['tahun_ajaran'] || $aktif['semester'] !== $saran['semester'];
    }

    /**
     * Mendapatkan daftar pilihan tahun ajaran untuk form (tahun sebelumnya, berjalan, berikutnya, serta yang ada di DB).
     */
    public function getDaftarPilihanTahunAjaran(?Carbon $tanggal = null): array
    {
        $date = $tanggal ? $tanggal->copy()->setTimezone('Asia/Jakarta') : Carbon::now('Asia/Jakarta');
        $currentYear = $date->year;
        $baseYear = $date->month >= 7 ? $currentYear : $currentYear - 1;

        $options = [
            ($baseYear - 1).'/'.$baseYear,
            $baseYear.'/'.($baseYear + 1),
            ($baseYear + 1).'/'.($baseYear + 2),
            ($currentYear - 1).'/'.$currentYear,
            $currentYear.'/'.($currentYear + 1),
            ($currentYear + 1).'/'.($currentYear + 2),
        ];

        try {
            $aktif = $this->getActivePeriode();
            if (! empty($aktif['tahun_ajaran'])) {
                $options[] = $aktif['tahun_ajaran'];
            }

            if (Schema::hasTable('jadwal')) {
                $dbJadwal = Jadwal::distinct()->pluck('tahun_ajaran')->filter()->all();
                $options = array_merge($options, $dbJadwal);
            }
            if (Schema::hasTable('kelas')) {
                $dbKelas = Kelas::distinct()->pluck('tahun_ajaran')->filter()->all();
                $options = array_merge($options, $dbKelas);
            }
        } catch (\Throwable $e) {
            // Ignore error
        }

        $options = array_unique($options);
        sort($options);

        return array_values($options);
    }

    /**
     * Mendapatkan daftar opsi kombinasi periode (tahun ajaran dan semester)
     * yang ada di data Kelas dan Jadwal, ditambah periode aktif, diurutkan terbaru.
     *
     * @return array<int, array{tahun_ajaran: string, semester: string, value: string, label: string, is_aktif: bool}>
     */
    public function getDaftarKombinasiPeriode(): array
    {
        $active = $this->getActivePeriode();
        $pairs = [];

        if (! empty($active['tahun_ajaran']) && ! empty($active['semester'])) {
            $pairs[$active['tahun_ajaran'].'|'.$active['semester']] = [
                'tahun_ajaran' => $active['tahun_ajaran'],
                'semester' => $active['semester'],
            ];
        }

        try {
            if (Schema::hasTable('kelas')) {
                $kelasPairs = Kelas::select('tahun_ajaran', 'semester')
                    ->whereNotNull('tahun_ajaran')
                    ->whereNotNull('semester')
                    ->distinct()
                    ->get();
                foreach ($kelasPairs as $k) {
                    $pairs[$k->tahun_ajaran.'|'.$k->semester] = [
                        'tahun_ajaran' => $k->tahun_ajaran,
                        'semester' => $k->semester,
                    ];
                }
            }

            if (Schema::hasTable('jadwal')) {
                $jadwalPairs = Jadwal::select('tahun_ajaran', 'semester')
                    ->whereNotNull('tahun_ajaran')
                    ->whereNotNull('semester')
                    ->distinct()
                    ->get();
                foreach ($jadwalPairs as $j) {
                    $pairs[$j->tahun_ajaran.'|'.$j->semester] = [
                        'tahun_ajaran' => $j->tahun_ajaran,
                        'semester' => $j->semester,
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Ignore error
        }

        // Urutkan terbaru: tahun_ajaran desc, lalu semester (Genap sebelum Ganjil dalam tahun yang sama)
        uasort($pairs, function ($a, $b) {
            if ($a['tahun_ajaran'] === $b['tahun_ajaran']) {
                return $b['semester'] <=> $a['semester'];
            }

            return strcmp($b['tahun_ajaran'], $a['tahun_ajaran']);
        });

        $result = [];
        foreach ($pairs as $p) {
            $isAktif = ($p['tahun_ajaran'] === ($active['tahun_ajaran'] ?? '') && $p['semester'] === ($active['semester'] ?? ''));
            $val = $p['tahun_ajaran'].'|'.$p['semester'];
            $label = $p['tahun_ajaran'].' - '.$p['semester'].($isAktif ? ' (aktif)' : '');
            $result[] = [
                'tahun_ajaran' => $p['tahun_ajaran'],
                'semester' => $p['semester'],
                'value' => $val,
                'label' => $label,
                'is_aktif' => $isAktif,
            ];
        }

        return $result;
    }
}
