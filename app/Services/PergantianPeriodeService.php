<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PergantianPeriodeService
{
    public const MAX_KELAS_SALIN = 100;

    public const MAX_SISWA_PINDAH = 500;

    public const MAX_SISWA_LULUS = 500;

    /**
     * Mendapatkan daftar kombinasi periode yang benar-benar ada di data Kelas aktif.
     *
     * @return array<int, array{tahun_ajaran: string, semester: string, value: string, label: string}>
     */
    public function getDaftarPeriodeAsalKelas(): array
    {
        $pairs = Kelas::whereNull('deleted_at')
            ->whereNotNull('tahun_ajaran')
            ->whereNotNull('semester')
            ->select('tahun_ajaran', 'semester')
            ->distinct()
            ->get();

        $sorted = $pairs->sort(function ($a, $b) {
            if ($a->tahun_ajaran === $b->tahun_ajaran) {
                return $b->semester <=> $a->semester;
            }

            return strcmp($b->tahun_ajaran, $a->tahun_ajaran);
        })->values();

        return $sorted->map(function ($item) {
            return [
                'tahun_ajaran' => $item->tahun_ajaran,
                'semester' => $item->semester,
                'value' => $item->tahun_ajaran.'-'.$item->semester,
                'label' => $item->tahun_ajaran.' - Semester '.$item->semester,
            ];
        })->all();
    }

    /**
     * Mengambil daftar kelas aktif pada periode tertentu.
     *
     * @return Collection<int, Kelas>
     */
    public function getKelasByPeriode(string $tahunAjaran, string $semester): Collection
    {
        return Kelas::with('jurusan')
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->orderBy('tingkat', 'asc')
            ->orderBy('nama', 'asc')
            ->get();
    }

    /**
     * Mengambil seluruh kelas aktif untuk dropdown seleksi.
     *
     * @return Collection<int, Kelas>
     */
    public function getDaftarKelasAktif(): Collection
    {
        return Kelas::with('jurusan')
            ->orderBy('tahun_ajaran', 'desc')
            ->orderBy('semester', 'desc')
            ->orderBy('tingkat', 'asc')
            ->orderBy('nama', 'asc')
            ->get();
    }

    /**
     * Mengambil daftar siswa aktif pada kelas tertentu.
     *
     * @return Collection<int, Siswa>
     */
    public function getSiswaAktifByKelas(int $kelasId): Collection
    {
        return Siswa::with('user')
            ->where('kelas_id', $kelasId)
            ->join('users', 'siswa.user_id', '=', 'users.id')
            ->select('siswa.*')
            ->orderBy('users.name', 'asc')
            ->get();
    }

    /**
     * Menyalin kelas-kelas terpilih dari periode asal ke periode tujuan.
     * Kelas yang sudah ada di periode tujuan dilewati.
     * Jadwal dan siswa TIDAK disalin.
     *
     * @param  array<int>  $kelasIds
     * @return array{disalin: int, dilewati: int}
     */
    public function salinKelas(
        string $tahunAjaranAsal,
        string $semesterAsal,
        string $tahunAjaranTujuan,
        string $semesterTujuan,
        array $kelasIds
    ): array {
        if ($tahunAjaranAsal === $tahunAjaranTujuan && $semesterAsal === $semesterTujuan) {
            throw new InvalidArgumentException('Periode tujuan tidak boleh sama dengan periode asal.');
        }

        if (count($kelasIds) > self::MAX_KELAS_SALIN) {
            throw new InvalidArgumentException('Jumlah kelas yang disalin melebihi batas maksimal ('.self::MAX_KELAS_SALIN.' kelas).');
        }

        return DB::transaction(function () use ($tahunAjaranAsal, $semesterAsal, $tahunAjaranTujuan, $semesterTujuan, $kelasIds) {
            // Ambil hanya kelas aktif dari periode asal
            $kelasAsalList = Kelas::whereIn('id', $kelasIds)
                ->where('tahun_ajaran', $tahunAjaranAsal)
                ->where('semester', $semesterAsal)
                ->get();

            if ($kelasAsalList->count() !== count($kelasIds)) {
                throw new InvalidArgumentException('Terdapat kelas yang tidak valid atau bukan berasal dari periode asal.');
            }

            $disalin = 0;
            $dilewati = 0;

            foreach ($kelasAsalList as $kelas) {
                // Periksa apakah kelas sudah ada di periode tujuan (nama + jurusan + periode sama)
                $sudahAda = Kelas::where('nama', $kelas->nama)
                    ->where('jurusan_id', $kelas->jurusan_id)
                    ->where('tahun_ajaran', $tahunAjaranTujuan)
                    ->where('semester', $semesterTujuan)
                    ->exists();

                if ($sudahAda) {
                    $dilewati++;

                    continue;
                }

                Kelas::create([
                    'nama' => $kelas->nama,
                    'tingkat' => $kelas->tingkat,
                    'jurusan_id' => $kelas->jurusan_id,
                    'tahun_ajaran' => $tahunAjaranTujuan,
                    'semester' => $semesterTujuan,
                ]);

                $disalin++;
            }

            return [
                'disalin' => $disalin,
                'dilewati' => $dilewati,
            ];
        });
    }

    /**
     * Memindahkan siswa-siswa terpilih dari kelas asal ke kelas tujuan.
     * Hanya mengubah siswa.kelas_id; detail_absensi dan sesi lama tidak diubah.
     *
     * @param  array<int>  $siswaIds
     * @return int Jumlah siswa yang dipindahkan
     */
    public function pindahkanSiswa(int $kelasAsalId, int $kelasTujuanId, array $siswaIds): int
    {
        if ($kelasAsalId === $kelasTujuanId) {
            throw new InvalidArgumentException('Kelas tujuan harus berbeda dari kelas asal.');
        }

        if (count($siswaIds) > self::MAX_SISWA_PINDAH) {
            throw new InvalidArgumentException('Jumlah siswa yang dipindahkan melebihi batas maksimal ('.self::MAX_SISWA_PINDAH.' siswa).');
        }

        return DB::transaction(function () use ($kelasAsalId, $kelasTujuanId, $siswaIds) {
            // Pastikan kelas tujuan ada dan aktif
            $kelasTujuan = Kelas::find($kelasTujuanId);
            if (! $kelasTujuan) {
                throw new InvalidArgumentException('Kelas tujuan tidak valid atau sudah terhapus.');
            }

            // Validasi di sisi server: setiap siswa harus benar-benar siswa aktif di kelas asal
            $validSiswaCount = Siswa::whereIn('id', $siswaIds)
                ->where('kelas_id', $kelasAsalId)
                ->whereNull('deleted_at')
                ->count();

            if ($validSiswaCount !== count($siswaIds)) {
                throw new InvalidArgumentException('Terdapat siswa yang tidak valid, bukan siswa aktif, atau bukan berasal dari kelas asal.');
            }

            // Pindahkan kelas siswa
            return Siswa::whereIn('id', $siswaIds)
                ->where('kelas_id', $kelasAsalId)
                ->update(['kelas_id' => $kelasTujuanId]);
        });
    }

    /**
     * Meluluskan siswa-siswa terpilih dari kelas tertentu.
     * Menggunakan Soft Delete model Siswa ($siswa->delete()).
     * JANGAN PERNAH forceDelete. Akun User TIDAK dihapus.
     *
     * @param  array<int>  $siswaIds
     * @return int Jumlah siswa yang diluluskan
     */
    public function luluskanSiswa(int $kelasId, array $siswaIds): int
    {
        if (count($siswaIds) > self::MAX_SISWA_LULUS) {
            throw new InvalidArgumentException('Jumlah siswa yang diluluskan melebihi batas maksimal ('.self::MAX_SISWA_LULUS.' siswa).');
        }

        return DB::transaction(function () use ($kelasId, $siswaIds) {
            // Validasi bahwa semua siswa_ids adalah siswa aktif di kelasId
            $siswaList = Siswa::whereIn('id', $siswaIds)
                ->where('kelas_id', $kelasId)
                ->whereNull('deleted_at')
                ->get();

            if ($siswaList->count() !== count($siswaIds)) {
                throw new InvalidArgumentException('Terdapat siswa yang tidak valid atau bukan siswa aktif dari kelas yang dipilih.');
            }

            $count = 0;
            foreach ($siswaList as $siswa) {
                // Soft delete model Siswa, jangan forceDelete, dan akun user tidak disentuh
                $siswa->delete();
                $count++;
            }

            return $count;
        });
    }
}
