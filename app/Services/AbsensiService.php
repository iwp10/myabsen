<?php

namespace App\Services;

use App\Enums\StatusKehadiran;
use App\Models\DetailAbsensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AbsensiService
{
    /**
     * Mendapatkan daftar jadwal untuk guru pada hari tertentu.
     */
    public function getJadwalHariIni(int $userId, Carbon $tanggal)
    {
        $guru = Guru::where('user_id', $userId)->first();
        if (! $guru) {
            return collect();
        }

        $hari = $this->getHariIndonesia($tanggal->dayOfWeek);
        if (! $hari) {
            return collect(); // Minggu atau tidak valid
        }

        return Jadwal::with([
            'kelas',
            'mapel',
            'sesiAbsensi' => function ($q) use ($tanggal) {
                $q->where('tanggal', $tanggal->toDateString())->with('detailAbsensi');
            },
        ])
            ->where('guru_id', $guru->id)
            ->where('hari', $hari)
            ->orderBy('jam_mulai')
            ->get()
            ->map(function ($jadwal) {
                $jadwal->sesi_hari_ini = $jadwal->sesiAbsensi->first();

                return $jadwal;
            });
    }

    /**
     * Menyimpan data absensi.
     */
    public function simpanAbsensi(Jadwal $jadwal, string $tanggal, array $dataDetail, ?string $catatan, int $userId)
    {
        return DB::transaction(function () use ($jadwal, $tanggal, $dataDetail, $catatan, $userId) {
            $sesi = SesiAbsensi::lockForUpdate()->firstOrCreate(
                ['jadwal_id' => $jadwal->id, 'tanggal' => $tanggal],
                ['diabsen_oleh' => $userId]
            );

            // Jika sesi sudah ada (bukan baru dibuat), update catatan dan diubah_oleh
            if (! $sesi->wasRecentlyCreated) {
                $sesi->catatan = $catatan;
                $sesi->diubah_oleh = $userId;
                $sesi->save();
            } else {
                // Update catatan untuk sesi baru
                $sesi->catatan = $catatan;
                $sesi->save();
            }

            // Ambil semua siswa di kelas tersebut
            $siswaList = $jadwal->kelas->siswa()->get();

            $detailRecords = [];
            $now = Carbon::now('Asia/Jakarta');

            foreach ($siswaList as $siswa) {
                $status = $dataDetail[$siswa->id]['status'] ?? StatusKehadiran::HADIR->value;
                $keterangan = $dataDetail[$siswa->id]['keterangan'] ?? null;

                $detailRecords[] = [
                    'sesi_absensi_id' => $sesi->id,
                    'siswa_id' => $siswa->id,
                    'status' => $status,
                    'keterangan' => $keterangan,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Lakukan upsert detail absensi
            DetailAbsensi::upsert(
                $detailRecords,
                ['sesi_absensi_id', 'siswa_id'], // unique columns
                ['status', 'keterangan', 'updated_at'] // columns to update
            );

            return $sesi;
        });
    }

    /**
     * Menyiapkan data form absensi untuk jadwal dan tanggal tertentu.
     */
    public function getFormAbsensiData(Jadwal $jadwal, Carbon $tanggal): array
    {
        $jadwal->load([
            'kelas.siswa' => function ($query) {
                $query->join('users', 'siswa.user_id', '=', 'users.id')
                    ->select('siswa.*')
                    ->orderBy('users.name');
            },
            'kelas.siswa.user',
            'mapel',
        ]);

        $sesi = SesiAbsensi::with('detailAbsensi')
            ->where('jadwal_id', $jadwal->id)
            ->where('tanggal', $tanggal->toDateString())
            ->first();

        $detailExisting = [];
        if ($sesi) {
            foreach ($sesi->detailAbsensi as $detail) {
                $detailExisting[$detail->siswa_id] = [
                    'status' => $detail->status->value,
                    'keterangan' => $detail->keterangan,
                ];
            }
        }

        return [
            'sesi' => $sesi,
            'detailExisting' => $detailExisting,
        ];
    }

    /**
     * Mendapatkan statistik ringkas mengajar untuk guru.
     */
    public function getStatistikGuru(int $guruId): array
    {
        return [
            'total_kelas' => Jadwal::where('guru_id', $guruId)->distinct()->count('kelas_id'),
            'total_mapel' => Jadwal::where('guru_id', $guruId)->distinct()->count('mapel_id'),
            'total_jadwal' => Jadwal::where('guru_id', $guruId)->count(),
        ];
    }

    /**
     * Mendapatkan daftar status kehadiran siswa hari ini per jadwal pelajaran.
     */
    public function getStatusHariIniSiswa(Siswa $siswa, Carbon $tanggal): Collection
    {
        $hari = self::getHariServer($tanggal);
        if (! $hari) {
            return collect();
        }

        $tanggalStr = $tanggal->toDateString();

        $jadwals = Jadwal::with([
            'mapel',
            'guru.user',
            'sesiAbsensi' => function ($q) use ($tanggalStr, $siswa) {
                $q->where('tanggal', $tanggalStr)
                    ->with(['detailAbsensi' => function ($dq) use ($siswa) {
                        $dq->where('siswa_id', $siswa->id);
                    }]);
            },
        ])
            ->where('kelas_id', $siswa->kelas_id)
            ->where('hari', $hari)
            ->orderBy('jam_mulai')
            ->get();

        return $jadwals->map(function ($jadwal) {
            $sesi = $jadwal->sesiAbsensi->first();
            $detail = $sesi?->detailAbsensi?->first();
            $status = $detail ? $detail->status : null;

            return [
                'jadwal' => $jadwal,
                'status_label' => $status ? $status->label() : 'Belum diabsen',
                'status_value' => $status ? $status->value : null,
            ];
        });
    }

    /**
     * Mendapatkan rekap persentase kehadiran siswa per mata pelajaran.
     */
    public function getRekapPerMapelSiswa(int $siswaId): Collection
    {
        return DB::table('detail_absensi')
            ->join('sesi_absensi', 'detail_absensi.sesi_absensi_id', '=', 'sesi_absensi.id')
            ->join('jadwal', 'sesi_absensi.jadwal_id', '=', 'jadwal.id')
            ->join('mapel', 'jadwal.mapel_id', '=', 'mapel.id')
            ->where('detail_absensi.siswa_id', $siswaId)
            ->select(
                'mapel.id',
                'mapel.nama as mapel',
                DB::raw('COUNT(detail_absensi.id) as total_sesi'),
                DB::raw("SUM(CASE WHEN detail_absensi.status = 'hadir' THEN 1 ELSE 0 END) as total_hadir"),
                DB::raw("SUM(CASE WHEN detail_absensi.status = 'izin' THEN 1 ELSE 0 END) as total_izin"),
                DB::raw("SUM(CASE WHEN detail_absensi.status = 'sakit' THEN 1 ELSE 0 END) as total_sakit"),
                DB::raw("SUM(CASE WHEN detail_absensi.status = 'alpa' THEN 1 ELSE 0 END) as total_alpa")
            )
            ->groupBy('mapel.id', 'mapel.nama')
            ->get()
            ->map(function ($item) {
                $item->persentase = $this->hitungPersentaseKehadiran(
                    (int) $item->total_hadir,
                    (int) $item->total_izin,
                    (int) $item->total_sakit,
                    (int) $item->total_sesi
                );

                return $item;
            });
    }

    /**
     * Mendapatkan riwayat sesi untuk guru.
     */
    public function getRiwayatSesi(int $userId)
    {
        $guru = Guru::where('user_id', $userId)->first();
        if (! $guru) {
            return collect();
        }

        return SesiAbsensi::with(['jadwal.kelas', 'jadwal.mapel', 'detailAbsensi'])
            ->whereHas('jadwal', function ($query) use ($guru) {
                $query->where('guru_id', $guru->id);
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy(Jadwal::select('jam_mulai')
                ->whereColumn('jadwal.id', 'sesi_absensi.jadwal_id')
                ->limit(1), 'desc')
            ->get();
    }

    /**
     * Konversi dayOfWeek dari Carbon (0 = Minggu, 1 = Senin) ke enum hari.
     */
    public static function getHariServer(?Carbon $date = null): ?string
    {
        $date = $date ?? Carbon::now('Asia/Jakarta');

        $hari = [
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            6 => 'sabtu',
        ];

        return $hari[$date->dayOfWeek] ?? null;
    }

    public function getHariIndonesia(int $dayOfWeek): ?string
    {
        $hari = [
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            6 => 'sabtu',
        ];

        return $hari[$dayOfWeek] ?? null;
    }

    /**
     * Memeriksa apakah tanggal berada di masa depan (Asia/Jakarta).
     */
    public function isTanggalMasaDepan(Carbon|string $tanggal): bool
    {
        $date = $tanggal instanceof Carbon
            ? $tanggal->copy()->setTimezone('Asia/Jakarta')->startOfDay()
            : Carbon::parse($tanggal, 'Asia/Jakarta')->startOfDay();

        $today = Carbon::now('Asia/Jakarta')->startOfDay();

        return $date->greaterThan($today);
    }

    /**
     * Memeriksa apakah tanggal berada dalam batas koreksi (default 7 hari terakhir: hari ini dan 6 hari sebelumnya).
     */
    public function isTanggalDalamBatasKoreksi(Carbon|string $tanggal): bool
    {
        $date = $tanggal instanceof Carbon
            ? $tanggal->copy()->setTimezone('Asia/Jakarta')->startOfDay()
            : Carbon::parse($tanggal, 'Asia/Jakarta')->startOfDay();

        $today = Carbon::now('Asia/Jakarta')->startOfDay();

        if ($date->greaterThan($today)) {
            return false;
        }

        $batasHari = (int) config('absensi.batas_koreksi_hari', 7);
        $batasTanggal = $today->copy()->subDays($batasHari - 1);

        return $date->greaterThanOrEqualTo($batasTanggal);
    }

    /**
     * Memeriksa apakah hari pada tanggal cocok dengan hari jadwal.
     */
    public function isHariCocokDenganJadwal(Jadwal $jadwal, Carbon|string $tanggal): bool
    {
        $date = $tanggal instanceof Carbon
            ? $tanggal->copy()->setTimezone('Asia/Jakarta')
            : Carbon::parse($tanggal, 'Asia/Jakarta');

        $hariTanggal = self::getHariServer($date);

        return $jadwal->hari === $hariTanggal;
    }

    /**
     * Memeriksa apakah tanggal berada dalam batas koreksi untuk role tertentu.
     * Admin: boleh tanggal lampau kapan saja (tidak boleh masa depan).
     * Guru: harus dalam batas 7 hari terakhir (hari ini dan 6 hari sebelumnya, tidak boleh masa depan).
     */
    public function isTanggalDalamBatasKoreksiRole(User|string $userOrRole, Carbon|string $tanggal): bool
    {
        $role = $userOrRole instanceof User ? $userOrRole->role : $userOrRole;

        if ($this->isTanggalMasaDepan($tanggal)) {
            return false;
        }

        if ($role === 'admin') {
            return true;
        }

        if ($role === 'guru') {
            return $this->isTanggalDalamBatasKoreksi($tanggal);
        }

        return false;
    }

    /**
     * Mendapatkan tanggal terakhir dalam jendela 7 hari yang cocok dengan hari sebuah jadwal.
     */
    public function getTanggalTerakhirJadwal(Jadwal $jadwal, ?Carbon $referensi = null): ?string
    {
        $today = $referensi
            ? ($referensi instanceof Carbon ? $referensi->copy()->setTimezone('Asia/Jakarta')->startOfDay() : Carbon::parse($referensi, 'Asia/Jakarta')->startOfDay())
            : Carbon::now('Asia/Jakarta')->startOfDay();

        $batasHari = (int) config('absensi.batas_koreksi_hari', 7);

        for ($i = 0; $i < $batasHari; $i++) {
            $checkDate = $today->copy()->subDays($i);
            if ($this->isHariCocokDenganJadwal($jadwal, $checkDate)) {
                return $checkDate->toDateString();
            }
        }

        return null;
    }

    /**
     * Mendapatkan daftar tanggal yang valid untuk koreksi jadwal (dalam batas koreksi dan cocok harinya).
     *
     * @return array<string>
     */
    public function getTanggalBolehDikoreksi(Jadwal $jadwal): array
    {
        $tanggal = $this->getTanggalTerakhirJadwal($jadwal);

        return $tanggal ? [$tanggal] : [];
    }

    /**
     * Mendapatkan jadwal mingguan guru beserta tanggal target dalam jendela 7 hari dan status absensinya.
     */
    public function getJadwalMingguanGuru(int $userId): Collection
    {
        $guru = Guru::where('user_id', $userId)->first();
        if (! $guru) {
            return collect();
        }

        $jadwals = Jadwal::with(['kelas.jurusan', 'mapel'])
            ->where('guru_id', $guru->id)
            ->orderByRaw("CASE hari 
                WHEN 'senin' THEN 1 
                WHEN 'selasa' THEN 2 
                WHEN 'rabu' THEN 3 
                WHEN 'kamis' THEN 4 
                WHEN 'jumat' THEN 5 
                WHEN 'sabtu' THEN 6 
                ELSE 7 END")
            ->orderBy('jam_mulai', 'asc')
            ->get();

        if ($jadwals->isEmpty()) {
            return collect();
        }

        $today = Carbon::now('Asia/Jakarta')->startOfDay();
        $todayStr = $today->toDateString();

        // Cari tanggal target untuk setiap jadwal
        $targetDates = [];
        foreach ($jadwals as $jadwal) {
            $tgl = $this->getTanggalTerakhirJadwal($jadwal, $today);
            $jadwal->target_tanggal = $tgl;
            if ($tgl) {
                $targetDates[$tgl] = true;
            }
        }

        // Eager load SesiAbsensi untuk pasangan jadwal dan tanggal target
        $sesiList = SesiAbsensi::whereIn('jadwal_id', $jadwals->pluck('id'))
            ->whereIn('tanggal', array_keys($targetDates))
            ->get()
            ->keyBy(fn ($item) => $item->jadwal_id.'_'.$item->tanggal);

        foreach ($jadwals as $jadwal) {
            $tgl = $jadwal->target_tanggal;
            $sesi = $tgl ? ($sesiList[$jadwal->id.'_'.$tgl] ?? null) : null;
            $jadwal->sesi_terakhir = $sesi;

            $isHariIni = ($tgl === $todayStr);
            $jadwal->is_hari_ini = $isHariIni;

            if ($sesi) {
                $jadwal->status_absensi = 'Sudah diabsen';
            } elseif ($isHariIni) {
                $jadwal->status_absensi = 'Hari ini';
            } else {
                $jadwal->status_absensi = 'Belum diabsen';
            }
        }

        return $jadwals;
    }

    /**
     * Mendapatkan daftar jadwal untuk fitur Koreksi Absensi Admin pada tanggal dan kelas tertentu.
     */
    public function getJadwalKoreksiAdmin(string $tanggal, ?int $kelasId = null): Collection
    {
        $tanggalObj = Carbon::parse($tanggal, 'Asia/Jakarta');
        $hari = self::getHariServer($tanggalObj);
        if (! $hari) {
            return collect();
        }

        $query = Jadwal::with([
            'kelas.jurusan',
            'mapel',
            'guru.user',
            'sesiAbsensi' => function ($q) use ($tanggal) {
                $q->where('tanggal', $tanggal)->with('detailAbsensi');
            },
        ])
            ->where('hari', $hari)
            ->orderBy('jam_mulai');

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        return $query->get()->map(function ($jadwal) use ($tanggal) {
            $sesi = $jadwal->sesiAbsensi->first();
            $jadwal->sesi_koreksi = $sesi;
            $jadwal->status_absensi = $sesi ? 'Sudah diabsen' : 'Belum diabsen';
            $jadwal->tanggal_koreksi = $tanggal;

            return $jadwal;
        });
    }

    /**
     * Mendapatkan rekap laporan absensi berdasarkan filter.
     */
    public function getRekapLaporan(array $filters)
    {
        $query = DetailAbsensi::query()
            ->join('sesi_absensi', 'detail_absensi.sesi_absensi_id', '=', 'sesi_absensi.id')
            ->join('jadwal', 'sesi_absensi.jadwal_id', '=', 'jadwal.id')
            ->join('siswa', 'detail_absensi.siswa_id', '=', 'siswa.id')
            ->join('users', 'siswa.user_id', '=', 'users.id')
            ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->join('mapel', 'jadwal.mapel_id', '=', 'mapel.id');

        if (! empty($filters['kelas_id'])) {
            $query->where('jadwal.kelas_id', $filters['kelas_id']);
        }
        if (! empty($filters['mapel_id'])) {
            $query->where('jadwal.mapel_id', $filters['mapel_id']);
        }
        if (! empty($filters['bulan'])) {
            // bulan is YYYY-MM
            $parts = explode('-', $filters['bulan']);
            if (count($parts) === 2) {
                $query->whereYear('sesi_absensi.tanggal', $parts[0])
                    ->whereMonth('sesi_absensi.tanggal', $parts[1]);
            }
        }
        if (! empty($filters['guru_id'])) {
            $query->where('jadwal.guru_id', $filters['guru_id']);
        }

        $query->select(
            'siswa.id as siswa_id',
            'siswa.nis',
            'users.name as nama_siswa',
            'kelas.nama as nama_kelas',
            'mapel.nama as nama_mapel',
            DB::raw('SUM(CASE WHEN detail_absensi.status = "hadir" THEN 1 ELSE 0 END) as hadir'),
            DB::raw('SUM(CASE WHEN detail_absensi.status = "izin" THEN 1 ELSE 0 END) as izin'),
            DB::raw('SUM(CASE WHEN detail_absensi.status = "sakit" THEN 1 ELSE 0 END) as sakit'),
            DB::raw('SUM(CASE WHEN detail_absensi.status = "alpa" THEN 1 ELSE 0 END) as alpa'),
            DB::raw('COUNT(detail_absensi.id) as total_sesi')
        )
            ->groupBy('siswa.id', 'siswa.nis', 'users.name', 'kelas.nama', 'mapel.nama')
            ->orderBy('kelas.nama')
            ->orderBy('mapel.nama')
            ->orderBy('users.name');

        return $query->get();
    }

    /**
     * Hitung persentase kehadiran siswa.
     * Rumus: ((Hadir + Izin + Sakit) / Total Pertemuan) * 100
     * Kategori Alpa adalah satu-satunya yang mengurangi persentase.
     */
    public function hitungPersentaseKehadiran(int $hadir, int $izin, int $sakit, int $totalSesi): float
    {
        if ($totalSesi <= 0) {
            return 0.0;
        }

        $positif = $hadir + $izin + $sakit;

        return round(($positif / $totalSesi) * 100, 2);
    }

    /**
     * Mendapatkan rincian breakdown data kehadiran siswa secara keseluruhan.
     */
    public function getRingkasanKehadiranSiswa(int $siswaId): array
    {
        $counts = DetailAbsensi::where('siswa_id', $siswaId)
            ->select(
                DB::raw("SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as total_hadir"),
                DB::raw("SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as total_izin"),
                DB::raw("SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as total_sakit"),
                DB::raw("SUM(CASE WHEN status = 'alpa' THEN 1 ELSE 0 END) as total_alpa"),
                DB::raw('COUNT(id) as total_sesi')
            )
            ->first();

        $totalHadir = (int) ($counts->total_hadir ?? 0);
        $totalIzin = (int) ($counts->total_izin ?? 0);
        $totalSakit = (int) ($counts->total_sakit ?? 0);
        $totalAlpa = (int) ($counts->total_alpa ?? 0);
        $totalSesi = (int) ($counts->total_sesi ?? 0);

        $persentase = $this->hitungPersentaseKehadiran($totalHadir, $totalIzin, $totalSakit, $totalSesi);

        return [
            'total_hadir' => $totalHadir,
            'total_izin' => $totalIzin,
            'total_sakit' => $totalSakit,
            'total_alpa' => $totalAlpa,
            'total_sesi' => $totalSesi,
            'persentase' => $persentase,
        ];
    }
}
