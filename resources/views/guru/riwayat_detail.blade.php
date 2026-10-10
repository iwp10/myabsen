<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('guru.riwayat', ['tahun_ajaran' => $selectedPeriode['tahun_ajaran'], 'semester' => $selectedPeriode['semester']]) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" title="Kembali">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Riwayat Absensi: {{ $kelas->nama }} - {{ $mapel->nama }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @php
                // Buat map: [sesi_id => [siswa_id => status]]
                $detailMap = [];
                foreach ($sesiList as $sesi) {
                    foreach ($sesi->detailAbsensi as $detail) {
                        $detailMap[$sesi->id][$detail->siswa_id] = $detail->status->value ?? $detail->status;
                    }
                }

                $jumlahSesi = $sesiList->count();
                $totalSiswa = $siswaList->count();
            @endphp

            {{-- Filter Periode, Pencarian Siswa, dan Tombol Export --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 border border-gray-100 dark:border-gray-700">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                            {{ $mapel->nama }} &bull; {{ $kelas->nama }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            Periode:
                            <span class="font-semibold text-blue-600 dark:text-blue-400">
                                {{ $selectedPeriode['tahun_ajaran'] }} - Semester {{ $selectedPeriode['semester'] }}
                            </span>
                            @if($selectedPeriode['tahun_ajaran'] === ($activePeriode['tahun_ajaran'] ?? '') && $selectedPeriode['semester'] === ($activePeriode['semester'] ?? ''))
                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                    (aktif)
                                </span>
                            @endif
                            <span class="mx-1">&bull;</span>
                            <span>{{ $totalSiswaSebelumFilter }} siswa terdaftar</span>
                            <span class="mx-1">&bull;</span>
                            <span>{{ $jumlahSesi }} pertemuan</span>
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto">
                        <form action="{{ route('guru.riwayat.detail', ['kelas' => $kelas->id, 'mapel' => $mapel->id]) }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full">
                            <select name="periode" class="w-full sm:w-56 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                @foreach($daftarPeriode as $item)
                                    <option value="{{ $item['value'] }}" {{ $filterPeriode === $item['value'] ? 'selected' : '' }}>
                                        {{ $item['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama atau NIS siswa..." class="w-full sm:w-56 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md text-sm transition text-center">
                                Cari
                            </button>
                            @if($search !== '' || (request()->filled('periode') && request('periode') !== ($activePeriode['tahun_ajaran'].'|'.$activePeriode['semester'])) || request()->filled('tahun_ajaran') || request()->filled('semester'))
                                <a href="{{ route('guru.riwayat.detail', ['kelas' => $kelas->id, 'mapel' => $mapel->id]) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-3 rounded-md text-sm flex items-center justify-center transition">
                                    Reset
                                </a>
                            @endif
                        </form>

                        {{-- Tombol Export Excel: membawa periode terpilih, TANPA parameter pencarian q --}}
                        <a href="{{ route('guru.laporan.export', ['kelas_mapel' => \App\Support\KelasMapel::make($kelas->id, $mapel->id), 'tahun_ajaran' => $selectedPeriode['tahun_ajaran'], 'semester' => $selectedPeriode['semester']]) }}"
                            class="inline-flex items-center justify-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-3 py-2 rounded-md transition whitespace-nowrap">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export Excel
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden border border-gray-100 dark:border-gray-700">
                @if($jumlahSesi === 0)
                    <div class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 mb-3 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="font-medium text-gray-700 dark:text-gray-300">Belum ada pertemuan yang tercatat pada periode ini.</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Sesi absensi akan tampil setelah guru melakukan absensi kelas.</p>
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
                                    <th class="px-2 py-2 border-b border-r border-gray-200 dark:border-gray-600 text-center font-semibold text-gray-600 dark:text-gray-300 w-16">%</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @php
                                    $batasRendah = (int) config('absensi.batas_kehadiran_rendah', 75);
                                @endphp
                                @forelse($siswaList as $no => $siswa)
                                    @php
                                        $h = 0; $i = 0; $s = 0; $a = 0; $total = 0;
                                        foreach ($sesiList as $sItem) {
                                            $st = $detailMap[$sItem->id][$siswa->id] ?? null;
                                            if ($st) {
                                                $total++;
                                                if ($st === 'hadir') $h++;
                                                elseif ($st === 'izin') $i++;
                                                elseif ($st === 'sakit') $s++;
                                                elseif ($st === 'alpa') $a++;
                                            }
                                        }
                                        $persen = app(\App\Services\AbsensiService::class)->hitungPersentaseKehadiran($h, $i, $s, $total);
                                        $isPerluPerhatian = ($total > 0 && $persen < $batasRendah);
                                        $rowBg = $isPerluPerhatian 
                                            ? 'bg-red-50/60 dark:bg-red-950/20 hover:bg-red-100/60 dark:hover:bg-red-950/35' 
                                            : 'hover:bg-gray-50 dark:hover:bg-gray-700/40';
                                        $stickyBg = $isPerluPerhatian 
                                            ? 'bg-red-50/90 dark:bg-red-950/80' 
                                            : 'bg-white dark:bg-gray-800';
                                    @endphp
                                    <tr class="{{ $rowBg }}">
                                        <td class="sticky left-0 z-10 {{ $stickyBg }} px-3 py-2 text-center text-gray-500 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700 w-8">{{ $no + 1 }}</td>
                                        <td class="sticky left-8 z-10 {{ $stickyBg }} px-3 py-2 text-gray-600 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700 font-mono text-xs w-32">{{ $siswa->nis }}</td>
                                        <td class="sticky left-[10rem] z-10 {{ $stickyBg }} px-3 py-2 font-medium text-gray-800 dark:text-gray-200 border-r border-gray-200 dark:border-gray-700 w-52">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span>{{ $siswa->user->name ?? $siswa->nama }}</span>
                                                @if($siswa->is_nonaktif)
                                                    <span class="text-xs text-gray-400 dark:text-gray-500 font-normal">(nonaktif)</span>
                                                @endif
                                                @if($isPerluPerhatian)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-900/60 dark:text-red-300 border border-red-200 dark:border-red-800">Perlu perhatian</span>
                                                @endif
                                            </div>
                                        </td>
                                        @foreach($sesiList as $sesi)
                                            @php
                                                $status = $detailMap[$sesi->id][$siswa->id] ?? null;
                                                [$kode, $bgKelas] = match($status) {
                                                    'hadir' => ['H', 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300'],
                                                    'izin'  => ['I', 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300'],
                                                    'sakit' => ['S', 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-300'],
                                                    'alpa'  => ['A', 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300'],
                                                    default => ['·', 'text-gray-300 dark:text-gray-600'],
                                                };
                                            @endphp
                                            <td class="px-1 py-2 border-r border-gray-100 dark:border-gray-700 w-16 text-center">
                                                <span class="inline-block w-7 h-7 leading-7 rounded font-bold text-xs text-center {{ $bgKelas }}">{{ $kode }}</span>
                                            </td>
                                        @endforeach
                                        {{-- Ringkasan --}}
                                        <td class="px-2 py-2 text-center font-bold text-green-600 dark:text-green-400 border-r border-gray-100 dark:border-gray-700 w-10">{{ $h }}</td>
                                        <td class="px-2 py-2 text-center font-bold text-blue-600 dark:text-blue-400 border-r border-gray-100 dark:border-gray-700 w-10">{{ $i }}</td>
                                        <td class="px-2 py-2 text-center font-bold text-yellow-600 dark:text-yellow-400 border-r border-gray-100 dark:border-gray-700 w-10">{{ $s }}</td>
                                        <td class="px-2 py-2 text-center font-bold text-red-600 dark:text-red-400 border-r border-gray-100 dark:border-gray-700 w-10">{{ $a }}</td>
                                        <td class="px-2 py-2 text-center text-xs font-semibold w-16 {{ $isPerluPerhatian ? 'text-red-600 dark:text-red-400 font-bold' : ($persen >= $batasRendah ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400') }}">
                                            {{ $persen }}%
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 3 + $jumlahSesi + 5 }}" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            @if($search !== '')
                                                Tidak ada siswa yang sesuai dengan pencarian "{{ $search }}".
                                            @else
                                                Belum ada data siswa di kelas ini.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
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
                    <div class="px-6 py-3 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-400 dark:text-gray-500 flex flex-wrap gap-4 items-center">
                        <span class="inline-flex items-center gap-1"><span class="w-4 h-4 rounded bg-green-100 dark:bg-green-900/50 inline-block"></span>H = Hadir</span>
                        <span class="inline-flex items-center gap-1"><span class="w-4 h-4 rounded bg-blue-100 dark:bg-blue-900/50 inline-block"></span>I = Izin</span>
                        <span class="inline-flex items-center gap-1"><span class="w-4 h-4 rounded bg-yellow-100 dark:bg-yellow-900/50 inline-block"></span>S = Sakit</span>
                        <span class="inline-flex items-center gap-1"><span class="w-4 h-4 rounded bg-red-100 dark:bg-red-900/50 inline-block"></span>A = Alpa</span>
                        <span class="inline-flex items-center gap-1.5"><span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-900/60 dark:text-red-300 border border-red-200 dark:border-red-800">Perlu perhatian</span> = Kehadiran &lt; {{ config('absensi.batas_kehadiran_rendah', 75) }}%</span>
                        <span class="ml-auto">· = Belum diabsen / Bukan anggota</span>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
