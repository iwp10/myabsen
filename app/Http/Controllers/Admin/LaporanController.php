<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LaporanAbsensiExport;
use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Services\AbsensiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama')->get();
        $mapel = Mapel::orderBy('nama')->get();

        return view('admin.laporan.index', compact('kelas', 'mapel'));
    }

    public function export(Request $request)
    {
        $filters = $request->only(['kelas_id', 'mapel_id', 'bulan']);

        $filename = 'rekap_absensi';
        if (! empty($filters['bulan'])) {
            $filename .= '_'.$filters['bulan'];
        } else {
            $filename .= '_'.date('Y-m');
        }
        $filename .= '.xlsx';

        return Excel::download(new LaporanAbsensiExport($filters), $filename);
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['kelas_id', 'mapel_id', 'bulan']);

        $absensiService = app(AbsensiService::class);
        $data = $absensiService->getRekapLaporan($filters);

        $filename = 'rekap_absensi';
        if (! empty($filters['bulan'])) {
            $filename .= '_'.$filters['bulan'];
        } else {
            $filename .= '_'.date('Y-m');
        }
        $filename .= '.pdf';

        $periode = ! empty($filters['bulan'])
            ? Carbon::createFromFormat('Y-m', $filters['bulan'])->translatedFormat('F Y')
            : 'Semua Periode';

        $viewName = view()->exists('admin.laporan.pdf') ? 'admin.laporan.pdf' : 'laporan.pdf';

        $pdf = Pdf::loadView($viewName, [
            'data' => $data,
            'filters' => $filters,
            'periode' => $periode,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }
}
