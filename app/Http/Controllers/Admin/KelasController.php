<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KelasFilterRequest;
use App\Http\Requests\StoreKelasRequest;
use App\Http\Requests\UpdateKelasRequest;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Services\PeriodeService;

class KelasController extends Controller
{
    public function index(KelasFilterRequest $request)
    {
        $search = $request->search;
        $periodeService = app(PeriodeService::class);
        $activePeriode = $periodeService->getActivePeriode();
        $daftarPeriode = $periodeService->getDaftarKombinasiPeriode();

        $reqPeriode = $request->query('periode');
        $reqTahunAjaran = $request->query('tahun_ajaran');
        $reqSemester = $request->query('semester');
        $filterTingkat = $request->query('tingkat');

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

        $kelas = Kelas::with('jurusan')
            ->when($filterTahunAjaran, function ($q) use ($filterTahunAjaran) {
                $q->where('tahun_ajaran', $filterTahunAjaran);
            })
            ->when($filterSemester, function ($q) use ($filterSemester) {
                $q->where('semester', $filterSemester);
            })
            ->when($filterTingkat, function ($q) use ($filterTingkat) {
                $q->where('tingkat', $filterTingkat);
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
            ->orderBy('tahun_ajaran', 'desc')
            ->orderByRaw("CASE semester WHEN 'Genap' THEN 1 WHEN 'Ganjil' THEN 2 ELSE 3 END")
            ->orderBy('tingkat', 'asc')
            ->orderBy('nama', 'asc')
            ->paginate(10)
            ->withQueryString();

        $daftarTingkatDb = Kelas::query()
            ->select('tingkat')
            ->distinct()
            ->whereNotNull('tingkat')
            ->where('tingkat', '!=', '')
            ->orderBy('tingkat')
            ->pluck('tingkat')
            ->all();

        $daftarTingkat = array_values(array_unique(array_merge(['10', '11', '12'], $daftarTingkatDb)));
        usort($daftarTingkat, function ($a, $b) {
            if (is_numeric($a) && is_numeric($b)) {
                return (int) $a <=> (int) $b;
            }

            return strnatcmp((string) $a, (string) $b);
        });

        return view('admin.kelas.index', compact(
            'kelas',
            'activePeriode',
            'daftarPeriode',
            'filterPeriode',
            'filterTahunAjaran',
            'filterSemester',
            'daftarTingkat',
            'filterTingkat'
        ));
    }

    public function create()
    {
        $jurusans = Jurusan::orderBy('nama')->get();
        $periodeService = app(PeriodeService::class);
        $activePeriode = $periodeService->getActivePeriode();
        $daftarTahunAjaran = $periodeService->getDaftarPilihanTahunAjaran();

        return view('admin.kelas.create', compact('jurusans', 'activePeriode', 'daftarTahunAjaran'));
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
        $periodeService = app(PeriodeService::class);
        $activePeriode = $periodeService->getActivePeriode();
        $daftarTahunAjaran = $periodeService->getDaftarPilihanTahunAjaran();

        return view('admin.kelas.edit', compact('kelas', 'jurusans', 'activePeriode', 'daftarTahunAjaran'));
    }

    public function update(UpdateKelasRequest $request, Kelas $kelas)
    {
        $kelas->update($request->validated());

        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas)
    {
        $siswaAktifCount = $kelas->siswa()->count();
        if ($siswaAktifCount > 0) {
            return redirect()->route('admin.kelas.index')
                ->with('error', "Kelas ini masih berisi {$siswaAktifCount} siswa aktif. Pindahkan atau luluskan siswa terlebih dahulu.");
        }

        if ($kelas->jadwal()->exists()) {
            return redirect()->route('admin.kelas.index')
                ->with('error', 'Kelas tidak dapat dihapus karena masih memiliki jadwal aktif.');
        }

        $kelas->delete();

        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}
