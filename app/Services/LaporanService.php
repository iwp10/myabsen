<?php

namespace App\Services;

use App\Exports\LaporanAbsensiExport;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class LaporanService
{
    public function __construct(
        protected AbsensiService $absensiService
    ) {}

    /**
     * Mempersiapkan dan menormalisasi filter berdasarkan role pengguna.
     */
    public function prepareFilters(User $user, array $validatedFilters): array
    {
        $activePeriode = $this->absensiService->getActivePeriode();

        $filters = [
            'kelas_id' => $validatedFilters['kelas_id'] ?? null,
            'mapel_id' => $validatedFilters['mapel_id'] ?? null,
            'tahun_ajaran' => ! empty($validatedFilters['tahun_ajaran']) ? $validatedFilters['tahun_ajaran'] : $activePeriode['tahun_ajaran'],
            'semester' => ! empty($validatedFilters['semester']) ? $validatedFilters['semester'] : $activePeriode['semester'],
            'tanggal_awal' => $validatedFilters['tanggal_awal'] ?? null,
            'tanggal_akhir' => $validatedFilters['tanggal_akhir'] ?? null,
        ];

        if (! empty($validatedFilters['kelas_mapel'])) {
            $parts = explode('-', $validatedFilters['kelas_mapel']);
            if (count($parts) === 2) {
                $filters['kelas_id'] = $parts[0];
                $filters['mapel_id'] = $parts[1];
            }
        }

        // Jika guru, otomatis batasi data absensi hanya untuk jadwal guru tersebut (AB-08)
        if ($user->role === 'guru') {
            $guru = $user->guru;
            $filters['guru_id'] = $guru ? $guru->id : null;
        }

        return array_filter($filters, fn ($val) => $val !== null);
    }

    /**
     * Membentuk nama file ekspor yang konsisten untuk Excel maupun PDF.
     */
    public function generateFilename(User $user, array $validatedFilters, string $extension): string
    {
        $prefix = $user->role === 'guru' ? 'rekap_absensi_guru' : 'rekap_absensi';

        $periode = date('Y-m-d');
        if (! empty($validatedFilters['tahun_ajaran'])) {
            $ta = str_replace('/', '-', $validatedFilters['tahun_ajaran']);
            $periode = $ta.(! empty($validatedFilters['semester']) ? '_'.$validatedFilters['semester'] : '');
        }

        return "{$prefix}_{$periode}.{$extension}";
    }

    /**
     * Mengekspor laporan rekap absensi ke format Excel (.xlsx).
     */
    public function exportExcel(User $user, array $validatedFilters): BinaryFileResponse
    {
        $filters = $this->prepareFilters($user, $validatedFilters);
        $filename = $this->generateFilename($user, $validatedFilters, 'xlsx');

        return Excel::download(new LaporanAbsensiExport($filters), $filename);
    }

    /**
     * Mengekspor laporan rekap absensi ke format PDF (.pdf).
     */
    public function exportPdf(User $user, array $validatedFilters): Response
    {
        $filters = $this->prepareFilters($user, $validatedFilters);
        $filename = $this->generateFilename($user, $validatedFilters, 'pdf');

        $data = $this->absensiService->getRekapLaporan($filters);

        $periodeText = 'Semua Periode';
        if (! empty($filters['tahun_ajaran'])) {
            $periodeText = 'TA '.$filters['tahun_ajaran'];
            if (! empty($filters['semester'])) {
                $periodeText .= ' Semester '.$filters['semester'];
            }
        }

        // Ambil nama entitas sebagai variabel (tanpa query langsung di Blade PDF)
        $namaKelas = ! empty($filters['kelas_id']) ? Kelas::find($filters['kelas_id'])?->nama : null;
        $namaMapel = ! empty($filters['mapel_id']) ? Mapel::find($filters['mapel_id'])?->nama : null;
        $namaGuru = ! empty($filters['guru_id']) ? Guru::with('user')->find($filters['guru_id'])?->user?->name : null;

        $pdf = Pdf::loadView('laporan.pdf', [
            'data' => $data,
            'filters' => $filters,
            'periode' => $periodeText,
            'namaKelas' => $namaKelas,
            'namaMapel' => $namaMapel,
            'namaGuru' => $namaGuru,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }
}
