<?php

namespace App\Exports;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Services\AbsensiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

/**
 * Satu sheet rekap absensi untuk satu pasangan Kelas + Mapel milik guru.
 * Tata letak disamakan dengan tabel Riwayat di web:
 * No | NIS | Nama Siswa | P1..Pn (tanggal + hari) | H | I | S | A | %
 * Kolom pertemuan hanya sebanyak sesi yang benar-benar ada.
 */
class LaporanAbsensiPerKelasSheet implements FromArray, WithCustomStartCell, WithEvents, WithStrictNullComparison, WithTitle
{
    /** Baris tetap pada layout. */
    private const ROW_JUDUL = 1;

    private const ROW_HEADER = 7;   // No / NIS / Nama / P1..Pn / H I S A %

    private const ROW_TANGGAL = 8;  // tanggal lengkap per pertemuan

    private const ROW_HARI = 9;     // nama hari per pertemuan

    private const ROW_DATA = 10;    // data siswa mulai di sini

    private const WARNA_STATUS = [
        'H' => 'C6F6D5', // hijau
        'I' => 'BEE3F8', // biru
        'S' => 'FEFCBF', // kuning
        'A' => 'FED7D7', // merah
    ];

    protected $guruId;

    protected $kelasId;

    protected $mapelId;

    protected $filters;

    protected $kelasNama = '';

    protected $mapelNama = '';

    protected $guruNama = '';

    protected ?string $customTitle = null;

    /** @var array<int, string> tanggal (Y-m-d) per pertemuan */
    protected array $tanggalSesi = [];

    protected int $jumlahSiswa = 0;

