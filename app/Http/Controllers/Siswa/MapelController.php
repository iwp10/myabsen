<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Services\AbsensiService;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    public function __construct(protected AbsensiService $absensiService) {}

    /**
     * Menampilkan daftar mata pelajaran pada jadwal kelas siswa di periode aktif.
     */
    public function index(Request $request)
    {
        $siswa = $request->user()->siswa;
        $data = $this->absensiService->getMataPelajaranSiswa($siswa);

        return view('siswa.mapel', $data);
    }
}
