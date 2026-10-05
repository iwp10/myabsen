<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJadwalRequest;
use App\Http\Requests\UpdateJadwalRequest;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Services\PeriodeService;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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

        $jadwals = Jadwal::with(['kelas', 'mapel', 'guru.user'])
            ->when($filterTahunAjaran, function ($q) use ($filterTahunAjaran) {
                $q->where('tahun_ajaran', $filterTahunAjaran);
            })
            ->when($filterSemester, function ($q) use ($filterSemester) {
                $q->where('semester', $filterSemester);
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
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->paginate(10)
            ->withQueryString();

        return view('admin.jadwal.index', compact(
            'jadwals',
            'activePeriode',
            'daftarPeriode',
            'filterPeriode',
            'filterTahunAjaran',
            'filterSemester'
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
