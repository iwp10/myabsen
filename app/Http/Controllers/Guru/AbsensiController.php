<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportLaporanRequest;
use App\Http\Requests\Guru\ShowAbsensiRequest;
use App\Http\Requests\Guru\StoreAbsensiRequest;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Services\AbsensiService;
use App\Services\LaporanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AbsensiController extends Controller
{
    public function __construct(
        protected AbsensiService $absensiService,
        protected LaporanService $laporanService
    ) {}

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
     * Menampilkan halaman Jadwal & Koreksi Absensi untuk guru dalam 7 hari terakhir.
     */
    public function koreksiAbsensi(Request $request)
    {
        $guru = Guru::where('user_id', $request->user()->id)->first();
        $daftarHari = $this->absensiService->getJadwalKoreksiTujuhHariGuru($request->user()->id);

        return view('guru.koreksi_absensi', [
            'guru' => $guru,
            'daftarHari' => $daftarHari,
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
     * Menampilkan riwayat absensi per kelas & mapel.
     */
    public function riwayat(Request $request)
    {
        $guru = Guru::where('user_id', $request->user()->id)->first();
        if (! $guru) {
            return view('guru.riwayat', ['kelompokRiwayat' => collect()]);
        }

        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');

        // Ambil semua jadwal milik guru, unik per kelas+mapel
        $jadwalList = Jadwal::with(['kelas', 'mapel'])
            ->where('guru_id', $guru->id)
            ->get()
            ->unique(fn($j) => $j->kelas_id . '-' . $j->mapel_id);

        // Untuk setiap kelas+mapel, ambil sesi absensi beserta detail siswa
        $kelompokRiwayat = $jadwalList->map(function ($jadwal) use ($guru, $tanggalAwal, $tanggalAkhir) {
            $jadwalIds = Jadwal::where('guru_id', $guru->id)
                ->where('kelas_id', $jadwal->kelas_id)
                ->where('mapel_id', $jadwal->mapel_id)
                ->pluck('id');

            $sesiQuery = \App\Models\SesiAbsensi::with([
                    'detailAbsensi.siswa.user',
                ])
                ->whereIn('jadwal_id', $jadwalIds)
                ->orderBy('tanggal', 'asc');

            if ($tanggalAwal) $sesiQuery->where('tanggal', '>=', $tanggalAwal);
            if ($tanggalAkhir) $sesiQuery->where('tanggal', '<=', $tanggalAkhir);

            $sesiList = $sesiQuery->get();

            // Ambil daftar siswa di kelas ini
            $siswaList = \App\Models\Siswa::with('user')
                ->where('kelas_id', $jadwal->kelas_id)
                ->whereNull('deleted_at')
                ->get()
                ->sortBy('user.name');

            return [
                'jadwal'    => $jadwal,
                'sesiList'  => $sesiList,
                'siswaList' => $siswaList,
            ];
        })->values();

        return view('guru.riwayat', [
            'kelompokRiwayat' => $kelompokRiwayat,
            'tanggalAwal'     => $tanggalAwal,
            'tanggalAkhir'    => $tanggalAkhir,
        ]);
    }

    /**
     * Mengunduh rekap absensi untuk guru dalam format Excel.
     */
    public function export(Request $request)
    {
        $guru = $request->user()->guru;
        if (!$guru) {
            abort(403);
        }

        $filters = $request->only([
            'kelas_mapel', 'tanggal_awal', 'tanggal_akhir', 'bulan', 'mode',
        ]);
        // Jumlah pertemuan tetap 30, tidak perlu input dari user
        $filters['jumlah_pertemuan'] = 30;
        $filters['mode'] = $filters['mode'] ?? 'data';

        $filename = 'Rekap_Absensi_' . str_replace(' ', '_', $guru->user->name) . '_' . date('Ymd') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LaporanAbsensiGuruExport($guru->id, $filters),
            $filename
        );
    }

    /**
     * Mengunduh rekap absensi untuk guru dalam format PDF.
     */
    public function exportPdf(ExportLaporanRequest $request)
    {
        return $this->laporanService->exportPdf($request->user(), $request->validated());
    }
}
