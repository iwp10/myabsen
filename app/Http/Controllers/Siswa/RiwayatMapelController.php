<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\RiwayatMapelDetailRequest;
use App\Models\Mapel;
use App\Services\AbsensiService;

class RiwayatMapelController extends Controller
{
    public function __construct(
        protected AbsensiService $absensiService
    ) {}

    public function detail(RiwayatMapelDetailRequest $request, Mapel $mapel)
    {
        $siswa = $request->user()->siswa;
        if (! $siswa) {
            abort(404);
        }

        $activePeriode = $this->absensiService->getActivePeriode();
        $validated = $request->validated();
        $tahunAjaran = $validated['tahun_ajaran'] ?? $activePeriode['tahun_ajaran'];
        $semester = $validated['semester'] ?? $activePeriode['semester'];

        if (! $tahunAjaran || ! $semester) {
            abort(404);
        }

        $detail = $this->absensiService->getDetailRiwayatMapelSiswa(
            $siswa->id,
            $mapel->id,
            $tahunAjaran,
            $semester
        );

        if (! $detail) {
            abort(404);
        }

        $selectedPeriode = [
            'tahun_ajaran' => $tahunAjaran,
            'semester' => $semester,
        ];
        $filterPeriodeValue = $tahunAjaran.'|'.$semester;

        return view('siswa.riwayat_mapel', array_merge($detail, [
            'siswa' => $siswa,
            'selectedPeriode' => $selectedPeriode,
            'filterPeriodeValue' => $filterPeriodeValue,
            'activePeriode' => $activePeriode,
        ]));
    }
}
