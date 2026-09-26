<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\DetailAbsensi;
use App\Models\Jadwal;
use App\Models\Mapel;
use App\Models\SesiAbsensi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $siswa = $request->user()->siswa;

        $hariIni = strtolower(Carbon::now()->locale('id')->isoFormat('dddd'));
        $tanggalHariIni = Carbon::today()->format('Y-m-d');

        $jadwals = Jadwal::with(['mapel', 'guru.user'])
            ->where('kelas_id', $siswa->kelas_id)
            ->where('hari', $hariIni)
            ->orderBy('jam_mulai')
            ->get();

        $statusHariIni = $jadwals->map(function ($jadwal) use ($tanggalHariIni, $siswa) {
            $sesi = SesiAbsensi::where('jadwal_id', $jadwal->id)
                ->where('tanggal', $tanggalHariIni)
                ->first();

            $status = null;
            if ($sesi) {
                $detail = DetailAbsensi::where('sesi_absensi_id', $sesi->id)
                    ->where('siswa_id', $siswa->id)
                    ->first();
                $status = $detail ? $detail->status : null;
            }

            return [
                'jadwal' => $jadwal,
                'status_label' => $status ? $status->label() : 'Belum diabsen',
                'status_value' => $status ? $status->value : null,
            ];
        });

        return view('siswa.dashboard', compact('statusHariIni'));
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
                DB::raw('SUM(CASE WHEN detail_absensi.status = "hadir" THEN 1 ELSE 0 END) as total_hadir')
            )
            ->groupBy('mapel.id', 'mapel.nama')
            ->get()
            ->map(function ($item) {
                $item->persentase = $item->total_sesi > 0
                    ? round(($item->total_hadir / $item->total_sesi) * 100, 2)
                    : 0;

                return $item;
            });

        return view('siswa.riwayat', compact('riwayat', 'mapels', 'persentasePerMapel'));
    }
}
