<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px 4px;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .w-5 { width: 5%; }
        .w-10 { width: 10%; }
        .w-20 { width: 20%; }
    </style>
</head>
<body>
    <h2>Rekapitulasi Absensi Siswa</h2>
    
    <div style="margin-bottom: 15px;">
        <strong>Periode:</strong> {{ $periode ?? 'Semua Periode' }}<br>
        
        @if(!empty($namaJurusan))
            <strong>Jurusan:</strong> {{ $namaJurusan }}<br>
        @endif

        @if(!empty($namaKelas))
            <strong>Kelas:</strong> {{ $namaKelas }}<br>
        @endif
        
        @if(!empty($namaMapel))
            <strong>Mata Pelajaran:</strong> {{ $namaMapel }}<br>
        @endif
        
        @if(!empty($namaGuru))
            <strong>Guru:</strong> {{ $namaGuru }}<br>
        @endif
    </div>
    
    <table>
        <thead>
            <tr>
                <th class="w-5">No</th>
                <th class="w-10">NIS</th>
                <th class="w-20">Nama Siswa</th>
                <th class="w-10">Kelas</th>
                <th class="w-20">Mata Pelajaran</th>
                <th>Hadir</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Alpa</th>
                <th>Total</th>
                <th>Persentase</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $row)
                @php
                    $persentase = app(\App\Services\AbsensiService::class)->hitungPersentaseKehadiran(
                        (int) $row->hadir,
                        (int) $row->izin,
                        (int) $row->sakit,
                        (int) $row->total_sesi
                    );
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $row->nis }}</td>
                    <td>
                        {{ $row->nama_siswa }}
                        @if(!empty($row->is_nonaktif))
                            <span style="font-size: 10px; color: #6b7280;">(nonaktif)</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $row->nama_kelas }}</td>
                    <td>{{ $row->nama_mapel }}</td>
                    <td class="text-center">{{ $row->hadir }}</td>
                    <td class="text-center">{{ $row->izin }}</td>
                    <td class="text-center">{{ $row->sakit }}</td>
                    <td class="text-center">{{ $row->alpa }}</td>
                    <td class="text-center">{{ $row->total_sesi }}</td>
                    <td class="text-center">{{ $persentase }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center">
                        @if(!empty($filters['kelas_id']) && !empty($filters['jurusan_id']))
                            Kelas yang dipilih tidak sesuai dengan jurusan yang dipilih atau tidak ada data absensi.
                        @else
                            Tidak ada data absensi yang sesuai filter.
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
