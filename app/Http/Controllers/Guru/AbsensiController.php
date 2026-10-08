<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportLaporanRequest;
use App\Http\Requests\Guru\ShowAbsensiRequest;
use App\Http\Requests\Guru\StoreAbsensiRequest;
use App\Http\Requests\RiwayatGuruFilterRequest;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Services\AbsensiService;
use App\Services\LaporanService;
use App\Services\PeriodeService;
use App\Support\KelasMapel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

class AbsensiController extends Controller
{
    public function __construct(
        protected AbsensiService $absensiService,
        protected LaporanService $laporanService,
        protected PeriodeService $periodeService
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
    public function riwayat(RiwayatGuruFilterRequest $request)
    {
        $guru = Guru::where('user_id', $request->user()->id)->first();
        if (! $guru) {
            return view('guru.riwayat', [
                'jadwalList' => collect(),
                'activePeriode' => $this->absensiService->getActivePeriode(),
                'daftarPeriode' => [],
                'selectedPeriode' => $this->absensiService->getActivePeriode(),
                'filterPeriode' => '',
                'search' => '',
            ]);
        }

        $activePeriode = $this->absensiService->getActivePeriode();
        $daftarPeriode = $this->periodeService->getDaftarPeriodeGuru($guru);

        $validated = $request->validated();
        $tahunAjaran = $validated['tahun_ajaran'] ?? null;
        $semester = $validated['semester'] ?? null;

        $selectedPeriode = [
            'tahun_ajaran' => $tahunAjaran ?: $activePeriode['tahun_ajaran'],
            'semester' => $semester ?: $activePeriode['semester'],
        ];

        $filterPeriodeValue = $selectedPeriode['tahun_ajaran'].'|'.$selectedPeriode['semester'];
        $search = trim((string) ($validated['q'] ?? ''));

        // Ambil semua jadwal milik guru pada periode terpilih, unik per kelas+mapel
        $query = Jadwal::with(['kelas', 'mapel'])
            ->where('guru_id', $guru->id)
            ->where('tahun_ajaran', $selectedPeriode['tahun_ajaran'])
            ->where('semester', $selectedPeriode['semester']);

        if ($search !== '') {
            $escaped = addcslashes($search, '%_');
            $query->where(function ($q) use ($escaped) {
                $q->whereHas('kelas', fn ($k) => $k->where('nama', 'like', "%{$escaped}%"))
                    ->orWhereHas('mapel', fn ($m) => $m->where('nama', 'like', "%{$escaped}%"));
            });
        }

        $jadwalList = $query->get()
            ->unique(fn ($j) => KelasMapel::make($j->kelas_id, $j->mapel_id))
            ->values();

        return view('guru.riwayat', [
            'jadwalList' => $jadwalList,
            'activePeriode' => $activePeriode,
            'daftarPeriode' => $daftarPeriode,
            'selectedPeriode' => $selectedPeriode,
            'filterPeriode' => $filterPeriodeValue,
            'search' => $search,
        ]);
    }

    /**
     * Menampilkan riwayat absensi untuk satu kelas & mapel tertentu.
     */
    public function riwayatDetail(RiwayatGuruFilterRequest $request, Kelas $kelas, Mapel $mapel)
    {
        $activePeriode = $this->absensiService->getActivePeriode();
        $guru = $request->user()->guru;
        $daftarPeriode = $guru ? $this->periodeService->getDaftarPeriodeGuru($guru) : [];

        $validated = $request->validated();
        $tahunAjaran = $validated['tahun_ajaran'] ?? null;
        $semester = $validated['semester'] ?? null;

        $selectedPeriode = [
            'tahun_ajaran' => $tahunAjaran ?: $activePeriode['tahun_ajaran'],
            'semester' => $semester ?: $activePeriode['semester'],
        ];

        $filterPeriodeValue = $selectedPeriode['tahun_ajaran'].'|'.$selectedPeriode['semester'];
        $search = trim((string) ($validated['q'] ?? ''));

        // Cari jadwal milik guru yang login pada kelas-mapel & periode terpilih
        $jadwal = Jadwal::with(['kelas', 'mapel'])
            ->where('guru_id', $guru?->id)
            ->where('kelas_id', $kelas->id)
            ->where('mapel_id', $mapel->id)
            ->where('tahun_ajaran', $selectedPeriode['tahun_ajaran'])
            ->where('semester', $selectedPeriode['semester'])
            ->first();

        // Jika tidak ada jadwal miliknya, cari jadwal kelas-mapel lain pada periode terpilih (atau 404 jika sama sekali tidak ada)
        // agar JadwalPolicy memeriksa dan menolak dengan 403 Forbidden
        if (! $jadwal) {
            $jadwalLain = Jadwal::where('kelas_id', $kelas->id)
                ->where('mapel_id', $mapel->id)
                ->where('tahun_ajaran', $selectedPeriode['tahun_ajaran'])
                ->where('semester', $selectedPeriode['semester'])
                ->first();

            if ($jadwalLain) {
                Gate::authorize('viewRiwayat', $jadwalLain);
            }

            abort(404);
        }

        Gate::authorize('viewRiwayat', $jadwal);

        $jadwalIds = Jadwal::where('guru_id', $guru->id)
            ->where('kelas_id', $kelas->id)
            ->where('mapel_id', $mapel->id)
            ->where('tahun_ajaran', $selectedPeriode['tahun_ajaran'])
            ->where('semester', $selectedPeriode['semester'])
            ->pluck('id');

        // Tampilkan pertemuan pada periode terpilih
        $sesiList = SesiAbsensi::with('detailAbsensi')
            ->whereIn('jadwal_id', $jadwalIds)
            ->orderBy('tanggal', 'asc')
            ->get();

        // Ambil daftar siswa di kelas ini (aktif + nonaktif yang memiliki riwayat sesi)
        $siswaList = $this->absensiService->getSiswaUntukLaporan($kelas->id, $sesiList->pluck('id'));

        $totalSiswaSebelumFilter = $siswaList->count();
        if ($search !== '') {
            $kwLower = mb_strtolower($search);
            $siswaList = $siswaList->filter(function ($siswa) use ($kwLower) {
                $nama = mb_strtolower($siswa->nama ?? $siswa->user?->name ?? '');
                $nis = mb_strtolower($siswa->nis ?? '');

                return str_contains($nama, $kwLower) || str_contains($nis, $kwLower);
            })->values();
        }

        return view('guru.riwayat_detail', [
            'jadwal' => $jadwal,
            'kelas' => $kelas,
            'mapel' => $mapel,
            'sesiList' => $sesiList,
            'siswaList' => $siswaList,
            'daftarPeriode' => $daftarPeriode,
            'selectedPeriode' => $selectedPeriode,
            'filterPeriode' => $filterPeriodeValue,
            'search' => $search,
            'totalSiswaSebelumFilter' => $totalSiswaSebelumFilter,
        ]);
    }

    /**
     * Mengunduh rekap absensi untuk guru dalam format Excel.
     */
    public function export(ExportLaporanRequest $request)
    {
        return $this->laporanService->exportExcel($request->user(), $request->validated());
    }
}