    public function __construct($guruId, $kelasId, $mapelId, $filters, ?string $customTitle = null)
    {
        $this->guruId = $guruId;
        $this->kelasId = $kelasId;
        $this->mapelId = $mapelId;
        $this->filters = $filters;
        $this->customTitle = $customTitle;

        $kelas = Kelas::withTrashed()->find($kelasId);
        $mapel = Mapel::withTrashed()->find($mapelId);

        $this->kelasNama = $kelas ? $kelas->nama : '-';
        $this->mapelNama = $mapel ? $mapel->nama : '-';

        if (! empty($guruId)) {
            $guru = Guru::with('user')->find($guruId);
            $this->guruNama = $guru && $guru->user ? $guru->user->name : '-';
        } else {
            $guruNames = DB::table('jadwal')
                ->join('guru', 'jadwal.guru_id', '=', 'guru.id')
                ->join('users', 'guru.user_id', '=', 'users.id')
                ->where('jadwal.kelas_id', $kelasId)
                ->where('jadwal.mapel_id', $mapelId)
                ->when(! empty($filters['tahun_ajaran']), fn ($q) => $q->where('jadwal.tahun_ajaran', $filters['tahun_ajaran']))
                ->when(! empty($filters['semester']), fn ($q) => $q->where('jadwal.semester', $filters['semester']))
                ->distinct()
                ->pluck('users.name')
                ->all();
            $this->guruNama = ! empty($guruNames) ? implode(', ', $guruNames) : '-';
        }
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function title(): string
    {
        if ($this->customTitle !== null) {
            return $this->customTitle;
        }

        if ($this->kelasId == 0) {
            return 'Data Kosong';
        }

        $abjadMapel = preg_replace('/[^A-Z]/', '', strtoupper($this->mapelNama));
        if (empty($abjadMapel)) {
            $abjadMapel = substr(strtoupper($this->mapelNama), 0, 3);
        }

        $title = $this->kelasNama.' - '.$abjadMapel;
        $title = str_replace(['*', ':', '?', '[', ']', '/', '\\'], '', $title);

        return substr($title, 0, 31);
    }

    public function array(): array
    {
        if ($this->kelasId == 0) {
            return [['Belum ada jadwal mengajar.']];
        }

        // 1. Siswa di kelas ini
        $siswaList = DB::table('siswa')
            ->join('users', 'siswa.user_id', '=', 'users.id')
            ->where('siswa.kelas_id', $this->kelasId)
            ->whereNull('siswa.deleted_at')
            ->select('siswa.id', 'siswa.nis', 'users.name as nama_siswa')
            ->orderBy('users.name')
            ->get();
        $this->jumlahSiswa = $siswaList->count();

        // 2. Sesi (pertemuan) yang benar-benar ada
        $sesiQuery = DB::table('sesi_absensi')
            ->join('jadwal', 'sesi_absensi.jadwal_id', '=', 'jadwal.id')
            ->where('jadwal.kelas_id', $this->kelasId)
            ->where('jadwal.mapel_id', $this->mapelId);

        if (! empty($this->guruId)) {
            $sesiQuery->where('jadwal.guru_id', $this->guruId);
        }

        if (! empty($this->filters['tanggal_awal'])) {
            $sesiQuery->where('sesi_absensi.tanggal', '>=', $this->filters['tanggal_awal']);
        }
        if (! empty($this->filters['tanggal_akhir'])) {
            $sesiQuery->where('sesi_absensi.tanggal', '<=', $this->filters['tanggal_akhir']);
        }
        if (! empty($this->filters['tahun_ajaran'])) {
            $sesiQuery->where('jadwal.tahun_ajaran', $this->filters['tahun_ajaran']);
        }
        if (! empty($this->filters['semester'])) {
            $sesiQuery->where('jadwal.semester', $this->filters['semester']);
        }

        $sesiData = $sesiQuery
            ->select('sesi_absensi.id', 'sesi_absensi.tanggal')
            ->orderBy('sesi_absensi.tanggal')
            ->get();

        $sesiIds = $sesiData->pluck('id')->all();
        $this->tanggalSesi = $sesiData
            ->map(fn ($s) => Carbon::parse($s->tanggal)->toDateString())
            ->all();
        $jumlahSesi = count($sesiIds);

        // 3. Detail absensi: [siswa_id][sesi_id] => kode
        $kodeStatus = ['hadir' => 'H', 'izin' => 'I', 'sakit' => 'S', 'alpa' => 'A'];
        $detailMap = [];
        if ($jumlahSesi > 0) {
            DB::table('detail_absensi')
                ->whereIn('sesi_absensi_id', $sesiIds)
                ->select('siswa_id', 'sesi_absensi_id', 'status')
                ->get()
                ->each(function ($d) use (&$detailMap, $kodeStatus) {
                    $detailMap[$d->siswa_id][$d->sesi_absensi_id] = $kodeStatus[$d->status] ?? '';
                });
        }

        $absensiService = app(AbsensiService::class);

        // 4. Bangun baris
        $rows = [];

        // Kop (baris 1-5), baris 6 kosong
        $rows[] = ['REKAPITULASI ABSENSI'];
        $rows[] = ['Kelas', '', ': '.$this->kelasNama];
        $rows[] = ['Mata Pelajaran', '', ': '.$this->mapelNama];
        $rows[] = ['Guru', '', ': '.$this->guruNama];
        $rows[] = ['Periode', '', ': '.$this->teksPeriode()];
        $rows[] = [''];

        // Header tabel (baris 7-9)
        $header = ['No', 'NIS', 'Nama Siswa'];
        $barisTanggal = ['', '', ''];
        $barisHari = ['', '', ''];
        foreach ($this->tanggalSesi as $idx => $tgl) {
            $c = Carbon::parse($tgl)->locale('id');
            $header[] = 'P'.($idx + 1);
            $barisTanggal[] = $c->format('d/m/Y');
            $barisHari[] = $c->isoFormat('dddd');
        }
        array_push($header, 'H', 'I', 'S', 'A', '%');
        $rows[] = $header;
        $rows[] = $barisTanggal;
        $rows[] = $barisHari;

        // Data siswa (baris 10+)
        $rekap = array_fill_keys(['H', 'I', 'S', 'A'], array_fill(0, $jumlahSesi, 0));

        foreach ($siswaList as $no => $siswa) {
            $row = [$no + 1, ' '.$siswa->nis, $siswa->nama_siswa]; // spasi: paksa NIS sebagai teks
            $hitung = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0];

            foreach ($sesiIds as $idx => $sesiId) {
                $kode = $detailMap[$siswa->id][$sesiId] ?? '';
                if ($kode !== '') {
                    $hitung[$kode]++;
                    $rekap[$kode][$idx]++;
                    $row[] = $kode;
                } else {
                    $row[] = '·'; // belum diabsen / bukan anggota
                }
            }

            $totalData = array_sum($hitung);
            $persen = $absensiService->hitungPersentaseKehadiran($hitung['H'], $hitung['I'], $hitung['S'], $totalData);

            array_push($row, $hitung['H'], $hitung['I'], $hitung['S'], $hitung['A'], $persen / 100);
            $rows[] = $row;
        }

        // Rekap per pertemuan
        $labelRekap = ['H' => 'Hadir (H)', 'I' => 'Izin (I)', 'S' => 'Sakit (S)', 'A' => 'Alpa (A)'];
        foreach ($labelRekap as $kode => $label) {
            $rows[] = array_merge([$label, '', ''], array_map(fn ($n) => $n ?: '-', $rekap[$kode]));
        }

        // Keterangan
        $rows[] = [''];
        $rows[] = ['Keterangan: H = Hadir, I = Izin, S = Sakit, A = Alpa, · = Belum diabsen / bukan anggota'];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                if ($this->kelasId == 0) {
                    return;
                }

                $sheet = $event->sheet->getDelegate();
                $jumlahSesi = count($this->tanggalSesi);

                $kolomAkhirIdx = 3 + $jumlahSesi + 5; // No,NIS,Nama + P + H,I,S,A,%
                $kolomAkhir = Coordinate::stringFromColumnIndex($kolomAkhirIdx);
                $kolomH = Coordinate::stringFromColumnIndex(4 + $jumlahSesi);
                $kolomPersen = $kolomAkhir;

                $rowDataAkhir = self::ROW_DATA + $this->jumlahSiswa - 1;
                $rowRekapAwal = self::ROW_DATA + $this->jumlahSiswa;
                $rowRekapAkhir = $rowRekapAwal + 3;
                $rowKeterangan = $rowRekapAkhir + 2;

                $thin = ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '9CA3AF']];

                // --- Kop ---
                $sheet->mergeCells('A1:'.$kolomAkhir.'1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                for ($r = 2; $r <= 5; $r++) {
                    $sheet->mergeCells("A{$r}:B{$r}");
                    $sheet->getStyle("A{$r}")->getFont()->setBold(true);
                }

                // --- Header tabel: No/NIS/Nama dan H/I/S/A/% digabung 3 baris ---
                $kolomGabung = ['A', 'B', 'C'];
                for ($i = 0; $i < 5; $i++) {
                    $kolomGabung[] = Coordinate::stringFromColumnIndex(4 + $jumlahSesi + $i);
                }
                foreach ($kolomGabung as $col) {
                    $sheet->mergeCells($col.self::ROW_HEADER.':'.$col.self::ROW_HARI);
                }

                $rangeHeader = 'A'.self::ROW_HEADER.':'.$kolomAkhir.self::ROW_HARI;
                $sheet->getStyle($rangeHeader)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E5E7EB']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => ['allBorders' => $thin],
                ]);
                if ($jumlahSesi > 0) {
                    $rangeTglHari = 'D'.self::ROW_TANGGAL.':'.Coordinate::stringFromColumnIndex(3 + $jumlahSesi).self::ROW_HARI;
                    $sheet->getStyle($rangeTglHari)->getFont()->setBold(false)->setSize(9);
                }

                // Warna judul kolom H/I/S/A
                foreach (['H' => '15803D', 'I' => '1D4ED8', 'S' => 'A16207', 'A' => 'B91C1C'] as $i => $warna) {
                    $off = array_search($i, ['H', 'I', 'S', 'A'], true);
                    $col = Coordinate::stringFromColumnIndex(4 + $jumlahSesi + $off);
                    $sheet->getStyle($col.self::ROW_HEADER)->getFont()->getColor()->setRGB($warna);
                }

                // --- Lebar kolom ---
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(14);
                $sheet->getColumnDimension('C')->setWidth(32);
                for ($i = 0; $i < $jumlahSesi; $i++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex(4 + $i))->setWidth(11);
                }
                for ($i = 0; $i < 4; $i++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex(4 + $jumlahSesi + $i))->setWidth(6);
                }
                $sheet->getColumnDimension($kolomPersen)->setWidth(9);

                // --- Data siswa ---
                if ($this->jumlahSiswa > 0) {
                    $rangeData = 'A'.self::ROW_DATA.':'.$kolomAkhir.$rowDataAkhir;
                    $sheet->getStyle($rangeData)->getBorders()->getAllBorders()->applyFromArray($thin);
                    $sheet->getStyle('A'.self::ROW_DATA.':A'.$rowDataAkhir)
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('D'.self::ROW_DATA.':'.$kolomAkhir.$rowDataAkhir)
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle($kolomH.self::ROW_DATA.':'.$kolomAkhir.$rowDataAkhir)
                        ->getFont()->setBold(true);
                    $sheet->getStyle($kolomPersen.self::ROW_DATA.':'.$kolomPersen.$rowDataAkhir)
                        ->getNumberFormat()->setFormatCode('0.##%');

                    // Warna sel status per pertemuan (sama seperti web)
                    for ($r = self::ROW_DATA; $r <= $rowDataAkhir; $r++) {
                        for ($i = 0; $i < $jumlahSesi; $i++) {
                            $cell = Coordinate::stringFromColumnIndex(4 + $i).$r;
                            $nilai = (string) $sheet->getCell($cell)->getValue();
                            if (isset(self::WARNA_STATUS[$nilai])) {
                                $sheet->getStyle($cell)->getFill()
                                    ->setFillType(Fill::FILL_SOLID)
                                    ->getStartColor()->setRGB(self::WARNA_STATUS[$nilai]);
                                $sheet->getStyle($cell)->getFont()->setBold(true);
                            } else {
                                $sheet->getStyle($cell)->getFont()->getColor()->setRGB('9CA3AF');
                            }
                        }

                        // % merah jika < 75
                        $persen = (float) $sheet->getCell($kolomPersen.$r)->getValue();
                        $sheet->getStyle($kolomPersen.$r)->getFont()->getColor()
                            ->setRGB($persen >= 0.75 ? '15803D' : 'DC2626');
                    }
                }

                // --- Rekap per pertemuan ---
                $warnaRekap = ['DCFCE7', 'DBEAFE', 'FEF9C3', 'FEE2E2'];
                for ($k = 0; $k < 4; $k++) {
                    $r = $rowRekapAwal + $k;
                    $sheet->mergeCells("A{$r}:C{$r}");
                    $sheet->getStyle("A{$r}:{$kolomAkhir}{$r}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $warnaRekap[$k]]],
                        'borders' => ['allBorders' => $thin],
                    ]);
                    $sheet->getStyle("D{$r}:{$kolomAkhir}{$r}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // --- Keterangan ---
                $sheet->getStyle("A{$rowKeterangan}")->getFont()->setItalic(true)->setSize(9);

                // --- Freeze & cetak ---
                $sheet->freezePane('D'.self::ROW_DATA);
                $setup = $sheet->getPageSetup();
                $setup->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $setup->setPaperSize(PageSetup::PAPERSIZE_A4);
                $setup->setFitToWidth(1);
                $setup->setFitToHeight(0);
                $setup->setRowsToRepeatAtTopByStartAndEnd(self::ROW_HEADER, self::ROW_HARI);
                $sheet->getPageMargins()->setLeft(0.4)->setRight(0.4)->setTop(0.5)->setBottom(0.5);
            },
        ];
    }

    /**
     * Teks periode untuk kop: dari filter, atau rentang tanggal pertemuan yang ada.
     */
    private function teksPeriode(): string
    {
        $fmt = fn ($t) => Carbon::parse($t)->locale('id')->isoFormat('D MMMM YYYY');

        if (! empty($this->filters['tahun_ajaran'])) {
            $ta = 'TA '.$this->filters['tahun_ajaran'];
            $sem = ! empty($this->filters['semester']) ? ' Semester '.$this->filters['semester'] : '';

            return $ta.$sem;
        }
        if (! empty($this->filters['tanggal_awal']) || ! empty($this->filters['tanggal_akhir'])) {
            $awal = ! empty($this->filters['tanggal_awal']) ? $fmt($this->filters['tanggal_awal']) : '...';
            $akhir = ! empty($this->filters['tanggal_akhir']) ? $fmt($this->filters['tanggal_akhir']) : '...';

            return "{$awal} s/d {$akhir}";
        }
        if (count($this->tanggalSesi) > 0) {
            return $fmt(reset($this->tanggalSesi)).' s/d '.$fmt(end($this->tanggalSesi))
                .' ('.count($this->tanggalSesi).' pertemuan)';
        }

        return 'Belum ada pertemuan';
    }
}
