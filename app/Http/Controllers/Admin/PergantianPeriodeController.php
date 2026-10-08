<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LuluskanSiswaRequest;
use App\Http\Requests\Admin\PindahkanSiswaRequest;
use App\Http\Requests\Admin\SalinKelasRequest;
use App\Models\Kelas;
use App\Services\PergantianPeriodeService;
use App\Services\PeriodeService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PergantianPeriodeController extends Controller
{
    public function __construct(
        protected PergantianPeriodeService $pergantianPeriodeService,
        protected PeriodeService $periodeService
    ) {}

    /**
     * Menampilkan halaman Pergantian Periode.
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'panduan');
        $activePeriode = $this->periodeService->getActivePeriode();
        $daftarPeriodeAsal = $this->pergantianPeriodeService->getDaftarPeriodeAsalKelas();
        $daftarTahunAjaran = $this->periodeService->getDaftarPilihanTahunAjaran();
        $daftarKelasAktif = $this->pergantianPeriodeService->getDaftarKelasAktif();

        // Data untuk Tab Salin Kelas
        $periodeAsal = $request->query('periode_asal');
        if (! $periodeAsal && ! empty($daftarPeriodeAsal)) {
            $periodeAsal = $daftarPeriodeAsal[0]['value'];
        }

        $kelasPeriodeAsal = collect();
        if ($periodeAsal && str_contains($periodeAsal, '-')) {
            [$tahunAjaranAsal, $semesterAsal] = explode('-', $periodeAsal, 2);
            $kelasPeriodeAsal = $this->pergantianPeriodeService->getKelasByPeriode($tahunAjaranAsal, $semesterAsal);
        }

        // Data untuk Tab Pindahkan Siswa
        $kelasAsalId = $request->query('kelas_asal_id');
        $kelasTujuanId = $request->query('kelas_tujuan_id');
        $siswaKelasAsal = collect();
        if ($kelasAsalId) {
            $siswaKelasAsal = $this->pergantianPeriodeService->getSiswaAktifByKelas((int) $kelasAsalId);
        }

        // Data untuk Tab Luluskan Siswa
        $kelasLulusId = $request->query('kelas_id');
        $siswaLulus = collect();
        if ($kelasLulusId) {
            $siswaLulus = $this->pergantianPeriodeService->getSiswaAktifByKelas((int) $kelasLulusId);
        }

        return view('admin.pergantian_periode.index', compact(
            'tab',
            'activePeriode',
            'daftarPeriodeAsal',
            'daftarTahunAjaran',
            'daftarKelasAktif',
            'periodeAsal',
            'kelasPeriodeAsal',
            'kelasAsalId',
            'kelasTujuanId',
            'siswaKelasAsal',
            'kelasLulusId',
            'siswaLulus'
        ));
    }

    /**
     * Memproses salin kelas ke periode baru.
     */
    public function salinKelas(SalinKelasRequest $request): RedirectResponse
    {
        try {
            [$tahunAjaranAsal, $semesterAsal] = explode('-', $request->validated('periode_asal'), 2);

            $hasil = $this->pergantianPeriodeService->salinKelas(
                $tahunAjaranAsal,
                $semesterAsal,
                $request->validated('tahun_ajaran_tujuan'),
                $request->validated('semester_tujuan'),
                $request->validated('kelas_ids')
            );

            return redirect()->route('admin.pergantian-periode.index', [
                'tab' => 'salin',
                'periode_asal' => $request->validated('periode_asal'),
            ])->with('success', "{$hasil['disalin']} kelas berhasil disalin, {$hasil['dilewati']} kelas dilewati (sudah ada).");
        } catch (Exception $e) {
            return redirect()->route('admin.pergantian-periode.index', [
                'tab' => 'salin',
                'periode_asal' => $request->validated('periode_asal'),
            ])->with('error', $e->getMessage());
        }
    }

    /**
     * Memproses pemindahan siswa dari kelas asal ke kelas tujuan.
     */
    public function pindahkanSiswa(PindahkanSiswaRequest $request): RedirectResponse
    {
        try {
            $kelasTujuan = Kelas::find($request->validated('kelas_tujuan_id'));

            $jumlah = $this->pergantianPeriodeService->pindahkanSiswa(
                (int) $request->validated('kelas_asal_id'),
                (int) $request->validated('kelas_tujuan_id'),
                $request->validated('siswa_ids')
            );

            $namaTujuan = $kelasTujuan?->nama ?? 'tujuan';

            return redirect()->route('admin.pergantian-periode.index', [
                'tab' => 'pindah',
                'kelas_asal_id' => $request->validated('kelas_asal_id'),
            ])->with('success', "{$jumlah} siswa berhasil dipindahkan ke kelas {$namaTujuan}.");
        } catch (Exception $e) {
            return redirect()->route('admin.pergantian-periode.index', [
                'tab' => 'pindah',
                'kelas_asal_id' => $request->validated('kelas_asal_id'),
            ])->with('error', $e->getMessage());
        }
    }

    /**
     * Memproses kelulusan siswa terpilih.
     */
    public function luluskanSiswa(LuluskanSiswaRequest $request): RedirectResponse
    {
        try {
            $jumlah = $this->pergantianPeriodeService->luluskanSiswa(
                (int) $request->validated('kelas_id'),
                $request->validated('siswa_ids')
            );

            return redirect()->route('admin.pergantian-periode.index', [
                'tab' => 'lulus',
                'kelas_id' => $request->validated('kelas_id'),
            ])->with('success', "{$jumlah} siswa berhasil diluluskan (status nonaktif).");
        } catch (Exception $e) {
            return redirect()->route('admin.pergantian-periode.index', [
                'tab' => 'lulus',
                'kelas_id' => $request->validated('kelas_id'),
            ])->with('error', $e->getMessage());
        }
    }
}
