<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Http\Request;
use App\Exports\LaporanAbsensiExport;
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
        if (!empty($filters['bulan'])) {
            $filename .= '_' . $filters['bulan'];
        } else {
            $filename .= '_' . date('Y-m');
        }
        $filename .= '.xlsx';

        return Excel::download(new LaporanAbsensiExport($filters), $filename);
    }
}
