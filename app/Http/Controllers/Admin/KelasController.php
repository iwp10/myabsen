<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKelasRequest;
use App\Http\Requests\UpdateKelasRequest;
use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $reqTahunAjaran = $request->query('tahun_ajaran');
        $reqSemester = $request->query('semester');
        
        $absensiService = app(\App\Services\AbsensiService::class);
        $activePeriode = $absensiService->getActivePeriode();

        // Jika tidak ada filter yang dipilih, gunakan active periode
        $filterTahunAjaran = $reqTahunAjaran ?? $activePeriode['tahun_ajaran'];
        $filterSemester = $reqSemester ?? $activePeriode['semester'];

        $kelas = Kelas::with('jurusan')
            ->when($filterTahunAjaran && $filterTahunAjaran !== 'all', function ($q) use ($filterTahunAjaran) {
                $q->where('tahun_ajaran', $filterTahunAjaran);
            })
            ->when($filterSemester && $filterTahunAjaran !== 'all', function ($q) use ($filterSemester) {
                $q->where('semester', $filterSemester);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('tingkat', 'like', "%{$search}%")
                        ->orWhere('tahun_ajaran', 'like', "%{$search}%")
                        ->orWhereHas('jurusan', function ($sub) use ($search) {
                            $sub->where('nama', 'like', "%{$search}%")
                                ->orWhere('kode', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        $tahunAjarans = Kelas::select('tahun_ajaran')->distinct()->orderBy('tahun_ajaran', 'desc')->pluck('tahun_ajaran');

        return view('admin.kelas.index', compact('kelas', 'activePeriode', 'filterTahunAjaran', 'filterSemester', 'tahunAjarans'));
    }

    public function create()
    {
        $jurusans = Jurusan::orderBy('nama')->get();

        return view('admin.kelas.create', compact('jurusans'));
    }

    public function store(StoreKelasRequest $request)
    {
        Kelas::create($request->validated());

        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kelas)
    {
        $jurusans = Jurusan::orderBy('nama')->get();

        return view('admin.kelas.edit', compact('kelas', 'jurusans'));
    }

    public function update(UpdateKelasRequest $request, Kelas $kelas)
    {
        $kelas->update($request->validated());

        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas)
    {
        if ($kelas->jadwal()->exists()) {
            return redirect()->route('admin.kelas.index')
                ->with('error', 'Kelas tidak dapat dihapus karena masih memiliki jadwal aktif.');
        }

        $kelas->delete();

        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}
