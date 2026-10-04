<?php

namespace App\Http\Controllers\Guru;

use App\Exports\LaporanAbsensiGuruExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExportLaporanRequest;
use App\Http\Requests\Guru\ShowAbsensiRequest;
use App\Http\Requests\Guru\StoreAbsensiRequest;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Services\AbsensiService;
use App\Services\LaporanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

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
            return view('guru.riwayat', ['jadwalList' => collect()]);
        }

        // Ambil semua jadwal milik guru, unik per kelas+mapel
        $jadwalList = Jadwal::with(['kelas', 'mapel'])
            ->where('guru_id', $guru->id)
            ->get()
            ->unique(fn ($j) => $j->kelas_id.'-'.$j->mapel_id)
            ->values();

        return view('guru.riwayat', [
            'jadwalList' => $jadwalList,
        ]);
    }

    /**
     * Menampilkan riwayat absensi untuk satu kelas & mapel tertentu.
     */
    public function riwayatDetail(Request $request, Kelas $kelas, Mapel $mapel)
    {
        $guru = Guru::where('user_id', $request->user()->id)->first();
        if (! $guru) {
            abort(403);
        }

        // Pastikan guru memang mengajar kelas & mapel ini
        $jadwal = Jadwal::with(['kelas', 'mapel'])
            ->where('guru_id', $guru->id)
            ->where('kelas_id', $kelas->id)
            ->where('mapel_id', $mapel->id)
            ->firstOrFail();

        $jadwalIds = Jadwal::where('guru_id', $guru->id)
            ->where('kelas_id', $kelas->id)
            ->where('mapel_id', $mapel->id)
            ->pluck('id');

        // Tampilkan semua pertemuan (tanpa filter periode)
        $sesiList = SesiAbsensi::with('detailAbsensi')
            ->whereIn('jadwal_id', $jadwalIds)
            ->orderBy('tanggal', 'asc')
            ->get();

        // Ambil daftar siswa di kelas ini
        $siswaList = Siswa::with('user')
            ->where('kelas_id', $kelas->id)
            ->whereNull('deleted_at')
            ->get()
            ->sortBy('user.name');

        return view('guru.riwayat_detail', [
            'jadwal' => $jadwal,
            'kelas' => $kelas,
            'mapel' => $mapel,
            'sesiList' => $sesiList,
            'siswaList' => $siswaList,
        ]);
    }

    /**
     * Mengunduh rekap absensi untuk guru dalam format Excel.
     */
    public function export(ExportLaporanRequest $request)
    {
        return $this->laporanService->exportExcel($request->user(), $request->validated());
    }

    /**
     * Mengunduh rekap absensi untuk guru dalam format PDF.
     */
    public function exportPdf(ExportLaporanRequest $request)
    {
        return $this->laporanService->exportPdf($request->user(), $request->validated());
    }
}
