<?php

namespace App\Services;

use App\Models\DetailAbsensi;
use Illuminate\Support\Facades\DB;

class PertemuanService
{
    /**
     * Menghitung nomor urut pertemuan (1-based: 1, 2, 3...) untuk kumpulan detail absensi.
     *
     * Urutan pertemuan konsisten dengan definisi matriks kehadiran guru:
     * - Dikelompokkan per kelas_id, mapel_id, tahun_ajaran, dan semester.
     * - Diurutkan kronologis berdasarkan tanggal ASC, lalu jam_mulai jadwal ASC, lalu sesi_absensi.id ASC.
     * - Dihitung terhadap SELURUH sesi pada kelas + mapel periode tersebut (tidak terpengaruh filter halaman atau status).
     * - Efisien tanpa N+1 query.
     *
     * @param  iterable<DetailAbsensi>  $detailAbsensiItems
     * @return array<int, int|null> Mapping [detail_absensi_id => nomor_pertemuan]
     */
    public function hitungNomorPertemuan(iterable $detailAbsensiItems): array
    {
        $items = collect($detailAbsensiItems);
        if ($items->isEmpty()) {
            return [];
        }

        // Kumpulkan grup unik (kelas_id, mapel_id, tahun_ajaran, semester)
        $groups = [];
        foreach ($items as $item) {
            $sesi = $item->sesiAbsensi;
            $jadwal = $sesi?->jadwal;

            if (! $jadwal || ! $jadwal->kelas_id || ! $jadwal->mapel_id || ! $jadwal->tahun_ajaran || ! $jadwal->semester) {
                continue;
            }

            $groupKey = implode('|', [
                $jadwal->kelas_id,
                $jadwal->mapel_id,
                $jadwal->tahun_ajaran,
                $jadwal->semester,
            ]);

            if (! isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'kelas_id' => $jadwal->kelas_id,
                    'mapel_id' => $jadwal->mapel_id,
                    'tahun_ajaran' => $jadwal->tahun_ajaran,
                    'semester' => $jadwal->semester,
                ];
            }
        }

        if (empty($groups)) {
            $result = [];
            foreach ($items as $item) {
                $result[$item->id] = null;
            }

            return $result;
        }

        // Query seluruh sesi untuk setiap grup unik (biasanya hanya 1-3 grup pada 1 halaman riwayat)
        $sesiNomorMap = [];
        foreach ($groups as $g) {
            $sesiIds = DB::table('sesi_absensi')
                ->join('jadwal', 'sesi_absensi.jadwal_id', '=', 'jadwal.id')
                ->where('jadwal.kelas_id', $g['kelas_id'])
                ->where('jadwal.mapel_id', $g['mapel_id'])
                ->where('jadwal.tahun_ajaran', $g['tahun_ajaran'])
                ->where('jadwal.semester', $g['semester'])
                ->orderBy('sesi_absensi.tanggal', 'asc')
                ->orderBy('jadwal.jam_mulai', 'asc')
                ->orderBy('sesi_absensi.id', 'asc')
                ->pluck('sesi_absensi.id');

            foreach ($sesiIds as $idx => $sesiId) {
                $sesiNomorMap[$sesiId] = $idx + 1;
            }
        }

        $result = [];
        foreach ($items as $item) {
            $sesiId = $item->sesi_absensi_id ?? $item->sesiAbsensi?->id;
            $result[$item->id] = ($sesiId && isset($sesiNomorMap[$sesiId]))
                ? $sesiNomorMap[$sesiId]
                : null;
        }

        return $result;
    }

    /**
     * Memformat nomor pertemuan menjadi label tampilan yang ramah.
     *
     * Contoh: 1 -> "Pertemuan ke-1", null -> "-"
     */
    public function formatNomorPertemuan(?int $nomor): string
    {
        if ($nomor !== null && $nomor > 0) {
            return "Pertemuan ke-{$nomor}";
        }

        return '-';
    }
}
