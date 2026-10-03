<?php

namespace App\Exports;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Conditional;

class LaporanAbsensiPerKelasSheet implements FromArray, WithTitle, WithEvents, WithCustomStartCell
{
    protected $guruId;
    protected $kelasId;
    protected $mapelId;
    protected $filters;
    protected $mode;
    protected $jumlahPertemuan;

    protected $kelasNama = '';
    protected $mapelNama = '';
    protected $guruNama = '';
    protected $tanggalSesi = [];
    protected $hariSesi = [];

    public function __construct($guruId, $kelasId, $mapelId, $filters)
    {
        $this->guruId = $guruId;
        $this->kelasId = $kelasId;
        $this->mapelId = $mapelId;
        $this->filters = $filters;
        $this->mode = $filters['mode'] ?? 'data';
        $this->jumlahPertemuan = (int) ($filters['jumlah_pertemuan'] ?? 24);

        $kelas = Kelas::find($kelasId);
        $mapel = Mapel::find($mapelId);
        $guru = Guru::with('user')->find($guruId);

        $this->kelasNama = $kelas ? $kelas->nama : 'Unknown';
        $this->mapelNama = $mapel ? $mapel->nama : 'Unknown';
        $this->guruNama = $guru ? $guru->user->name : 'Unknown';
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function title(): string
    {
        if ($this->kelasId == 0) return 'Data Kosong';
        
        $abjadMapel = preg_replace('/[^A-Z]/', '', strtoupper($this->mapelNama));
        if (empty($abjadMapel)) $abjadMapel = substr(strtoupper($this->mapelNama), 0, 3);
        
        $title = $this->kelasNama . ' - ' . $abjadMapel;
        $title = str_replace(['*', ':', '?', '[', ']', '/'], '', $title);
        return substr($title, 0, 31);
    }

    public function array(): array
    {
        if ($this->kelasId == 0) return [];

        // 1. Ambil Siswa
        $siswaQuery = DB::table('siswa')
            ->join('users', 'siswa.user_id', '=', 'users.id')
            ->where('siswa.kelas_id', $this->kelasId)
            ->whereNull('siswa.deleted_at')
            ->select('siswa.id', 'siswa.nis', 'users.name as nama_siswa')
            ->orderBy('users.name', 'asc')
            ->get();

        // 2. Tentukan jumlah kolom pertemuan
        $totalPertemuan = $this->jumlahPertemuan;
        
        $detailMap = [];
        if ($this->mode === 'data') {
            $sesiQueryBase = DB::table('sesi_absensi')
                ->join('jadwal', 'sesi_absensi.jadwal_id', '=', 'jadwal.id')
                ->where('jadwal.guru_id', $this->guruId)
                ->where('jadwal.kelas_id', $this->kelasId)
                ->where('jadwal.mapel_id', $this->mapelId);
                
            if (!empty($this->filters['tanggal_awal'])) {
                $sesiQueryBase->where('sesi_absensi.tanggal', '>=', $this->filters['tanggal_awal']);
            }
            if (!empty($this->filters['tanggal_akhir'])) {
                $sesiQueryBase->where('sesi_absensi.tanggal', '<=', $this->filters['tanggal_akhir']);
            }
            if (!empty($this->filters['bulan'])) {
                $parts = explode('-', $this->filters['bulan']);
                if (count($parts) === 2) {
                    $sesiQueryBase->whereYear('sesi_absensi.tanggal', $parts[0])
                                  ->whereMonth('sesi_absensi.tanggal', $parts[1]);
                }
            }

            $sesiQuery = clone $sesiQueryBase;
            $sesiData = $sesiQuery->select('sesi_absensi.id', 'sesi_absensi.tanggal', 'jadwal.hari')
                ->orderBy('sesi_absensi.tanggal', 'asc')
                ->get();
                
            $this->tanggalSesi = $sesiData->pluck('tanggal')->toArray();
            $this->hariSesi = $sesiData->pluck('hari')->toArray();
            
            // Kolom minimal menyesuaikan dengan sesi yang ada
            if (count($this->tanggalSesi) > $totalPertemuan) {
                $totalPertemuan = count($this->tanggalSesi);
            }
            
            $detailQuery = DB::table('detail_absensi')
                ->join('sesi_absensi', 'detail_absensi.sesi_absensi_id', '=', 'sesi_absensi.id')
                ->join('jadwal', 'sesi_absensi.jadwal_id', '=', 'jadwal.id')
                ->where('jadwal.kelas_id', $this->kelasId)
                ->where('jadwal.mapel_id', $this->mapelId)
                ->select('detail_absensi.siswa_id', 'sesi_absensi.tanggal', 'detail_absensi.status')
                ->get();
                
            foreach ($detailQuery as $d) {
                $statusMap = ['hadir'=>'H', 'izin'=>'I', 'sakit'=>'S', 'alpa'=>'A'];
                $detailMap[$d->siswa_id][$d->tanggal] = $statusMap[$d->status] ?? '';
            }
        }

        // 3. Bangun baris data
        $rows = [];
        // Space untuk custom header (A1..A4) lalu row Tanggal (A5), Hari (A6), Table Header (A7)
        // Maka data siswa dimulai di baris ke-8.
        $startRowData = 8;
        $no = 1;

        foreach ($siswaQuery as $index => $siswa) {
            $currentRow = $startRowData + $index;
            $row = [
                $no++,
                " " . $siswa->nis, // Tambahkan spasi agar excel paksa sebagai teks
                $siswa->nama_siswa
            ];

            // Isi P1..Pn
            for ($i = 0; $i < $totalPertemuan; $i++) {
                if ($this->mode === 'data' && isset($this->tanggalSesi[$i])) {
                    $tgl = $this->tanggalSesi[$i];
                    $row[] = $detailMap[$siswa->id][$tgl] ?? '';
                } else {
                    $row[] = ''; // Template kosong
                }
            }

            // Rumus Excel di sisi kanan
            $colStartRange = Coordinate::stringFromColumnIndex(4);
            $colEndRange = Coordinate::stringFromColumnIndex(4 + $totalPertemuan - 1);
            $range = "{$colStartRange}{$currentRow}:{$colEndRange}{$currentRow}";

            $row[] = "=COUNTIF({$range}, \"H\")"; // Hadir
            $row[] = "=COUNTIF({$range}, \"I\")"; // Izin
            $row[] = "=COUNTIF({$range}, \"S\")"; // Sakit
            $row[] = "=COUNTIF({$range}, \"A\")"; // Alpa
            $row[] = "=COUNTA({$range})"; // Total Sesi
            $row[] = "=IF(COUNTA({$range})=0, 0, COUNTIF({$range}, \"H\") / COUNTA({$range}))"; // Persen

            $rows[] = $row;
        }
        
        $totalSiswa = count($siswaQuery);
        $barisRekapH = ['','','Rekapitulasi Hadir (H)'];
        $barisRekapI = ['','','Rekapitulasi Izin (I)'];
        $barisRekapS = ['','','Rekapitulasi Sakit (S)'];
        $barisRekapA = ['','','Rekapitulasi Alpa (A)'];
        
        for ($i = 0; $i < $totalPertemuan; $i++) {
            $colLetter = Coordinate::stringFromColumnIndex(4 + $i);
            $rangeCol = "{$colLetter}8:{$colLetter}" . (7 + $totalSiswa);
            $barisRekapH[] = "=COUNTIF({$rangeCol}, \"H\")";
            $barisRekapI[] = "=COUNTIF({$rangeCol}, \"I\")";
            $barisRekapS[] = "=COUNTIF({$rangeCol}, \"S\")";
            $barisRekapA[] = "=COUNTIF({$rangeCol}, \"A\")";
        }
        
        $rows[] = []; // Spacer
        $rows[] = $barisRekapH;
        $rows[] = $barisRekapI;
        $rows[] = $barisRekapS;
        $rows[] = $barisRekapA;
        $rows[] = [];
        $rows[] = ['Keterangan:', 'H = Hadir, I = Izin, S = Sakit, A = Alpa'];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                if ($this->kelasId == 0) return;
                
                $sheet = $event->sheet->getDelegate();
                $totalPertemuan = $this->jumlahPertemuan;
                if ($this->mode === 'data' && count($this->tanggalSesi) > $totalPertemuan) {
                    $totalPertemuan = count($this->tanggalSesi);
                }

                // --- 1. Custom Header Judul (A1 - A4) ---
                $periode = !empty($this->filters['bulan']) ? $this->filters['bulan'] : 'Periode Kustom';
                $sheet->setCellValue('A1', 'REKAPITULASI ABSENSI');
                $sheet->setCellValue('A2', 'Kelas : ' . $this->kelasNama);
                $sheet->setCellValue('A3', 'Mata Pelajaran : ' . $this->mapelNama);
                $sheet->setCellValue('A4', 'Guru : ' . $this->guruNama . ' | Periode : ' . $periode);
                
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->mergeCells('A1:J1');

                // --- 2. Header Tabel ---
                // Baris 5: Tanggal, Baris 6: Hari, Baris 7: Header P1 dll.
                $sheet->setCellValue('C5', 'Tanggal:');
                $sheet->setCellValue('C6', 'Hari:');
                $sheet->getStyle('C5:C6')->getAlignment()->setHorizontal('right');
                
                $sheet->setCellValue('A7', 'No');
                $sheet->setCellValue('B7', 'NIS');
                $sheet->setCellValue('C7', 'Nama Siswa');
                
                for ($i = 0; $i < $totalPertemuan; $i++) {
                    $col = Coordinate::stringFromColumnIndex(4 + $i);
                    $tgl = $this->mode === 'data' && isset($this->tanggalSesi[$i]) ? \Carbon\Carbon::parse($this->tanggalSesi[$i])->format('d/m') : '';
                    $hari = $this->mode === 'data' && isset($this->hariSesi[$i]) ? substr(ucfirst($this->hariSesi[$i]), 0, 3) : '';
                    
                    $sheet->setCellValue($col . '5', $tgl);
                    $sheet->setCellValue($col . '6', $hari);
                    $sheet->setCellValue($col . '7', 'P' . ($i + 1));
                    $sheet->getColumnDimension($col)->setWidth(5);
                }

                $summaryCols = ['Hadir', 'Izin', 'Sakit', 'Alpa', 'Total', 'Persentase'];
                $startSummaryCol = 4 + $totalPertemuan;
                foreach ($summaryCols as $idx => $sc) {
                    $col = Coordinate::stringFromColumnIndex($startSummaryCol + $idx);
                    $sheet->setCellValue($col . '7', $sc);
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Styling Header (A7 sampai Ujung)
                $endColIndex = Coordinate::stringFromColumnIndex($startSummaryCol + count($summaryCols) - 1);
                $sheet->getStyle("A7:{$endColIndex}7")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E5E7EB']],
                    'alignment' => ['horizontal' => 'center'],
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                ]);

                // --- 3. Format NIS Text & Lebar Kolom Standar ---
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(35);
                
                // Set explicitly format text for NIS column so it doesn't get scientific notation. 
                // But we already prefixed with space, which is reliable for Excel.
                
                // Freeze pane di D8
                $sheet->freezePane('D8');

                // --- 4. Data Validation Dropdown & Conditional Formatting ---
                $startColP = 'D';
                $endColP = Coordinate::stringFromColumnIndex(3 + $totalPertemuan);
                $highestRow = $sheet->getHighestRow() - 6; // exclude rekap and footer rows
                
                if ($highestRow >= 8) {
                    $rangeP = "{$startColP}8:{$endColP}{$highestRow}";
                    
                    // Validasi Dropdown
                    $validation = $sheet->getCell("D8")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST)
                        ->setErrorStyle(DataValidation::STYLE_STOP)
                        ->setAllowBlank(true)
                        ->setShowInputMessage(true)
                        ->setShowErrorMessage(true)
                        ->setErrorTitle('Input Error')
                        ->setError('Hanya izinkan kode H, I, S, atau A huruf besar.')
                        ->setFormula1('"H,I,S,A"');

                    for ($row = 8; $row <= $highestRow; $row++) {
                        for ($col = 4; $col < 4 + $totalPertemuan; $col++) {
                            $colLetter = Coordinate::stringFromColumnIndex($col);
                            $sheet->getCell("{$colLetter}{$row}")->setDataValidation(clone $validation);
                        }
                    }

                    // Conditional Formatting
                    $conditionalStyles = [];
                    $rules = [
                        'H' => 'C6F6D5', // Hijau
                        'I' => 'BEE3F8', // Biru
                        'S' => 'FEFCBF', // Kuning
                        'A' => 'FED7D7', // Merah
                    ];
                    foreach ($rules as $val => $color) {
                        $cond = new Conditional();
                        $cond->setConditionType(Conditional::CONDITION_CELLIS)
                             ->setOperatorType(Conditional::OPERATOR_EQUAL)
                             ->addCondition('"' . $val . '"')
                             ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getEndColor()->setARGB('FF' . $color);
                        $conditionalStyles[] = $cond;
                    }
                    $sheet->getStyle($rangeP)->setConditionalStyles($conditionalStyles);
                    $sheet->getStyle($rangeP)->getAlignment()->setHorizontal('center');
                }

                // --- 5. Format Persen ---
                $colPersen = Coordinate::stringFromColumnIndex($startSummaryCol + 5);
                if ($highestRow >= 8) {
                    $sheet->getStyle("{$colPersen}8:{$colPersen}{$highestRow}")->getNumberFormat()->setFormatCode('0.0%');
                }
                
                // --- 6. Set Print Layout ---
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 7);
                
                // --- 7. Border Keliling ---
                if ($highestRow >= 8) {
                    $sheet->getStyle("A7:{$endColIndex}{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                }
            }
        ];
    }
}
