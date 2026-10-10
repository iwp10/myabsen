<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\JadwalSiswaFilterRequest;
use App\Services\AbsensiService;

class JadwalController extends Controller
{
    public function __construct(protected AbsensiService $absensiService) {}

    /**
     * Menampilkan jadwal pelajaran siswa pada kelas dan periode aktif.
     */
    public function index(JadwalSiswaFilterRequest $request)
    {
        $siswa = $request->user()->siswa;
        $filterHari = $request->validated('hari');
        $filterMapelId = $request->validated('mapel_id') ? (int) $request->validated('mapel_id') : null;

        $data = $this->absensiService->getJadwalPelajaranSiswa($siswa, $filterHari, $filterMapelId);

        return view('siswa.jadwal', $data);
    }
}
