<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Services\AbsensiService;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function __construct(protected AbsensiService $absensiService) {}

    /**
     * Menampilkan jadwal pelajaran siswa pada kelas dan periode aktif.
     */
    public function index(Request $request)
    {
        $siswa = $request->user()->siswa;
        $filterHari = $request->query('hari');

        $data = $this->absensiService->getJadwalPelajaranSiswa($siswa, $filterHari);

        return view('siswa.jadwal', $data);
    }
}
