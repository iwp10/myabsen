<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportLaporanRequest;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Services\LaporanService;
use App\Services\PeriodeService;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function __construct(
        protected LaporanService $laporanService,
        protected PeriodeService $periodeService
    ) {}

    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama')->get();
        $mapel = Mapel::orderBy('nama')->get();
        $activePeriode = $this->periodeService->getActivePeriode();
        $daftarTahunAjaran = $this->periodeService->getDaftarPilihanTahunAjaran();

        return view('admin.laporan.index', compact('kelas', 'mapel', 'activePeriode', 'daftarTahunAjaran'));
    }

    public function export(ExportLaporanRequest $request)
    {
        return $this->laporanService->exportExcel($request->user(), $request->validated());
    }

    public function exportPdf(ExportLaporanRequest $request)
    {
        return $this->laporanService->exportPdf($request->user(), $request->validated());
    }
}
