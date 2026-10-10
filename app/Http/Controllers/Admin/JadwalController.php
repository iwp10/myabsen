<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\JadwalFilterRequest;
use App\Http\Requests\StoreJadwalRequest;
use App\Http\Requests\UpdateJadwalRequest;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Services\PeriodeService;

class JadwalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(JadwalFilterRequest $request)
    {
        $search = $request->query('search');
        $periodeService = app(PeriodeService::class);
        $activePeriode = $periodeService->getActivePeriode();
        $daftarPeriode = $periodeService->getDaftarKombinasiPeriode();

        $reqPeriode = $request->query('periode');
        $reqTahunAjaran = $request->query('tahun_ajaran');
        $reqSemester = $request->query('semester');

        $filterTahunAjaran = null;
        $filterSemester = null;
        $filterPeriode = '';

        if (! empty($reqPeriode) && $reqPeriode !== 'all') {
            $filterPeriode = $reqPeriode;
            if (str_contains($reqPeriode, '|')) {
                [$filterTahunAjaran, $filterSemester] = explode('|', $reqPeriode, 2);
            } elseif (str_contains($reqPeriode, ' - ')) {
                [$filterTahunAjaran, $filterSemester] = explode(' - ', $reqPeriode, 2);
            }
        } elseif (! empty($reqTahunAjaran) || ! empty($reqSemester)) {
            if (! empty($reqTahunAjaran) && $reqTahunAjaran !== 'all') {
                $filterTahunAjaran = $reqTahunAjaran;
            }
            if (! empty($reqSemester) && $reqSemester !== 'all') {
                $filterSemester = $reqSemester;
            }
            if ($filterTahunAjaran && $filterSemester) {
                $filterPeriode = $filterTahunAjaran.'|'.$filterSemester;
            }
        }

        $filterHari = $request->query('hari');
        $filterKelasId = $request->query('kelas_id');
        $filterGuruId = $request->query('guru_id');
        $filterMapelId = $request->query('mapel_id');
        $filterJamMulaiDari = $request->query('jam_mulai_dari', $request->query('jam_dari'));
        $filterJamMulaiSampai = $request->query('jam_mulai_sampai', $request->query('jam_sampai'));

        $jadwals = Jadwal::with(['kelas', 'mapel', 'guru.user'])
            ->when($filterTahunAjaran, function ($q) use ($filterTahunAjaran) {
                $q->where('tahun_ajaran', $filterTahunAjaran);
            })
            ->when($filterSemester, function ($q) use ($filterSemester) {
                $q->where('semester', $filterSemester);
            })
            ->when($filterHari, function ($q) use ($filterHari) {
                $q->where('hari', strtolower($filterHari));
            })
            ->when($filterKelasId, function ($q) use ($filterKelasId) {
                $q->where('kelas_id', $filterKelasId);
            })
            ->when($filterGuruId, function ($q) use ($filterGuruId) {
                $q->where('guru_id', $filterGuruId);
            })
            ->when($filterMapelId, function ($q) use ($filterMapelId) {
                $q->where('mapel_id', $filterMapelId);
            })
            ->when($filterJamMulaiDari, function ($q) use ($filterJamMulaiDari) {
                $time = strlen($filterJamMulaiDari) === 5 ? $filterJamMulaiDari.':00' : $filterJamMulaiDari;
                $q->where('jam_mulai', '>=', $time);
            })
            ->when($filterJamMulaiSampai, function ($q) use ($filterJamMulaiSampai) {
                $time = strlen($filterJamMulaiSampai) === 5 ? $filterJamMulaiSampai.':00' : $filterJamMulaiSampai;
                $q->where('jam_mulai', '<=', $time);
            })
            ->when($search, function ($query) use ($search) {
                $query->whereHas('kelas', function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%");
                })->orWhereHas('mapel', function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%");
                })->orWhereHas('guru.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->orderByRaw("CASE hari 
                WHEN 'senin' THEN 1 
                WHEN 'selasa' THEN 2 
                WHEN 'rabu' THEN 3 
                WHEN 'kamis' THEN 4 
                WHEN 'jumat' THEN 5 
                WHEN 'sabtu' THEN 6 
                ELSE 7 END")
            ->orderBy('jam_mulai', 'asc')
            ->paginate(10)
            ->withQueryString();

        $daftarKelasQuery = Kelas::orderBy('tingkat')->orderBy('nama');
        if ($filterTahunAjaran && $filterSemester) {
            $daftarKelasQuery->where('tahun_ajaran', $filterTahunAjaran)->where('semester', $filterSemester);
        }
        $daftarKelas = $daftarKelasQuery->get();
        $daftarGuru = Guru::with('user')->get()->sortBy('user.name');
        $daftarMapel = Mapel::orderBy('nama')->get();
        $daftarHariOptions = [
            'senin' => 'Senin',
            'selasa' => 'Selasa',
            'rabu' => 'Rabu',
            'kamis' => 'Kamis',
            'jumat' => 'Jumat',
            'sabtu' => 'Sabtu',
        ];

        return view('admin.jadwal.index', compact(
            'jadwals',
            'activePeriode',
            'daftarPeriode',
            'filterPeriode',
            'filterTahunAjaran',
            'filterSemester',
            'daftarKelas',
            'daftarGuru',
            'daftarMapel',
            'daftarHariOptions',
            'filterHari',
            'filterKelasId',
            'filterGuruId',
            'filterMapelId',
            'filterJamMulaiDari',
            'filterJamMulaiSampai'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kelas = Kelas::orderBy('tingkat')->orderBy('nama')->get();
        $mapel = Mapel::orderBy('nama')->get();
        $guru = Guru::with('user')->get()->sortBy('user.name');
        $periodeService = app(PeriodeService::class);
        $activePeriode = $periodeService->getActivePeriode();
        $daftarTahunAjaran = $periodeService->getDaftarPilihanTahunAjaran();

        return view('admin.jadwal.create', compact('kelas', 'mapel', 'guru', 'activePeriode', 'daftarTahunAjaran'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJadwalRequest $request)
    {
        Jadwal::create($request->validated());

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Jadwal $jadwal)
    {
        $kelas = Kelas::orderBy('tingkat')->orderBy('nama')->get();
        $mapel = Mapel::orderBy('nama')->get();
        $guru = Guru::with('user')->get()->sortBy('user.name');
        $periodeService = app(PeriodeService::class);
        $activePeriode = $periodeService->getActivePeriode();
        $daftarTahunAjaran = $periodeService->getDaftarPilihanTahunAjaran();

        return view('admin.jadwal.edit', compact('jadwal', 'kelas', 'mapel', 'guru', 'activePeriode', 'daftarTahunAjaran'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJadwalRequest $request, Jadwal $jadwal)
    {
        $jadwal->update($request->validated());

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Jadwal $jadwal)
    {
        $hasSesi = $jadwal->sesiAbsensi()->exists();

        if ($hasSesi) {
            return redirect()->route('admin.jadwal.index')->with('error', 'Jadwal tidak dapat dihapus karena sudah memiliki riwayat absensi.');
        }

        $jadwal->delete();

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil dihapus.');
    }
}
