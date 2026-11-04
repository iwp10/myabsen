<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AktifkanSemesterRequest;
use App\Http\Requests\Admin\PergantianSemesterRequest;
use App\Services\PergantianSemesterService;
use App\Services\PeriodeService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PergantianSemesterController extends Controller
{
    public function __construct(
        protected PergantianSemesterService $pergantianSemesterService,
        protected PeriodeService $periodeService
    ) {}

    /**
     * Menampilkan halaman formulir dan pratinjau Pergantian Semester Otomatis.
     */
    public function index(Request $request): View
    {
        $activePeriode = $this->periodeService->getActivePeriode();
        $daftarTahunAjaran = $this->pergantianSemesterService->getDaftarTahunAjaranGanjil();

        // Fallback jika belum ada kelas ganjil di DB, ambil dari pilihan tahun ajaran periode
        if (empty($daftarTahunAjaran)) {
            $daftarTahunAjaran = $this->periodeService->getDaftarPilihanTahunAjaran();
        }

        // Tentukan tahun ajaran terpilih
        $tahunAjaranTerpilih = $request->query('tahun_ajaran', $request->query('asal'));
        if (! $tahunAjaranTerpilih) {
            $tahunAjaranTerpilih = ($activePeriode['semester'] === 'Ganjil')
                ? $activePeriode['tahun_ajaran']
                : ($daftarTahunAjaran[0] ?? $activePeriode['tahun_ajaran']);
        }

        // Ambil opsi tindakan (bawaan true jika baru pertama dibuka)
        $adaQuery = $request->has('tahun_ajaran') || $request->has('asal');
        $salinKelas = $adaQuery ? $request->boolean('salin_kelas', false) : true;
        $pindahSiswa = $adaQuery ? $request->boolean('pindah_siswa', false) : true;
        $salinJadwal = $adaQuery ? $request->boolean('salin_jadwal', false) : true;

        $opsi = [
            'salin_kelas' => $salinKelas,
            'pindah_siswa' => $pindahSiswa,
            'salin_jadwal' => $salinJadwal,
        ];

        $pratinjau = null;
        $errorPesan = null;

        // Validasi jika ada percobaan memilih semester asal selain Ganjil
        $semesterAsalParam = $request->query('semester_asal');
        if ($semesterAsalParam && strtolower($semesterAsalParam) !== 'ganjil') {
            $errorPesan = 'Pergantian otomatis hanya untuk Ganjil ke Genap pada tahun ajaran yang sama. Untuk tahun ajaran baru, gunakan tab Salin kelas, Pindahkan siswa, dan Luluskan siswa.';
        } elseif ($adaQuery && ! empty($tahunAjaranTerpilih)) {
            try {
                $pratinjau = $this->pergantianSemesterService->pratinjau($tahunAjaranTerpilih, $opsi);
            } catch (Exception $e) {
                $errorPesan = $e->getMessage();
            }
        }

        return view('admin.pergantian_semester.index', compact(
            'activePeriode',
            'daftarTahunAjaran',
            'tahunAjaranTerpilih',
            'opsi',
            'pratinjau',
            'errorPesan'
        ));
    }

    /**
     * Menjalankan eksekusi pergantian semester otomatis.
     */
    public function jalankan(PergantianSemesterRequest $request): RedirectResponse
    {
        try {
            $tahunAjaran = $request->validated('tahun_ajaran');
            $opsi = [
                'salin_kelas' => $request->boolean('salin_kelas', false),
                'pindah_siswa' => $request->boolean('pindah_siswa', false),
                'salin_jadwal' => $request->boolean('salin_jadwal', false),
            ];

            $hasil = $this->pergantianSemesterService->jalankan($tahunAjaran, $opsi, (int) $request->user()->id);

            return redirect()->route('admin.pergantian-semester.index', [
                'tahun_ajaran' => $tahunAjaran,
                'salin_kelas' => $opsi['salin_kelas'] ? 1 : 0,
                'pindah_siswa' => $opsi['pindah_siswa'] ? 1 : 0,
                'salin_jadwal' => $opsi['salin_jadwal'] ? 1 : 0,
            ])
                ->with('hasil_eksekusi', $hasil)
                ->with('success', "Pergantian semester otomatis selesai: {$hasil['kelas_berhasil']} kelas berhasil diproses, {$hasil['siswa_dipindah']} siswa dipindahkan, dan {$hasil['jadwal_disalin']} jadwal disalin.");
        } catch (Exception $e) {
            return redirect()->route('admin.pergantian-semester.index', [
                'tahun_ajaran' => $request->input('tahun_ajaran'),
            ])->with('error', $e->getMessage());
        }
    }

    /**
     * Mengaktifkan periode semester Genap secara terpisah.
     */
    public function aktifkan(AktifkanSemesterRequest $request): RedirectResponse
    {
        try {
            $tahunAjaran = $request->validated('tahun_ajaran');
            $info = $this->pergantianSemesterService->aktifkanPeriodeTujuan($tahunAjaran, (int) $request->user()->id);

            $pesanSukses = "Periode akademik aktif berhasil diubah menjadi {$info['semester']} {$info['tahun_ajaran']}.";

            $redirect = redirect()->route('admin.pergantian-semester.index', [
                'tahun_ajaran' => $tahunAjaran,
            ])->with('success', $pesanSukses);

            if ($info['tanpa_jadwal']) {
                $redirect->with('warning', "Periode aktif berhasil diubah ke {$info['semester']} {$info['tahun_ajaran']}. PERINGATAN: Periode tujuan belum punya jadwal sama sekali; dashboard guru dan siswa saat ini akan kosong.");
            }

            return $redirect;
        } catch (Exception $e) {
            return redirect()->route('admin.pergantian-semester.index', [
                'tahun_ajaran' => $request->input('tahun_ajaran'),
            ])->with('error', 'Gagal mengaktifkan periode: '.$e->getMessage());
        }
    }
}
