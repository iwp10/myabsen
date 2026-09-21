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
}
