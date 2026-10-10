<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\JadwalGuruFilterRequest;
use App\Models\Guru;
use App\Services\AbsensiService;
use Carbon\Carbon;

class JadwalController extends Controller
{
    public function __construct(protected AbsensiService $absensiService) {}

    /**
     * Menampilkan daftar seluruh jadwal mengajar guru dalam seminggu.
     */
    public function index(JadwalGuruFilterRequest $request)
    {
        $guru = Guru::where('user_id', $request->user()->id)->first();
        $filterHari = $request->validated('hari');
        $filterKelasId = $request->validated('kelas_id') ? (int) $request->validated('kelas_id') : null;
        $filterMapelId = $request->validated('mapel_id') ? (int) $request->validated('mapel_id') : null;

        $jadwals = $this->absensiService->getJadwalMingguanGuru(
            $request->user()->id,
            $filterHari,
            $filterKelasId,
            $filterMapelId
        );
        $hariIni = AbsensiService::getHariServer(Carbon::now('Asia/Jakarta'));

        $stats = $guru
            ? $this->absensiService->getStatistikGuru($guru->id)
            : ['daftar_kelas' => collect(), 'daftar_mapel' => collect()];

        $daftarKelas = $stats['daftar_kelas'];
        $daftarMapel = $stats['daftar_mapel'];

        $activeKelas = $filterKelasId ? $daftarKelas->firstWhere('id', $filterKelasId) : null;
        $activeMapel = $filterMapelId ? $daftarMapel->firstWhere('id', $filterMapelId) : null;

        if ($filterHari) {
            $jadwalHariQuery = $this->absensiService->getJadwalMingguanGuru($request->user()->id, $filterHari);
            $kelasHariIds = $jadwalHariQuery->pluck('kelas_id')->unique();
            $mapelHariIds = $jadwalHariQuery->pluck('mapel_id')->unique();

            $daftarKelas = $daftarKelas->filter(fn ($k) => $kelasHariIds->contains($k['id']) || $k['id'] === $filterKelasId)->values();
            $daftarMapel = $daftarMapel->filter(fn ($m) => $mapelHariIds->contains($m['id']) || $m['id'] === $filterMapelId)->values();
        }

        return view('guru.jadwal', compact(
            'jadwals',
            'guru',
            'filterHari',
            'filterKelasId',
            'filterMapelId',
            'daftarKelas',
            'daftarMapel',
            'activeKelas',
            'activeMapel',
            'hariIni'
        ));
    }
}
