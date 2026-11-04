<?php

namespace App\Services;

use App\Models\DetailAbsensi;
use App\Models\Jadwal;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SiswaDashboardService
{
    public function __construct(
        protected AbsensiService $absensiService,
        protected PeriodeService $periodeService
    ) {}

    /**
     * Mendapatkan jadwal pembelajaran kelas siswa dalam jendela 7 hari terakhir (H sampai H-6)
     * yang belum memiliki sesi absensi dari guru.
     *
     * Aturan:
     * - Periode aktif saja.
     * - Tanggal masa depan tidak dihitung.
     * - Hari ini hanya dihitung setelah jam mulai jadwal lewat (Asia/Jakarta).
     * - Hanya hari yang cocok dengan hari jadwal (Senin s.d. Sabtu).
     * - Maksimal 7 item disajikan, beserta jumlah sisa jika total > 7.
     * - Kueri efisien konstan (tanpa N+1).
     *
     * @return array{
     *     total: int,
     *     items: Collection<int, object>,
     *     sisa: int,
     *     has_data: bool
     * }
     */
    public function getJadwalBelumDiabsen(Siswa $siswa, ?Carbon $now = null): array
    {
        $now = $now ?? Carbon::now('Asia/Jakarta');
        $activePeriode = $this->periodeService->getActivePeriode();

        if (! $siswa->kelas_id || empty($activePeriode['tahun_ajaran']) || empty($activePeriode['semester'])) {
            return [
                'total' => 0,
                'items' => collect(),
                'sisa' => 0,
                'has_data' => false,
            ];
        }

        // Ambil seluruh jadwal kelas siswa pada periode aktif
        $semuaJadwal = Jadwal::with(['mapel', 'guru.user'])
            ->where('kelas_id', $siswa->kelas_id)
            ->where('tahun_ajaran', $activePeriode['tahun_ajaran'])
            ->where('semester', $activePeriode['semester'])
            ->orderBy('jam_mulai', 'asc')
            ->get();

        if ($semuaJadwal->isEmpty()) {
            return [
                'total' => 0,
                'items' => collect(),
                'sisa' => 0,
                'has_data' => false,
            ];
        }

        $batasHari = (int) config('absensi.batas_koreksi_hari', 7);
        $today = $now->copy()->startOfDay();
        $currentTimeStr = $now->format('H:i:s');

        // Siapkan rentang 7 hari terakhir (hari ini mundur hingga H-6)
        $daftarHari = [];
        $daftarTanggalStr = [];
        for ($i = 0; $i < $batasHari; $i++) {
            $date = $today->copy()->subDays($i);
            $tanggalStr = $date->toDateString();
            $daftarTanggalStr[] = $tanggalStr;
            $hariServer = AbsensiService::getHariServer($date);

            $daftarHari[] = [
                'date' => $date,
                'tanggal' => $tanggalStr,
                'hari' => $hariServer,
                'hari_label' => $hariServer ? ucfirst($hariServer) : $date->locale('id')->isoFormat('dddd'),
                'tanggal_label' => $date->locale('id')->isoFormat('dddd, D MMMM YYYY'),
                'is_hari_ini' => ($i === 0),
            ];
        }

        // Ambil sesi absensi yang ada untuk jadwal kelas pada 7 tanggal tersebut dalam satu kueri
        $sesiList = SesiAbsensi::whereIn('jadwal_id', $semuaJadwal->pluck('id'))
            ->whereIn('tanggal', $daftarTanggalStr)
            ->get()
            ->keyBy(fn ($item) => $item->jadwal_id.'_'.$item->tanggal);

        $belumDiabsenList = collect();

        // Evaluasi per tanggal secara berurutan (dari hari ini mundur ke hari-hari sebelumnya)
        foreach ($daftarHari as $hariItem) {
            $hari = $hariItem['hari'];
            $tgl = $hariItem['tanggal'];
            $isHariIni = $hariItem['is_hari_ini'];
            $tglLabel = $hariItem['tanggal_label'];

            if (! $hari) {
                continue; // Melewati hari Minggu / tidak valid
            }

            $jadwalHari = $semuaJadwal->where('hari', $hari);

            foreach ($jadwalHari as $j) {
                // Sesi sudah ada -> berarti sudah diabsen guru
                if (isset($sesiList[$j->id.'_'.$tgl])) {
                    continue;
                }

                // Hari ini: baru dihitung jika jam mulai jadwal sudah lewat
                if ($isHariIni) {
                    $jamMulai = strlen($j->jam_mulai) === 5 ? $j->jam_mulai.':00' : $j->jam_mulai;
                    if ($currentTimeStr < $jamMulai) {
                        continue;
                    }
                }

                $belumDiabsenList->push((object) [
                    'jadwal_id' => $j->id,
                    'mapel_nama' => $j->mapel?->nama ?? '-',
                    'guru_nama' => $j->guru?->user?->name ?? 'Belum ditentukan',
                    'tanggal' => $tgl,
                    'tanggal_label' => $tglLabel,
                    'hari' => $hari,
                    'jam_mulai' => substr($j->jam_mulai, 0, 5),
                    'jam_selesai' => substr($j->jam_selesai, 0, 5),
                ]);
            }
        }

        $total = $belumDiabsenList->count();
        $items = $belumDiabsenList->take(7);
        $sisa = max(0, $total - 7);

        return [
            'total' => $total,
            'items' => $items,
            'sisa' => $sisa,
            'has_data' => $total > 0,
        ];
    }

    /**
     * Mendapatkan rekapitulasi kehadiran siswa dalam 7 hari terakhir (H sampai H-6) pada periode aktif.
     * Menggunakan rumus AB-07: ((Hadir + Izin + Sakit) / Total Pertemuan) * 100.
     *
     * @return array{
     *     total_hadir: int,
     *     total_izin: int,
     *     total_sakit: int,
     *     total_alpa: int,
     *     total_sesi: int,
     *     persentase: float,
     *     has_data: bool,
     *     rentang_label: string
     * }
     */
    public function getRingkasanMingguIni(Siswa $siswa, ?Carbon $now = null): array
    {
        $now = $now ?? Carbon::now('Asia/Jakarta');
        $activePeriode = $this->periodeService->getActivePeriode();

        $startDate = $now->copy()->subDays(6)->toDateString();
        $endDate = $now->copy()->toDateString();

        $rentangLabel = Carbon::parse($startDate)->locale('id')->isoFormat('D MMMM')
            .' - '
            .Carbon::parse($endDate)->locale('id')->isoFormat('D MMMM YYYY');

        if (empty($activePeriode['tahun_ajaran']) || empty($activePeriode['semester'])) {
            return [
                'total_hadir' => 0,
                'total_izin' => 0,
                'total_sakit' => 0,
                'total_alpa' => 0,
                'total_sesi' => 0,
                'persentase' => 0.0,
                'has_data' => false,
                'rentang_label' => $rentangLabel,
            ];
        }

        $counts = DetailAbsensi::where('siswa_id', $siswa->id)
            ->join('sesi_absensi', 'detail_absensi.sesi_absensi_id', '=', 'sesi_absensi.id')
            ->join('jadwal', 'sesi_absensi.jadwal_id', '=', 'jadwal.id')
            ->whereBetween('sesi_absensi.tanggal', [$startDate, $endDate])
            ->where('jadwal.tahun_ajaran', $activePeriode['tahun_ajaran'])
            ->where('jadwal.semester', $activePeriode['semester'])
            ->select(
                DB::raw("SUM(CASE WHEN detail_absensi.status = 'hadir' THEN 1 ELSE 0 END) as total_hadir"),
                DB::raw("SUM(CASE WHEN detail_absensi.status = 'izin' THEN 1 ELSE 0 END) as total_izin"),
                DB::raw("SUM(CASE WHEN detail_absensi.status = 'sakit' THEN 1 ELSE 0 END) as total_sakit"),
                DB::raw("SUM(CASE WHEN detail_absensi.status = 'alpa' THEN 1 ELSE 0 END) as total_alpa"),
                DB::raw('COUNT(detail_absensi.id) as total_sesi')
            )
            ->first();

        $totalHadir = (int) ($counts->total_hadir ?? 0);
        $totalIzin = (int) ($counts->total_izin ?? 0);
        $totalSakit = (int) ($counts->total_sakit ?? 0);
        $totalAlpa = (int) ($counts->total_alpa ?? 0);
        $totalSesi = (int) ($counts->total_sesi ?? 0);

        $persentase = $this->absensiService->hitungPersentaseKehadiran(
            $totalHadir,
            $totalIzin,
            $totalSakit,
            $totalSesi
        );

        return [
            'total_hadir' => $totalHadir,
            'total_izin' => $totalIzin,
            'total_sakit' => $totalSakit,
            'total_alpa' => $totalAlpa,
            'total_sesi' => $totalSesi,
            'persentase' => $persentase,
            'has_data' => $totalSesi > 0,
            'rentang_label' => $rentangLabel,
        ];
    }

    /**
     * Mendapatkan daftar mata pelajaran pada periode aktif yang persentase kehadirannya
     * berada di bawah batas konfigurasi (absensi.batas_kehadiran_rendah).
     *
     * Catatan: Tepat di batas tidak masuk; diurutkan dari yang persentasenya paling rendah, maksimal 5 mapel.
     *
     * @return Collection<int, object>
     */
    public function getMapelPerluPerhatian(Siswa $siswa): Collection
    {
        $activePeriode = $this->periodeService->getActivePeriode();

        if (empty($activePeriode['tahun_ajaran']) || empty($activePeriode['semester'])) {
            return collect();
        }

        $rekapPerMapel = $this->absensiService->getRekapPerMapelSiswa(
            $siswa->id,
            $activePeriode['tahun_ajaran'],
            $activePeriode['semester']
        );

        $batasRendah = (float) config('absensi.batas_kehadiran_rendah', 75);

        // Hanya yang persentasenya strictly di bawah ambang batas
        return $rekapPerMapel
            ->filter(fn ($item) => (float) $item->persentase < $batasRendah)
            ->sortBy('persentase')
            ->values()
            ->take(5);
    }
}
