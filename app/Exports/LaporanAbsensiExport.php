<?php

namespace App\Exports;

use App\Models\Jadwal;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanAbsensiExport implements Export, WithMultipleSheets
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
            ->select('kelas_id', 'mapel_id')
            ->distinct();

        // Terapkan filter dari form Admin
        if (! empty($this->filters['guru_id'])) {
            $jadwalQuery->where('guru_id', $this->filters['guru_id']);
        }
        if (! empty($this->filters['kelas_id'])) {
            $jadwalQuery->where('kelas_id', $this->filters['kelas_id']);
        }
        if (! empty($this->filters['mapel_id'])) {
            $jadwalQuery->where('mapel_id', $this->filters['mapel_id']);
        }
        if (! empty($this->filters['tahun_ajaran'])) {
            $jadwalQuery->where('tahun_ajaran', $this->filters['tahun_ajaran']);
        }
        if (! empty($this->filters['semester'])) {
            $jadwalQuery->where('semester', $this->filters['semester']);
        }

        $kombinasi = $jadwalQuery->get();
        $usedTitles = [];

        foreach ($kombinasi as $item) {
            $guruId = $this->filters['guru_id'] ?? null;

            // Generate clean sheet title
            $kelasNama = $item->kelas ? $item->kelas->nama : 'Kelas';
            $mapelNama = $item->mapel ? $item->mapel->nama : 'Mapel';
            $abjadMapel = preg_replace('/[^A-Z]/', '', strtoupper($mapelNama));
            if (empty($abjadMapel)) {
                $abjadMapel = substr(strtoupper($mapelNama), 0, 3);
            }

            $rawTitle = $kelasNama.' - '.$abjadMapel;
            $rawTitle = str_replace(['*', ':', '?', '[', ']', '/', '\\'], '', $rawTitle);
            $cleanTitle = substr($rawTitle, 0, 31);

            $finalTitle = $cleanTitle;
            $counter = 1;
            while (isset($usedTitles[strtolower($finalTitle)])) {
                $counter++;
                $suffix = ' ('.$counter.')';
                $finalTitle = substr($cleanTitle, 0, 31 - strlen($suffix)).$suffix;
            }
            $usedTitles[strtolower($finalTitle)] = true;

            $sheets[] = new LaporanAbsensiPerKelasSheet(
                $guruId,
                $item->kelas_id,
                $item->mapel_id,
                $this->filters,
                $finalTitle
            );
        }

        // Jika tidak ada data, render 1 sheet kosong agar proses download tidak error
        if (count($sheets) === 0) {
            $guruId = $this->filters['guru_id'] ?? 0;
            $sheets[] = new LaporanAbsensiPerKelasSheet($guruId, 0, 0, $this->filters, 'Data Kosong');
        }

        return $sheets;
    }
}
