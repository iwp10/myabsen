<?php

namespace App\Exports;

use App\Models\Guru;
use App\Models\Jadwal;
use Illuminate\Support\Facades\DB;
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
        if (! empty($this->filters['jurusan_id'])) {
            $jadwalQuery->whereHas('kelas', fn ($q) => $q->where('jurusan_id', $this->filters['jurusan_id']));
        }
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

        // Pre-fetch nama guru dalam 1 query untuk menghindari N+1 per sheet
        $guruId = $this->filters['guru_id'] ?? null;
        $defaultGuruNama = null;
        $guruNamesByPair = [];

        if (! empty($guruId)) {
            $guru = Guru::withTrashed()->with('user')->find($guruId);
            $defaultGuruNama = $guru && $guru->user ? $guru->user->name : '-';
        } else {
            $guruQuery = DB::table('jadwal')
                ->join('guru', 'jadwal.guru_id', '=', 'guru.id')
                ->join('users', 'guru.user_id', '=', 'users.id');

            if (! empty($this->filters['tahun_ajaran'])) {
                $guruQuery->where('jadwal.tahun_ajaran', $this->filters['tahun_ajaran']);
            }
            if (! empty($this->filters['semester'])) {
                $guruQuery->where('jadwal.semester', $this->filters['semester']);
            }
            if (! empty($this->filters['jurusan_id'])) {
                $guruQuery->join('kelas as k', 'jadwal.kelas_id', '=', 'k.id')
                    ->where('k.jurusan_id', $this->filters['jurusan_id']);
            }

            $guruRows = $guruQuery->select('jadwal.kelas_id', 'jadwal.mapel_id', 'users.name')
                ->distinct()
                ->get();

            foreach ($guruRows as $row) {
                $guruNamesByPair[$row->kelas_id.'-'.$row->mapel_id][] = $row->name;
            }
        }

        foreach ($kombinasi as $item) {
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

            $guruNama = $defaultGuruNama ?? (isset($guruNamesByPair[$item->kelas_id.'-'.$item->mapel_id])
                ? implode(', ', $guruNamesByPair[$item->kelas_id.'-'.$item->mapel_id])
                : '-');

            $sheets[] = new LaporanAbsensiPerKelasSheet(
                $guruId,
                $item->kelas_id,
                $item->mapel_id,
                $this->filters,
                $finalTitle,
                $kelasNama,
                $mapelNama,
                $guruNama
            );
        }

        // Jika tidak ada data, render 1 sheet kosong agar proses download tidak error
        if (count($sheets) === 0) {
            $fallbackGuruId = $this->filters['guru_id'] ?? 0;
            $pesanKosong = 'Belum ada jadwal mengajar.';
            if (! empty($this->filters['kelas_id']) && ! empty($this->filters['jurusan_id'])) {
                $pesanKosong = 'Kelas yang dipilih tidak sesuai dengan jurusan yang dipilih.';
            }

            $sheets[] = new LaporanAbsensiPerKelasSheet(
                $fallbackGuruId,
                0,
                0,
                $this->filters,
                'Data Kosong',
                null,
                null,
                null,
                $pesanKosong
            );
        }

        return $sheets;
    }
}
