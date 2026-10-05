<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePeriodeRequest;
use App\Services\PeriodeService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PengaturanPeriodeController extends Controller
{
    public function __construct(
        protected PeriodeService $periodeService
    ) {}

    /**
     * Menampilkan halaman Pengaturan Periode Aktif.
     */
    public function index(): View
    {
        $activePeriode = $this->periodeService->getActivePeriode();
        $saran = $this->periodeService->getSaranPeriode();
        $daftarTahunAjaran = $this->periodeService->getDaftarPilihanTahunAjaran();
        $isBerbeda = $this->periodeService->isPeriodeBerbedaDenganSaran();
        $tanggalHariIni = Carbon::now('Asia/Jakarta');

        return view('admin.pengaturan_periode.index', compact(
            'activePeriode',
            'saran',
            'daftarTahunAjaran',
            'isBerbeda',
            'tanggalHariIni'
        ));
    }

    /**
     * Memperbarui pengaturan periode aktif.
     */
    public function update(UpdatePeriodeRequest $request): RedirectResponse
    {
        $this->periodeService->setPeriodeAktif(
            $request->validated('tahun_ajaran'),
            $request->validated('semester')
        );

        return redirect()->route('admin.pengaturan-periode.index')
            ->with('status', 'Periode aktif berhasil diperbarui.');
    }
}
