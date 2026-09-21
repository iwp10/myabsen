<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\StoreAbsensiRequest;
use App\Models\Jadwal;
use App\Models\SesiAbsensi;
use App\Services\AbsensiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AbsensiController extends Controller
{
    protected AbsensiService $absensiService;

    public function __construct(AbsensiService $absensiService)
    {
        $this->absensiService = $absensiService;
    }

    /**
     * Menampilkan dashboard (jadwal hari ini).
     */
    public function dashboard(Request $request)
    {
        $tanggal = Carbon::now('Asia/Jakarta');
        $jadwalHariIni = $this->absensiService->getJadwalHariIni($request->user()->id, $tanggal);

        return view('guru.dashboard', [
            'jadwalHariIni' => $jadwalHariIni,
            'tanggal' => $tanggal,
        ]);
    }

    /**
     * Menampilkan halaman absensi untuk jadwal tertentu.
     */
    public function show(Jadwal $jadwal, Request $request)
    {
        Gate::authorize('absen', $jadwal);

        $tanggal = Carbon::now('Asia/Jakarta');

        // Eager load siswa
        $jadwal->load([
            'kelas.siswa' => function ($query) {
                $query->join('users', 'siswa.user_id', '=', 'users.id')
                    ->select('siswa.*')
                    ->orderBy('users.name');
            },
            'kelas.siswa.user',
            'mapel',
        ]);

        // Cek sesi yang sudah ada
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

        return view('guru.absensi', [
            'jadwal' => $jadwal,
            'tanggal' => $tanggal,
            'sesi' => $sesi,
            'detailExisting' => $detailExisting,
        ]);
    }

    /**
     * Menyimpan data absensi.
     */
    public function store(StoreAbsensiRequest $request, Jadwal $jadwal)
    {
        Gate::authorize('absen', $jadwal);

        $tanggal = Carbon::now('Asia/Jakarta')->toDateString();
        $dataDetail = $request->validated('siswa') ?? [];
        $catatan = $request->validated('catatan');

        $this->absensiService->simpanAbsensi(
            $jadwal,
            $tanggal,
            $dataDetail,
            $catatan,
            $request->user()->id
        );

        return redirect()->route('guru.dashboard')->with('status', 'Data absensi berhasil disimpan.');
    }

    /**
     * Menampilkan riwayat absensi.
     */
    public function riwayat(Request $request)
    {
        $riwayatSesi = $this->absensiService->getRiwayatSesi($request->user()->id);

        return view('guru.riwayat', [
            'riwayatSesi' => $riwayatSesi,
        ]);
    }
}
