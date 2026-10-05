<?php

namespace App\Exports;

use App\Models\Jadwal;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanAbsensiGuruExport implements Export, WithMultipleSheets
{
    use Exportable;

    protected $guruId;

    protected $filters;

    public function __construct($guruId, $filters)
    {
        $this->guruId = $guruId;
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        $sheets = [];

        $jadwalQuery = Jadwal::with(['kelas', 'mapel'])
            ->where('guru_id', $this->guruId)
            ->select('kelas_id', 'mapel_id')
            ->distinct();

        if (! empty($this->filters['kelas_mapel'])) {
            $parts = explode('-', $this->filters['kelas_mapel']);
            if (count($parts) === 2) {
                $jadwalQuery->where('kelas_id', $parts[0])->where('mapel_id', $parts[1]);
            }
        }

        $kombinasi = $jadwalQuery->get();

        foreach ($kombinasi as $item) {
            $sheets[] = new LaporanAbsensiPerKelasSheet($this->guruId, $item->kelas_id, $item->mapel_id, $this->filters);
        }

        // Jika tidak ada data, kita tambahkan sheet kosong dengan keterangan agar excel tidak error.
        if (count($sheets) === 0) {
            $sheets[] = new LaporanAbsensiPerKelasSheet($this->guruId, 0, 0, $this->filters);
        }

        return $sheets;
    }
}
