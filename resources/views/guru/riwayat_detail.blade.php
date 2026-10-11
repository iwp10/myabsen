<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('guru.riwayat', ['tahun_ajaran' => $selectedPeriode['tahun_ajaran'], 'semester' => $selectedPeriode['semester']]) }}" class="text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200" title="Kembali">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h2 class="font-bold text-xl text-slate-900 dark:text-white leading-tight">
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
            <div class="bg-white dark:bg-slate-900 shadow-xs rounded-xl p-5 sm:p-6 border-2 border-slate-300 dark:border-slate-600">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                            {{ $mapel->nama }} &bull; {{ $kelas->nama }}
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-0.5">
                            Periode:
                            <span class="font-bold text-slate-900 dark:text-slate-100">
                                {{ $selectedPeriode['tahun_ajaran'] }} - Semester {{ $selectedPeriode['semester'] }}
                            </span>
                            @if($selectedPeriode['tahun_ajaran'] === ($activePeriode['tahun_ajaran'] ?? '') && $selectedPeriode['semester'] === ($activePeriode['semester'] ?? ''))
                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-400 dark:border-emerald-500/50">
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
                            <select name="periode" class="w-full sm:w-56 border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 dark:[color-scheme:dark] focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 rounded-lg text-base sm:text-xs min-h-[44px] py-2 px-3 font-medium">
                                @foreach($daftarPeriode as $item)
                                    <option value="{{ $item['value'] }}" {{ $filterPeriode === $item['value'] ? 'selected' : '' }}>
                                        {{ $item['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama atau NIS siswa..." class="w-full sm:w-56 border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 placeholder:text-slate-500 dark:placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 rounded-lg text-base sm:text-xs min-h-[44px] py-2 px-3 font-medium">
                            <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950 font-bold py-2 px-5 rounded-lg text-xs uppercase tracking-wider min-h-[44px] transition text-center shadow-xs">
                                Cari
                            </button>
                            @if($search !== '' || (request()->filled('periode') && request('periode') !== ($activePeriode['tahun_ajaran'].'|'.$activePeriode['semester'])) || request()->filled('tahun_ajaran') || request()->filled('semester'))
                                <a href="{{ route('guru.riwayat.detail', ['kelas' => $kelas->id, 'mapel' => $mapel->id]) }}" class="border-2 border-slate-400 dark:border-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold py-2 px-4 rounded-lg text-xs uppercase tracking-wider min-h-[44px] flex items-center justify-center transition shadow-xs">
                                    Reset
                                </a>
                            @endif
                        </form>

                        {{-- Tombol Export Excel: membawa periode terpilih, TANPA parameter pencarian q --}}
                        <a href="{{ route('guru.laporan.export', ['kelas_mapel' => \App\Support\KelasMapel::make($kelas->id, $mapel->id), 'tahun_ajaran' => $selectedPeriode['tahun_ajaran'], 'semester' => $selectedPeriode['semester']]) }}"
                            class="inline-flex items-center justify-center gap-1.5 bg-emerald-700 hover:bg-emerald-800 text-white dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950 text-xs font-bold uppercase tracking-wider px-4 py-2.5 rounded-lg transition whitespace-nowrap min-h-[44px] shadow-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export Excel
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 shadow-xs rounded-xl overflow-hidden border-2 border-slate-300 dark:border-slate-600">
                @if($jumlahSesi === 0)
                    <div class="px-6 py-12 text-center text-slate-600 dark:text-slate-400">
                        <svg class="mx-auto h-12 w-12 mb-3 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="font-bold text-slate-900 dark:text-white">Belum ada pertemuan yang tercatat pada periode ini.</p>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">Sesi absensi akan tampil setelah guru melakukan absensi kelas.</p>
                    </div>
                @else
                    {{-- Petunjuk Geser di Mobile --}}
                    <div class="block sm:hidden px-4 py-2 bg-blue-100 dark:bg-blue-500/15 text-blue-900 dark:text-blue-300 text-xs font-semibold border-b-2 border-blue-400 dark:border-blue-500/50">
                        &larr; Geser tabel ke samping untuk melihat seluruh pertemuan &rarr;
                    </div>

                    {{-- Tabel Absensi Horizontal --}}
                    <div class="overflow-x-auto">
                        <table class="border-collapse text-sm w-full" style="min-width: max-content">
                            <thead>
                                {{-- Baris tanggal / nomor pertemuan --}}
                                <tr class="bg-slate-100 dark:bg-slate-800 border-b-2 border-slate-400 dark:border-slate-500">
                                    <th class="sticky left-0 z-20 bg-slate-100 dark:bg-slate-800 px-3 py-3 text-center font-bold text-slate-900 dark:text-white border-r-2 border-slate-300 dark:border-slate-600 w-12">No</th>
                                    <th class="sticky left-12 z-20 bg-slate-100 dark:bg-slate-800 px-3 py-3 text-left font-bold text-slate-900 dark:text-white border-r-2 border-slate-300 dark:border-slate-600 w-32">NIS</th>
                                    <th class="sticky left-[11rem] z-20 bg-slate-100 dark:bg-slate-800 px-4 py-3 text-left font-bold text-slate-900 dark:text-white border-r-2 border-slate-300 dark:border-slate-600 w-60 shadow-[3px_0_5px_-2px_rgba(0,0,0,0.15)]">Nama Siswa</th>
                                    @foreach($sesiList as $idx => $sesi)
                                        <th class="px-2 py-2 border-r-2 border-slate-200 dark:border-slate-700 text-center font-bold text-slate-700 dark:text-slate-200 w-16">
                                            <div class="text-xs font-extrabold text-blue-700 dark:text-blue-400">P{{ $idx + 1 }}</div>
                                            <div class="text-xs text-slate-600 dark:text-slate-300 font-semibold">{{ \Carbon\Carbon::parse($sesi->tanggal)->locale('id')->isoFormat('D MMM') }}</div>
                                            <div class="text-[11px] text-slate-600 dark:text-slate-400 font-normal">{{ substr(ucfirst(\Carbon\Carbon::parse($sesi->tanggal)->locale('id')->isoFormat('dddd')), 0, 3) }}</div>
                                        </th>
                                    @endforeach
                                    {{-- Kolom Ringkasan --}}
                                    <th class="px-2 py-3 border-r-2 border-slate-300 dark:border-slate-600 text-center font-extrabold text-emerald-900 dark:text-emerald-300 w-12 bg-emerald-100/70 dark:bg-emerald-500/20">H</th>
                                    <th class="px-2 py-3 border-r-2 border-slate-300 dark:border-slate-600 text-center font-extrabold text-blue-900 dark:text-blue-300 w-12 bg-blue-100/70 dark:bg-blue-500/20">I</th>
                                    <th class="px-2 py-3 border-r-2 border-slate-300 dark:border-slate-600 text-center font-extrabold text-amber-900 dark:text-amber-300 w-12 bg-amber-100/70 dark:bg-amber-500/20">S</th>
                                    <th class="px-2 py-3 border-r-2 border-slate-300 dark:border-slate-600 text-center font-extrabold text-red-900 dark:text-red-300 w-12 bg-red-100/70 dark:bg-red-500/20">A</th>
                                    <th class="px-3 py-3 text-center font-extrabold text-slate-900 dark:text-white w-20">%</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
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
                                            ? 'bg-red-50/70 dark:bg-red-950/40 hover:bg-red-100/70 dark:hover:bg-red-950/60' 
                                            : 'hover:bg-slate-50 dark:hover:bg-slate-800/60';
                                        $stickyBg = $isPerluPerhatian 
                                            ? 'bg-red-50 dark:bg-red-950' 
                                            : 'bg-white dark:bg-slate-900';
                                    @endphp
                                    <tr class="{{ $rowBg }} transition-colors">
                                        <td class="sticky left-0 z-10 {{ $stickyBg }} px-3 py-3 text-center font-semibold text-slate-600 dark:text-slate-300 border-r-2 border-slate-200 dark:border-slate-700 w-12">{{ $no + 1 }}</td>
                                        <td class="sticky left-12 z-10 {{ $stickyBg }} px-3 py-3 text-slate-700 dark:text-slate-200 border-r-2 border-slate-200 dark:border-slate-700 font-mono text-xs w-32 font-bold">{{ $siswa->nis }}</td>
                                        <td class="sticky left-[11rem] z-10 {{ $stickyBg }} px-4 py-3 font-bold text-slate-900 dark:text-slate-100 border-r-2 border-slate-300 dark:border-slate-600 w-60 shadow-[3px_0_5px_-2px_rgba(0,0,0,0.15)]">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span>{{ $siswa->user->name ?? $siswa->nama }}</span>
                                                @if($siswa->is_nonaktif)
                                                    <span class="text-xs text-slate-600 dark:text-slate-400 font-normal">(nonaktif)</span>
                                                @endif
                                                @if($isPerluPerhatian)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-900 border border-red-400 dark:bg-red-500/20 dark:text-red-300 dark:border-red-500/50 shadow-xs">Perlu perhatian</span>
                                                @endif
                                            </div>
                                        </td>
                                        @foreach($sesiList as $sesi)
                                            @php
                                                $status = $detailMap[$sesi->id][$siswa->id] ?? null;
                                                [$kode, $bgKelas] = match($status) {
                                                    'hadir' => ['H', 'bg-emerald-100 text-emerald-900 border border-emerald-400 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/50'],
                                                    'izin'  => ['I', 'bg-blue-100 text-blue-900 border border-blue-400 dark:bg-blue-500/15 dark:text-blue-300 dark:border-blue-500/50'],
                                                    'sakit' => ['S', 'bg-amber-100 text-amber-900 border border-amber-400 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/50'],
                                                    'alpa'  => ['A', 'bg-red-100 text-red-900 border border-red-400 dark:bg-red-500/15 dark:text-red-300 dark:border-red-500/50'],
                                                    default => ['·', 'text-slate-400 dark:text-slate-400'],
                                                };
                                            @endphp
                                            <td class="px-1 py-2 border-r-2 border-slate-200 dark:border-slate-700 w-16 text-center">
                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg font-extrabold text-xs {{ $bgKelas }} shadow-xs">{{ $kode }}</span>
                                            </td>
                                        @endforeach
                                        {{-- Ringkasan --}}
                                        <td class="px-2 py-3 text-center font-extrabold text-emerald-900 dark:text-emerald-300 border-r-2 border-slate-200 dark:border-slate-700 w-12 bg-emerald-100/50 dark:bg-emerald-500/15">{{ $h }}</td>
                                        <td class="px-2 py-3 text-center font-extrabold text-blue-900 dark:text-blue-300 border-r-2 border-slate-200 dark:border-slate-700 w-12 bg-blue-100/50 dark:bg-blue-500/15">{{ $i }}</td>
                                        <td class="px-2 py-3 text-center font-extrabold text-amber-900 dark:text-amber-300 border-r-2 border-slate-200 dark:border-slate-700 w-12 bg-amber-100/50 dark:bg-amber-500/15">{{ $s }}</td>
                                        <td class="px-2 py-3 text-center font-extrabold text-red-900 dark:text-red-300 border-r-2 border-slate-200 dark:border-slate-700 w-12 bg-red-100/50 dark:bg-red-500/15">{{ $a }}</td>
                                        <td class="px-3 py-3 text-center text-xs font-bold w-20 {{ $isPerluPerhatian ? 'text-red-700 dark:text-red-400 font-extrabold' : ($persen >= $batasRendah ? 'text-emerald-700 dark:text-emerald-400 font-extrabold' : 'text-amber-700 dark:text-amber-400 font-bold') }}">
                                            {{ $persen }}%
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 3 + $jumlahSesi + 5 }}" class="px-6 py-8 text-center text-slate-600 dark:text-slate-400">
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
                                @foreach(['hadir' => ['Hadir (H)', 'bg-emerald-100/70 dark:bg-emerald-500/15 text-emerald-900 dark:text-emerald-300', 'bg-emerald-100 dark:bg-slate-900'], 'izin' => ['Izin (I)', 'bg-blue-100/70 dark:bg-blue-500/15 text-blue-900 dark:text-blue-300', 'bg-blue-100 dark:bg-slate-900'], 'sakit' => ['Sakit (S)', 'bg-amber-100/70 dark:bg-amber-500/15 text-amber-900 dark:text-amber-300', 'bg-amber-100 dark:bg-slate-900'], 'alpa' => ['Alpa (A)', 'bg-red-100/70 dark:bg-red-500/15 text-red-900 dark:text-red-300', 'bg-red-100 dark:bg-slate-900']] as $st => $info)
                                    <tr class="{{ $info[1] }}">
                                        <td class="sticky left-0 z-10 {{ $info[2] }} px-3 py-2 border-t-2 border-r-2 border-slate-300 dark:border-slate-600 font-bold text-xs" colspan="3">
                                            <span>{{ $info[0] }}</span>
                                        </td>
                                        @foreach($sesiList as $sesi)
                                            @php
                                                $count = $sesi->detailAbsensi->where('status', \App\Enums\StatusKehadiran::from($st))->count();
                                            @endphp
                                            <td class="px-2 py-2 text-center text-xs font-extrabold border-t-2 border-r-2 border-slate-200 dark:border-slate-700">{{ $count ?: '-' }}</td>
                                        @endforeach
                                        <td colspan="5" class="border-t-2 border-slate-300 dark:border-slate-600"></td>
                                    </tr>
                                @endforeach
                            </tfoot>
                        </table>
                    </div>

                    {{-- Keterangan / Legend --}}
                    <div class="px-6 py-3 border-t-2 border-slate-300 dark:border-slate-600 text-xs text-slate-600 dark:text-slate-300 flex flex-wrap gap-4 items-center bg-slate-50 dark:bg-slate-800">
                        <span class="inline-flex items-center gap-1.5"><span class="w-5 h-5 rounded-md bg-emerald-100 text-emerald-900 border border-emerald-400 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/50 flex items-center justify-center font-bold text-[10px]">H</span> = Hadir</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-5 h-5 rounded-md bg-blue-100 text-blue-900 border border-blue-400 dark:bg-blue-500/15 dark:text-blue-300 dark:border-blue-500/50 flex items-center justify-center font-bold text-[10px]">I</span> = Izin</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-5 h-5 rounded-md bg-amber-100 text-amber-900 border border-amber-400 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/50 flex items-center justify-center font-bold text-[10px]">S</span> = Sakit</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-5 h-5 rounded-md bg-red-100 text-red-900 border border-red-400 dark:bg-red-500/15 dark:text-red-300 dark:border-red-500/50 flex items-center justify-center font-bold text-[10px]">A</span> = Alpa</span>
                        <span class="inline-flex items-center gap-1.5"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-900 border border-red-400 dark:bg-red-500/20 dark:text-red-300 dark:border-red-500/50">Perlu perhatian</span> = Kehadiran &lt; {{ config('absensi.batas_kehadiran_rendah', 75) }}%</span>
                        <span class="ml-auto">· = Belum diabsen / Bukan anggota</span>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
