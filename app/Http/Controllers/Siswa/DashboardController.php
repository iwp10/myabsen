<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\DetailAbsensi;
use App\Models\Mapel;
use App\Services\AbsensiService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected AbsensiService $absensiService;

    public function __construct(AbsensiService $absensiService)
    {
        $this->absensiService = $absensiService;
    }

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

    public function riwayat(Request $request)
    {
        $siswa = $request->user()->siswa;

        if (! $siswa) {
            $riwayat = collect();
            $mapels = collect();
            $persentasePerMapel = collect();

            return view('siswa.riwayat', compact('riwayat', 'mapels', 'persentasePerMapel'));
        }

        $query = DetailAbsensi::with(['sesiAbsensi.jadwal.mapel', 'sesiAbsensi.jadwal.guru.user'])
            ->where('siswa_id', $siswa->id);

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

        // Fix column ambiguity when using paginate, joining sesi_absensi
        $riwayat = $query->join('sesi_absensi', 'detail_absensi.sesi_absensi_id', '=', 'sesi_absensi.id')
            ->orderBy('sesi_absensi.tanggal', 'desc')
            ->select('detail_absensi.*')
            ->paginate(15)
            ->withQueryString();

        // For filter options
        $mapels = Mapel::whereHas('jadwal', function ($q) use ($siswa) {
            $q->where('kelas_id', $siswa->kelas_id);
        })->get();

        // Rekap per mapel didelegasikan ke AbsensiService
        $persentasePerMapel = $this->absensiService->getRekapPerMapelSiswa($siswa->id);

        return view('siswa.riwayat', compact('riwayat', 'mapels', 'persentasePerMapel'));
    }
}
