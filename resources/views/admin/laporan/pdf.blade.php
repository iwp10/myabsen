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
        <strong>Periode:</strong> {{ $periode ?? (!empty($filters['bulan']) ? \Carbon\Carbon::createFromFormat('Y-m', $filters['bulan'])->translatedFormat('F Y') : 'Semua Periode') }}<br>
        
        @if(!empty($filters['kelas_id']))
            <strong>Kelas:</strong> {{ \App\Models\Kelas::find($filters['kelas_id'])->nama ?? '-' }}<br>
        @endif
        
        @if(!empty($filters['mapel_id']))
            <strong>Mata Pelajaran:</strong> {{ \App\Models\Mapel::find($filters['mapel_id'])->nama ?? '-' }}<br>
        @endif
        
        @if(!empty($filters['guru_id']))
            <strong>Guru:</strong> {{ \App\Models\Guru::find($filters['guru_id'])->user->name ?? '-' }}<br>
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
                    $totalSesi = $row->total_sesi > 0 ? $row->total_sesi : 1;
                    $persentase = round(($row->hadir / $totalSesi) * 100, 2);
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $row->nis }}</td>
                    <td>{{ $row->nama_siswa }}</td>
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
                    <td colspan="11" class="text-center">Tidak ada data absensi yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
