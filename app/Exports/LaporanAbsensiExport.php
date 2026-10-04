<?php

namespace App\Exports;

use App\Models\Jadwal;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Export;

class LaporanAbsensiExport implements WithMultipleSheets, Export
{
    use Exportable;

    protected array $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        $sheets = [];

        $jadwalQuery = Jadwal::with(['kelas', 'mapel'])
            ->select('kelas_id', 'mapel_id', 'guru_id')
            ->distinct();

        // Terapkan filter dari form Admin
        if (!empty($this->filters['guru_id'])) {
            $jadwalQuery->where('guru_id', $this->filters['guru_id']);
        }
        if (!empty($this->filters['kelas_id'])) {
            $jadwalQuery->where('kelas_id', $this->filters['kelas_id']);
        }
        if (!empty($this->filters['mapel_id'])) {
            $jadwalQuery->where('mapel_id', $this->filters['mapel_id']);
        }

        $kombinasi = $jadwalQuery->get();

        foreach ($kombinasi as $item) {
            $sheets[] = new LaporanAbsensiPerKelasSheet(
                $item->guru_id, 
                $item->kelas_id, 
                $item->mapel_id, 
                $this->filters
            );
        }

        // Jika tidak ada data, render 1 sheet kosong agar proses download tidak error
        if (count($sheets) === 0) {
            $guruId = $this->filters['guru_id'] ?? 0;
            $sheets[] = new LaporanAbsensiPerKelasSheet($guruId, 0, 0, $this->filters);
        }

        return $sheets;
    }
}
