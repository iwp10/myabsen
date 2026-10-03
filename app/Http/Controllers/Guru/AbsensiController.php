<?php

namespace App\Http\Controllers\Guru;

use App\Exports\LaporanAbsensiExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\ShowAbsensiRequest;
use App\Http\Requests\Guru\StoreAbsensiRequest;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Services\AbsensiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

class AbsensiController extends Controller
{
    protected AbsensiService $absensiService;

    public function __construct(AbsensiService $absensiService)
    {
        $this->absensiService = $absensiService;
    }

    /**
     * Menampilkan dashboard (jadwal hari ini).
     */
    public function dashboard(Request $request)
    {
        $tanggal = Carbon::now('Asia/Jakarta');
        $jadwalHariIni = $this->absensiService->getJadwalHariIni($request->user()->id, $tanggal);

        $guru = Guru::where('user_id', $request->user()->id)->first();
        $stats = $guru
            ? $this->absensiService->getStatistikGuru($guru->id)
            : ['total_kelas' => 0, 'total_mapel' => 0, 'total_jadwal' => 0];

        return view('guru.dashboard', [
            'jadwalHariIni' => $jadwalHariIni,
            'tanggal' => $tanggal,
            'total_kelas' => $stats['total_kelas'],
            'total_mapel' => $stats['total_mapel'],
            'total_jadwal' => $stats['total_jadwal'],
        ]);
    }

    /**
     * Menampilkan halaman absensi untuk jadwal tertentu.
     */
    public function show(Jadwal $jadwal, ShowAbsensiRequest $request)
    {
        $tanggalInput = $request->input('tanggal');
        $tanggal = $tanggalInput
            ? Carbon::parse($tanggalInput, 'Asia/Jakarta')
            : Carbon::now('Asia/Jakarta');

        Gate::authorize('absen', [$jadwal, $tanggal]);

        $formData = $this->absensiService->getFormAbsensiData($jadwal, $tanggal);
        $tanggalBolehDikoreksi = $this->absensiService->getTanggalBolehDikoreksi($jadwal);

        return view('guru.absensi', [
            'jadwal' => $jadwal,
            'tanggal' => $tanggal,
            'sesi' => $formData['sesi'],
            'detailExisting' => $formData['detailExisting'],
            'tanggalBolehDikoreksi' => $tanggalBolehDikoreksi,
        ]);
    }

    /**
     * Menyimpan data absensi.
     */
    public function store(StoreAbsensiRequest $request, Jadwal $jadwal)
    {
        $tanggal = $request->validated('tanggal')
            ?? $request->input('tanggal')
            ?? Carbon::now('Asia/Jakarta')->toDateString();

        Gate::authorize('absen', [$jadwal, $tanggal]);

        $dataDetail = $request->validated('siswa') ?? [];
        $catatan = $request->validated('catatan');

        $this->absensiService->simpanAbsensi(
            $jadwal,
            $tanggal,
            $dataDetail,
            $catatan,
            $request->user()->id
        );

        return redirect()->route('guru.dashboard')->with('status', 'Data absensi berhasil disimpan.');
    }

    /**
     * Menampilkan riwayat absensi.
     */
    public function riwayat(Request $request)
    {
        $riwayatSesi = $this->absensiService->getRiwayatSesi($request->user()->id);

        return view('guru.riwayat', [
            'riwayatSesi' => $riwayatSesi,
        ]);
    }

    /**
     * Mengunduh rekap absensi untuk guru.
     */
    public function export(Request $request)
    {
        $guru = Guru::where('user_id', $request->user()->id)->first();

        $filters = [
            'guru_id' => $guru ? $guru->id : null,
            'bulan' => $request->query('bulan'),
        ];

        $filename = 'rekap_absensi_guru';
        if (! empty($filters['bulan'])) {
            $filename .= '_'.$filters['bulan'];
        } else {
            $filename .= '_'.date('Y-m');
        }
        $filename .= '.xlsx';

        return Excel::download(new LaporanAbsensiExport($filters), $filename);
    }

    /**
     * Mengunduh rekap absensi untuk guru dalam format PDF.
     */
    public function exportPdf(Request $request)
    {
        $guru = Guru::where('user_id', $request->user()->id)->first();

        $filters = [
            'guru_id' => $guru ? $guru->id : null,
            'bulan' => $request->query('bulan'),
        ];

        $absensiService = app(AbsensiService::class);
        $data = $absensiService->getRekapLaporan($filters);

        $filename = 'rekap_absensi_guru';
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
