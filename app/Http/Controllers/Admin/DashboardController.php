<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Services\PeriodeService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected PeriodeService $periodeService
    ) {}

    /**
     * Tampilkan halaman dashboard utama admin.
     */
    public function index(): View
    {
        $total_siswa = Siswa::count();
        $total_guru = Guru::count();
        $total_kelas = Kelas::count();
        $total_mapel = Mapel::count();

        $activePeriode = $this->periodeService->getActivePeriode();
        $saranPeriode = $this->periodeService->getSaranPeriode();
        $isBerbedaDenganSaran = $this->periodeService->isPeriodeBerbedaDenganSaran();

        return view('admin.dashboard', compact(
            'total_siswa',
            'total_guru',
            'total_kelas',
            'total_mapel',
            'activePeriode',
            'saranPeriode',
            'isBerbedaDenganSaran'
        ));
    }
}
