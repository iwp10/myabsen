<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Services\AbsensiService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KoreksiAbsensiController extends Controller
{
    public function __construct(protected AbsensiService $absensiService) {}

    /**
     * Menampilkan halaman Koreksi Absensi untuk admin.
     */
    public function index(Request $request)
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $tanggalInput = $request->query('tanggal', $today);
        $kelasId = $request->query('kelas_id') ? (int) $request->query('kelas_id') : null;

        $errorTanggal = null;
        try {
            $tanggalObj = Carbon::parse($tanggalInput, 'Asia/Jakarta')->startOfDay();
            if ($this->absensiService->isTanggalMasaDepan($tanggalObj)) {
                $errorTanggal = 'Tanggal absensi tidak boleh di masa depan.';
                $tanggalInput = $today;
                $tanggalObj = Carbon::now('Asia/Jakarta')->startOfDay();
            }
        } catch (\Exception $e) {
            $errorTanggal = 'Format tanggal tidak valid.';
            $tanggalInput = $today;
            $tanggalObj = Carbon::now('Asia/Jakarta')->startOfDay();
        }

        $kelasList = Kelas::with('jurusan')->orderBy('tingkat')->orderBy('nama')->get();

        $jadwals = collect();
        if ($tanggalObj && ! $errorTanggal) {
            $jadwals = $this->absensiService->getJadwalKoreksiAdmin($tanggalObj->toDateString(), $kelasId);
        }

        return view('admin.koreksi_absensi.index', [
            'tanggal' => $tanggalInput,
            'tanggalObj' => $tanggalObj,
            'kelasId' => $kelasId,
            'kelasList' => $kelasList,
            'jadwals' => $jadwals,
            'errorTanggal' => $errorTanggal,
            'hari' => AbsensiService::getHariServer($tanggalObj),
        ]);
    }
}
