<?php

namespace App\Services;

use App\Exports\LaporanAbsensiExport;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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
        $filters = [
            'kelas_id' => $validatedFilters['kelas_id'] ?? null,
            'mapel_id' => $validatedFilters['mapel_id'] ?? null,
            'bulan' => $validatedFilters['bulan'] ?? null,
        ];

        if (! empty($validatedFilters['kelas_mapel'])) {
            $parts = explode('-', $validatedFilters['kelas_mapel']);
            if (count($parts) === 2) {
                $filters['kelas_id'] = $filters['kelas_id'] ?? $parts[0];
                $filters['mapel_id'] = $filters['mapel_id'] ?? $parts[1];
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
    public function generateFilename(User $user, array $filters, string $extension): string
    {
        $prefix = $user->role === 'guru' ? 'rekap_absensi_guru' : 'rekap_absensi';

        $periode = ! empty($filters['bulan']) ? $filters['bulan'] : date('Y-m');

        return "{$prefix}_{$periode}.{$extension}";
    }

    /**
     * Mengekspor laporan rekap absensi ke format Excel (.xlsx).
     */
    public function exportExcel(User $user, array $validatedFilters): BinaryFileResponse
    {
        $filters = $this->prepareFilters($user, $validatedFilters);
        $filename = $this->generateFilename($user, $filters, 'xlsx');

        return Excel::download(new LaporanAbsensiExport($filters), $filename);
    }

    /**
     * Mengekspor laporan rekap absensi ke format PDF (.pdf).
     */
    public function exportPdf(User $user, array $validatedFilters): Response
    {
        $filters = $this->prepareFilters($user, $validatedFilters);
        $filename = $this->generateFilename($user, $filters, 'pdf');

        $data = $this->absensiService->getRekapLaporan($filters);

        $periode = ! empty($filters['bulan'])
            ? Carbon::createFromFormat('Y-m', $filters['bulan'])->translatedFormat('F Y')
            : 'Semua Periode';

        // Ambil nama entitas sebagai variabel (tanpa query langsung di Blade PDF)
        $namaKelas = ! empty($filters['kelas_id']) ? Kelas::find($filters['kelas_id'])?->nama : null;
        $namaMapel = ! empty($filters['mapel_id']) ? Mapel::find($filters['mapel_id'])?->nama : null;
        $namaGuru = ! empty($filters['guru_id']) ? Guru::with('user')->find($filters['guru_id'])?->user?->name : null;

        $pdf = Pdf::loadView('laporan.pdf', [
            'data' => $data,
            'filters' => $filters,
            'periode' => $periode,
            'namaKelas' => $namaKelas,
            'namaMapel' => $namaMapel,
            'namaGuru' => $namaGuru,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }
}
