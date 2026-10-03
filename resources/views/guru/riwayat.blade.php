<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Riwayat Absensi
            </h2>
            {{-- Filter Rentang Tanggal --}}
            <form action="{{ route('guru.riwayat') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">Periode:</span>
                <input type="date" name="tanggal_awal" value="{{ $tanggalAwal ?? '' }}"
                    class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm text-sm py-1 px-2">
                <span class="text-sm text-gray-500">s/d</span>
                <input type="date" name="tanggal_akhir" value="{{ $tanggalAkhir ?? '' }}"
                    class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm text-sm py-1 px-2">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-3 py-1.5 rounded-md">
                    Terapkan
                </button>
                @if($tanggalAwal || $tanggalAkhir)
                    <a href="{{ route('guru.riwayat') }}"
                        class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 text-sm font-semibold px-3 py-1.5 rounded-md">
                        Reset
                    </a>
                @endif
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            @if($kelompokRiwayat->isEmpty())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-10 text-center text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p class="font-medium">Belum ada jadwal yang ditemukan.</p>
                </div>
            @else
                @foreach($kelompokRiwayat as $kelompok)
                    @php
                        $jadwal   = $kelompok['jadwal'];
                        $sesiList = $kelompok['sesiList'];   // koleksi SesiAbsensi, urut asc
                        $siswaList = $kelompok['siswaList']; // koleksi Siswa, sortBy name

                        // Buat map: [sesi_id => [siswa_id => status]]
                        $detailMap = [];
                        foreach ($sesiList as $sesi) {
                            foreach ($sesi->detailAbsensi as $detail) {
                                $detailMap[$sesi->id][$detail->siswa_id] = $detail->status->value ?? $detail->status;
                            }
                        }

                        $jumlahSesi = $sesiList->count();
                        $totalSiswa = $siswaList->count();

                        // Warna badge hari
                        $warnaHari = match(strtolower($jadwal->hari)) {
                            'senin'  => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                            'selasa' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                            'rabu'   => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                            'kamis'  => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                            'jumat'  => 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200',
                            'sabtu'  => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                            default  => 'bg-gray-100 text-gray-700',
                        };
                    @endphp

                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                        {{-- Card Header --}}
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $warnaHari }}">
                                        {{ ucfirst($jadwal->hari) }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ substr($jadwal->jam_mulai, 0, 5) }} – {{ substr($jadwal->jam_selesai, 0, 5) }}
                                    </span>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                    {{ $jadwal->mapel->nama }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Kelas: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $jadwal->kelas->nama }}</span>
                                    &bull; {{ $totalSiswa }} siswa &bull; {{ $jumlahSesi }} pertemuan
                                </p>
                            </div>

                            {{-- Tombol Export Excel per kelas+mapel --}}
                            <a href="{{ route('guru.laporan.export') }}?kelas_mapel={{ $jadwal->kelas_id }}-{{ $jadwal->mapel_id }}&mode=data{{ $tanggalAwal ? '&tanggal_awal='.$tanggalAwal : '' }}{{ $tanggalAkhir ? '&tanggal_akhir='.$tanggalAkhir : '' }}"
                                class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-3 py-1.5 rounded-md transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Export Excel
                            </a>
                        </div>

                        @if($jumlahSesi === 0)
                            <div class="px-6 py-8 text-center text-gray-400 dark:text-gray-500 text-sm">
                                @if($tanggalAwal || $tanggalAkhir)
                                    Belum ada pertemuan dalam periode yang dipilih.
                                @else
                                    Belum ada pertemuan yang tercatat.
                                @endif
                            </div>
                        @else
                            {{-- Tabel Absensi Horizontal --}}
                            <div class="overflow-x-auto">
                                <table class="border-collapse text-sm" style="min-width: max-content">
                                    <thead>
                                        {{-- Baris tanggal / nomor pertemuan --}}
                                        <tr class="bg-gray-50 dark:bg-gray-700/60">
                                            <th class="sticky left-0 z-10 bg-gray-50 dark:bg-gray-700/60 px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-600 w-8 text-center">No</th>
                                            <th class="sticky left-8 z-10 bg-gray-50 dark:bg-gray-700/60 px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-600 w-32">NIS</th>
                                            <th class="sticky left-[10rem] z-10 bg-gray-50 dark:bg-gray-700/60 px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-600 w-52">Nama Siswa</th>
                                            @foreach($sesiList as $idx => $sesi)
                                                <th class="px-2 py-2 border-b border-r border-gray-200 dark:border-gray-600 text-center font-semibold text-gray-600 dark:text-gray-300 w-16">
                                                    <div class="text-xs font-bold text-gray-500 dark:text-gray-400">P{{ $idx + 1 }}</div>
                                                    <div class="text-xs text-gray-400 dark:text-gray-500 font-normal">{{ \Carbon\Carbon::parse($sesi->tanggal)->locale('id')->isoFormat('D MMM') }}</div>
                                                    <div class="text-xs text-gray-400 dark:text-gray-500 font-normal">{{ substr(ucfirst(\Carbon\Carbon::parse($sesi->tanggal)->locale('id')->isoFormat('dddd')), 0, 3) }}</div>
                                                </th>
                                            @endforeach
                                            {{-- Kolom Ringkasan --}}
                                            <th class="px-2 py-2 border-b border-r border-gray-200 dark:border-gray-600 text-center font-semibold text-green-600 dark:text-green-400 w-10">H</th>
                                            <th class="px-2 py-2 border-b border-r border-gray-200 dark:border-gray-600 text-center font-semibold text-blue-600 dark:text-blue-400 w-10">I</th>
                                            <th class="px-2 py-2 border-b border-r border-gray-200 dark:border-gray-600 text-center font-semibold text-yellow-600 dark:text-yellow-400 w-10">S</th>
                                            <th class="px-2 py-2 border-b border-r border-gray-200 dark:border-gray-600 text-center font-semibold text-red-600 dark:text-red-400 w-10">A</th>
                                            <th class="px-2 py-2 border-b border-gray-200 dark:border-gray-600 text-center font-semibold text-gray-600 dark:text-gray-300 w-16">%</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        @foreach($siswaList as $no => $siswa)
                                            @php
                                                $h = 0; $i = 0; $s = 0; $a = 0; $total = 0;
                                            @endphp
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                                <td class="sticky left-0 z-10 bg-white dark:bg-gray-800 px-3 py-2 text-center text-gray-500 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700 w-8">{{ $no + 1 }}</td>
                                                <td class="sticky left-8 z-10 bg-white dark:bg-gray-800 px-3 py-2 text-gray-600 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700 font-mono text-xs w-32">{{ $siswa->nis }}</td>
                                                <td class="sticky left-[10rem] z-10 bg-white dark:bg-gray-800 px-3 py-2 font-medium text-gray-800 dark:text-gray-200 border-r border-gray-200 dark:border-gray-700 w-52">{{ $siswa->user->name }}</td>
                                                @foreach($sesiList as $sesi)
                                                    @php
                                                        $status = $detailMap[$sesi->id][$siswa->id] ?? null;
                                                        [$kode, $bgKelas] = match($status) {
                                                            'hadir' => ['H', 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300'],
                                                            'izin'  => ['I', 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300'],
                                                            'sakit' => ['S', 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-300'],
                                                            'alpa'  => ['A', 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300'],
                                                            default => ['-', 'text-gray-300 dark:text-gray-600'],
                                                        };
                                                        if ($status) {
                                                            $total++;
                                                            if ($status === 'hadir') $h++;
                                                            elseif ($status === 'izin') $i++;
                                                            elseif ($status === 'sakit') $s++;
                                                            elseif ($status === 'alpa') $a++;
                                                        }
                                                    @endphp
                                                    <td class="px-1 py-2 border-r border-gray-100 dark:border-gray-700 w-16 text-center">
                                                        <span class="inline-block w-7 h-7 leading-7 rounded font-bold text-xs text-center {{ $bgKelas }}">{{ $kode }}</span>
                                                    </td>
                                                @endforeach
                                                {{-- Ringkasan --}}
                                                @php $persen = $total > 0 ? round($h / $total * 100, 1) : 0; @endphp
                                                <td class="px-2 py-2 text-center font-bold text-green-600 dark:text-green-400 border-r border-gray-100 dark:border-gray-700 w-10">{{ $h }}</td>
                                                <td class="px-2 py-2 text-center font-bold text-blue-600 dark:text-blue-400 border-r border-gray-100 dark:border-gray-700 w-10">{{ $i }}</td>
                                                <td class="px-2 py-2 text-center font-bold text-yellow-600 dark:text-yellow-400 border-r border-gray-100 dark:border-gray-700 w-10">{{ $s }}</td>
                                                <td class="px-2 py-2 text-center font-bold text-red-600 dark:text-red-400 border-r border-gray-100 dark:border-gray-700 w-10">{{ $a }}</td>
                                                <td class="px-2 py-2 text-center text-xs font-semibold w-16 {{ $persen >= 75 ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400' }}">
                                                    {{ $persen }}%
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    {{-- Baris Rekap Per Pertemuan (Hadir/Izin/Sakit/Alpa) --}}
                                    <tfoot>
                                        @foreach(['hadir' => ['Hadir (H)', 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300'], 'izin' => ['Izin (I)', 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300'], 'sakit' => ['Sakit (S)', 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300'], 'alpa' => ['Alpa (A)', 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300']] as $st => $info)
                                            <tr class="{{ $info[1] }}">
                                                <td class="sticky left-0 z-10 {{ $info[1] }} px-3 py-1.5 border-t border-r border-gray-200 dark:border-gray-700" colspan="3">
                                                    <span class="text-xs font-semibold">{{ $info[0] }}</span>
                                                </td>
                                                @foreach($sesiList as $sesi)
                                                    @php
                                                        $count = $sesi->detailAbsensi->where('status', \App\Enums\StatusKehadiran::from($st))->count();
                                                    @endphp
                                                    <td class="px-2 py-1.5 text-center text-xs font-bold border-t border-r border-gray-200 dark:border-gray-700">{{ $count ?: '-' }}</td>
                                                @endforeach
                                                <td colspan="5" class="border-t border-gray-200 dark:border-gray-700"></td>
                                            </tr>
                                        @endforeach
                                    </tfoot>
                                </table>
                            </div>

                            {{-- Keterangan --}}
                            <div class="px-6 py-3 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-400 dark:text-gray-500 flex gap-4">
                                <span class="inline-flex items-center gap-1"><span class="w-4 h-4 rounded bg-green-100 dark:bg-green-900/50 inline-block"></span>H = Hadir</span>
                                <span class="inline-flex items-center gap-1"><span class="w-4 h-4 rounded bg-blue-100 dark:bg-blue-900/50 inline-block"></span>I = Izin</span>
                                <span class="inline-flex items-center gap-1"><span class="w-4 h-4 rounded bg-yellow-100 dark:bg-yellow-900/50 inline-block"></span>S = Sakit</span>
                                <span class="inline-flex items-center gap-1"><span class="w-4 h-4 rounded bg-red-100 dark:bg-red-900/50 inline-block"></span>A = Alpa</span>
                                <span class="ml-auto">— = Belum diabsen</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif

        </div>
    </div>
</x-app-layout>
