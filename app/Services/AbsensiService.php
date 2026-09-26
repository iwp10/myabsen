<?php

namespace App\Services;

use App\Enums\StatusKehadiran;
use App\Models\DetailAbsensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\SesiAbsensi;
use Carbon\Carbon;
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

        return Jadwal::with(['kelas', 'mapel'])
            ->where('guru_id', $guru->id)
            ->where('hari', $hari)
            ->orderBy('jam_mulai')
            ->get()
            ->map(function ($jadwal) use ($tanggal) {
                $jadwal->sesi_hari_ini = SesiAbsensi::with('detailAbsensi')
                    ->where('jadwal_id', $jadwal->id)
                    ->where('tanggal', $tanggal->toDateString())
                    ->first();

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
            $now = now();

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
    private function getHariIndonesia(int $dayOfWeek): ?string
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
}
