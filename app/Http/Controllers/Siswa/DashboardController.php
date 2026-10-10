<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\RiwayatSiswaFilterRequest;
use App\Models\DetailAbsensi;
use App\Models\Mapel;
use App\Services\AbsensiService;
use App\Services\PeriodeService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected AbsensiService $absensiService,
        protected PeriodeService $periodeService
    ) {}

    public function dashboard(Request $request)
    {
        $siswa = $request->user()->siswa;
        $tanggalHariIni = Carbon::now('Asia/Jakarta');

        $statusHariIni = $siswa
            ? $this->absensiService->getStatusHariIniSiswa($siswa, $tanggalHariIni)
            : collect();

        $ringkasanKehadiran = $siswa
            ? $this->absensiService->getRingkasanKehadiranSiswa($siswa->id)
            : [
                'total_hadir' => 0,
                'total_izin' => 0,
                'total_sakit' => 0,
                'total_alpa' => 0,
                'total_sesi' => 0,
                'persentase' => 0.0,
            ];

        return view('siswa.dashboard', compact('statusHariIni', 'ringkasanKehadiran'));
    }

    public function riwayat(RiwayatSiswaFilterRequest $request)
    {
        $siswa = $request->user()->siswa;
        $activePeriode = $this->absensiService->getActivePeriode();

        if (! $siswa) {
            return view('siswa.riwayat', [
                'riwayat' => collect(),
                'mapels' => collect(),
                'persentasePerMapel' => collect(),
                'daftarPeriode' => [],
                'selectedPeriode' => $activePeriode,
                'filterPeriodeValue' => '',
                'activeTab' => 'per_mapel',
            ]);
        }

        $daftarPeriode = $this->periodeService->getDaftarPeriodeSiswa($siswa);

        $validated = $request->validated();
        $tahunAjaran = $validated['tahun_ajaran'] ?? null;
        $semester = $validated['semester'] ?? null;
        $activeTab = $validated['tab'] ?? 'per_mapel';

        $selectedPeriode = [
            'tahun_ajaran' => $tahunAjaran ?: $activePeriode['tahun_ajaran'],
            'semester' => $semester ?: $activePeriode['semester'],
        ];

        $filterPeriodeValue = $selectedPeriode['tahun_ajaran'].'|'.$selectedPeriode['semester'];

        $query = DetailAbsensi::with(['sesiAbsensi.jadwal.mapel', 'sesiAbsensi.jadwal.guru.user'])
            ->where('siswa_id', $siswa->id);

        if (! empty($selectedPeriode['tahun_ajaran'])) {
            $query->whereHas('sesiAbsensi.jadwal', function ($q) use ($selectedPeriode) {
                $q->where('tahun_ajaran', $selectedPeriode['tahun_ajaran'])
                    ->where('semester', $selectedPeriode['semester']);
            });
        }

        if ($request->filled('tanggal')) {
            $query->whereHas('sesiAbsensi', function ($q) use ($request) {
                $q->where('tanggal', $request->tanggal);
            });
        }

        if ($request->filled('mapel_id')) {
            $query->whereHas('sesiAbsensi.jadwal', function ($q) use ($request) {
                $q->where('mapel_id', $request->mapel_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('detail_absensi.status', $request->status);
        }

        // Fix column ambiguity when using paginate, joining sesi_absensi
        $riwayat = $query->join('sesi_absensi', 'detail_absensi.sesi_absensi_id', '=', 'sesi_absensi.id')
            ->orderBy('sesi_absensi.tanggal', 'desc')
            ->select('detail_absensi.*')
            ->paginate(15)
            ->withQueryString();

        // For filter options in selected periode
        $mapels = Mapel::whereHas('jadwal', function ($q) use ($siswa, $selectedPeriode) {
            $q->where('kelas_id', $siswa->kelas_id);
            if (! empty($selectedPeriode['tahun_ajaran'])) {
                $q->where('tahun_ajaran', $selectedPeriode['tahun_ajaran'])
                    ->where('semester', $selectedPeriode['semester']);
            }
        })->get();

        // Rekap per mapel didelegasikan ke AbsensiService memakai periode terpilih
        $persentasePerMapel = $this->absensiService->getRekapPerMapelSiswa(
            $siswa->id,
            $selectedPeriode['tahun_ajaran'],
            $selectedPeriode['semester']
        );

        return view('siswa.riwayat', compact(
            'riwayat',
            'mapels',
            'persentasePerMapel',
            'daftarPeriode',
            'selectedPeriode',
            'filterPeriodeValue',
            'activeTab'
        ));
    }
}
