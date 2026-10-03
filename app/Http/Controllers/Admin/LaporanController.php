<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportLaporanRequest;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Services\LaporanService;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function __construct(
        protected LaporanService $laporanService
    ) {}

    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama')->get();
        $mapel = Mapel::orderBy('nama')->get();

        return view('admin.laporan.index', compact('kelas', 'mapel'));
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
