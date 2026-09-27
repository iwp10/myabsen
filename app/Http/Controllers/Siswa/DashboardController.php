<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\DetailAbsensi;
use App\Models\Jadwal;
use App\Models\Mapel;
use App\Services\AbsensiService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

        $hariIni = strtolower(Carbon::now()->locale('id')->isoFormat('dddd'));
        $tanggalHariIni = Carbon::today()->format('Y-m-d');

        $statusHariIni = collect();

        if ($siswa) {
            $jadwals = Jadwal::with([
                'mapel',
                'guru.user',
                'sesiAbsensi' => function ($q) use ($tanggalHariIni, $siswa) {
                    $q->where('tanggal', $tanggalHariIni)
                        ->with(['detailAbsensi' => function ($dq) use ($siswa) {
                            $dq->where('siswa_id', $siswa->id);
                        }]);
                },
            ])
                ->where('kelas_id', $siswa->kelas_id)
                ->where('hari', $hariIni)
                ->orderBy('jam_mulai')
                ->get();

            $statusHariIni = $jadwals->map(function ($jadwal) {
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

        // Rekap per mapel menggunakan agregasi SQL
        $persentasePerMapel = DB::table('detail_absensi')
            ->join('sesi_absensi', 'detail_absensi.sesi_absensi_id', '=', 'sesi_absensi.id')
            ->join('jadwal', 'sesi_absensi.jadwal_id', '=', 'jadwal.id')
            ->join('mapel', 'jadwal.mapel_id', '=', 'mapel.id')
            ->where('detail_absensi.siswa_id', $siswa->id)
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
                $item->persentase = $this->absensiService->hitungPersentaseKehadiran(
                    (int) $item->total_hadir,
                    (int) $item->total_izin,
                    (int) $item->total_sakit,
                    (int) $item->total_sesi
                );

                return $item;
            });

        return view('siswa.riwayat', compact('riwayat', 'mapels', 'persentasePerMapel'));
    }
}
