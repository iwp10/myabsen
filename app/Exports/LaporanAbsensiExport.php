<?php

namespace App\Exports;

use App\Services\AbsensiService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LaporanAbsensiExport implements FromCollection, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $absensiService = app(AbsensiService::class);

        return collect($absensiService->getRekapLaporan($this->filters));
    }

    public function headings(): array
    {
        return [
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Mata Pelajaran',
            'Hadir',
            'Izin',
            'Sakit',
            'Alpa',
            'Total Sesi',
            'Persentase Kehadiran (%)',
        ];
    }

    public function map($row): array
    {
        $totalSesi = $row->total_sesi > 0 ? $row->total_sesi : 1; // avoid division by zero
        $persentase = round(($row->hadir / $totalSesi) * 100, 2);

        return [
            $row->nis,
            $row->nama_siswa,
            $row->nama_kelas,
            $row->nama_mapel,
            $row->hadir,
            $row->izin,
            $row->sakit,
            $row->alpa,
            $row->total_sesi,
            $persentase,
        ];
    }
}
