<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJadwalRequest;
use App\Http\Requests\UpdateJadwalRequest;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $reqTahunAjaran = $request->query('tahun_ajaran');
        $reqSemester = $request->query('semester');
        
        $absensiService = app(\App\Services\AbsensiService::class);
        $activePeriode = $absensiService->getActivePeriode();

        // Jika tidak ada filter yang dipilih, gunakan active periode
        $filterTahunAjaran = $reqTahunAjaran ?? $activePeriode['tahun_ajaran'];
        $filterSemester = $reqSemester ?? $activePeriode['semester'];

        $jadwals = Jadwal::with(['kelas', 'mapel', 'guru.user'])
            ->when($filterTahunAjaran && $filterTahunAjaran !== 'all', function ($q) use ($filterTahunAjaran) {
                $q->where('tahun_ajaran', $filterTahunAjaran);
            })
            ->when($filterSemester && $filterTahunAjaran !== 'all', function ($q) use ($filterSemester) {
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

        $tahunAjarans = Jadwal::select('tahun_ajaran')->distinct()->orderBy('tahun_ajaran', 'desc')->pluck('tahun_ajaran');

        return view('admin.jadwal.index', compact('jadwals', 'activePeriode', 'filterTahunAjaran', 'filterSemester', 'tahunAjarans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kelas = Kelas::orderBy('tingkat')->orderBy('nama')->get();
        $mapel = Mapel::orderBy('nama')->get();
        $guru = Guru::with('user')->get()->sortBy('user.name');

        return view('admin.jadwal.create', compact('kelas', 'mapel', 'guru'));
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

        return view('admin.jadwal.edit', compact('jadwal', 'kelas', 'mapel', 'guru'));
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
