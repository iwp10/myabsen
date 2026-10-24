<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
            <x-ui.back-button :href="route('guru.riwayat', ['tahun_ajaran' => $selectedPeriode['tahun_ajaran'], 'semester' => $selectedPeriode['semester']])" />
            <div class="min-w-0 flex-1">
                <h2 class="font-bold text-lg sm:text-xl text-slate-900 dark:text-white leading-tight truncate">
                    Riwayat Absensi: {{ $kelas->nama }} - {{ $mapel->nama }}
                </h2>
                <p class="text-xs text-slate-600 dark:text-slate-400 truncate">
                    Periode {{ $selectedPeriode['tahun_ajaran'] }} • Semester {{ $selectedPeriode['semester'] }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
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
                $batasRendah = (int) config('absensi.batas_kehadiran_rendah', 75);

                // Siapkan ringkasan per siswa
                $siswaProcessed = [];
                $totalHadirKelas = 0; $totalIzinKelas = 0; $totalSakitKelas = 0; $totalAlpaKelas = 0;

                foreach ($siswaList as $no => $siswa) {
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
                    $totalHadirKelas += $h;
                    $totalIzinKelas += $i;
                    $totalSakitKelas += $s;
                    $totalAlpaKelas += $a;

                    $persen = app(\App\Services\AbsensiService::class)->hitungPersentaseKehadiran($h, $i, $s, $total);
                    $isPerluPerhatian = ($total > 0 && $persen < $batasRendah);
                    $siswaProcessed[] = [
                        'no' => $no + 1,
                        'siswa' => $siswa,
                        'nama' => $siswa->user->name ?? $siswa->nama,
                        'nis' => $siswa->nis,
                        'is_nonaktif' => $siswa->is_nonaktif,
                        'h' => $h,
                        'i' => $i,
                        's' => $s,
                        'a' => $a,
                        'total' => $total,
                        'persen' => $persen,
                        'is_perlu_perhatian' => $isPerluPerhatian,
                    ];
                }
            @endphp

            <!-- Banner Header Kompak & Ringkasan Kelas -->
            <div class="bg-white dark:bg-slate-900 shadow-xs rounded-xl p-4 sm:p-6 border-2 border-slate-300 dark:border-slate-600 space-y-4">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">
                                {{ $mapel->nama }}
                            </h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-400 dark:border-emerald-500/50">
                                Kelas {{ $kelas->nama }}
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 font-medium mt-1">
                            <span>Tahun Ajaran {{ $selectedPeriode['tahun_ajaran'] }}</span> •
                            <span>Semester {{ $selectedPeriode['semester'] }}</span> •
                            <span>{{ $totalSiswaSebelumFilter }} siswa</span> •
                            <span>{{ $jumlahSesi }} pertemuan</span>
                        </p>
                    </div>

                    <!-- Ringkasan Akumulatif Kelas H/I/S/A -->
                    <div class="grid grid-cols-4 gap-2 sm:gap-3 text-center bg-slate-100 dark:bg-slate-800 p-2.5 sm:p-3 rounded-xl border-2 border-slate-300 dark:border-slate-600">
                        <div class="p-1 sm:p-2 rounded-lg bg-emerald-100 text-emerald-900 border border-emerald-400 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/50">
                            <span class="block text-[10px] font-bold uppercase tracking-wider">Hadir</span>
                            <span class="font-extrabold text-base sm:text-lg tabular-nums">{{ $totalHadirKelas }}</span>
                        </div>
                        <div class="p-1 sm:p-2 rounded-lg bg-blue-100 text-blue-900 border border-blue-400 dark:bg-blue-500/15 dark:text-blue-300 dark:border-blue-500/50">
                            <span class="block text-[10px] font-bold uppercase tracking-wider">Izin</span>
                            <span class="font-extrabold text-base sm:text-lg tabular-nums">{{ $totalIzinKelas }}</span>
                        </div>
                        <div class="p-1 sm:p-2 rounded-lg bg-amber-100 text-amber-900 border border-amber-400 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/50">
                            <span class="block text-[10px] font-bold uppercase tracking-wider">Sakit</span>
                            <span class="font-extrabold text-base sm:text-lg tabular-nums">{{ $totalSakitKelas }}</span>
                        </div>
                        <div class="p-1 sm:p-2 rounded-lg bg-red-100 text-red-900 border border-red-400 dark:bg-red-500/15 dark:text-red-300 dark:border-red-500/50">
                            <span class="block text-[10px] font-bold uppercase tracking-wider">Alpa</span>
                            <span class="font-extrabold text-base sm:text-lg tabular-nums">{{ $totalAlpaKelas }}</span>
                        </div>
                    </div>
                </div>

                <!-- Tombol Export Excel Lebar Penuh di Bawah Ringkasan (Membawa Periode, Tanpa q) -->
                <div>
                    <a href="{{ route('guru.laporan.export', ['kelas_mapel' => \App\Support\KelasMapel::make($kelas->id, $mapel->id), 'tahun_ajaran' => $selectedPeriode['tahun_ajaran'], 'semester' => $selectedPeriode['semester']]) }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-emerald-700 hover:bg-emerald-800 text-white dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950 text-xs font-bold uppercase tracking-wider px-4 py-2.5 rounded-xl transition min-h-[44px] shadow-xs">
                        <x-ui.icon name="file-text" size="sm" />
                        <span>Export Excel (Seluruh Siswa)</span>
                    </a>
                </div>
            </div>

            <!-- Bilah Pencarian Siswa & Filter Periode Melekat Saat Menggulir -->
            <div class="sticky top-16 z-20 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md p-3 sm:p-4 rounded-xl border-2 border-slate-300 dark:border-slate-600 shadow-md">
                <form action="{{ route('guru.riwayat.detail', ['kelas' => $kelas->id, 'mapel' => $mapel->id]) }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full">
                    <x-ui.select name="periode" wrapper-class="w-full sm:w-56">
                        @foreach($daftarPeriode as $item)
                            <option value="{{ $item['value'] }}" {{ $filterPeriode === $item['value'] ? 'selected' : '' }}>
                                {{ $item['label'] }}
                            </option>
                        @endforeach
                    </x-ui.select>

                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-600 dark:text-slate-400">
                            <x-ui.icon name="search" size="sm" class="w-4 h-4" />
                        </div>
                        <input type="text"
                               name="q"
                               value="{{ $search }}"
                               placeholder="Cari nama atau NIS siswa..."
                               class="w-full pl-9 pr-3 border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 placeholder:text-slate-500 dark:placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 rounded-xl text-base sm:text-xs min-h-[44px] py-2 font-medium">
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="flex-1 sm:flex-initial bg-emerald-700 hover:bg-emerald-800 text-white dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950 font-bold py-2 px-5 rounded-xl text-xs uppercase tracking-wider min-h-[44px] transition text-center shadow-xs">
                            Cari
                        </button>
                        @if($search !== '' || (request()->filled('periode') && request('periode') !== ($activePeriode['tahun_ajaran'].'|'.$activePeriode['semester'])) || request()->filled('tahun_ajaran') || request()->filled('semester'))
                            <a href="{{ route('guru.riwayat.detail', ['kelas' => $kelas->id, 'mapel' => $mapel->id]) }}" class="border-2 border-slate-400 dark:border-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold py-2 px-4 rounded-xl text-xs uppercase tracking-wider min-h-[44px] flex items-center justify-center transition shadow-xs">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Konten: Kartu Siswa di Mobile, Matriks di Desktop -->
            <div class="bg-white dark:bg-slate-900 shadow-xs rounded-xl overflow-hidden border-2 border-slate-300 dark:border-slate-600">
                @if($jumlahSesi === 0)
                    <div class="px-6 py-12 text-center text-slate-600 dark:text-slate-400">
                        <x-ui.icon name="calendar" size="lg" class="mx-auto mb-3 text-slate-600 dark:text-slate-400" />
                        <p class="font-bold text-slate-900 dark:text-white">Belum ada pertemuan yang tercatat pada periode ini.</p>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">Sesi absensi akan tampil setelah guru melakukan absensi kelas.</p>
                    </div>
                @else
                    {{-- 1. DAFTAR KARTU MOBILE (Hanya tampil di bawah breakpoint md) --}}
                    <div class="md:hidden p-3.5 space-y-3">
                        @forelse($siswaProcessed as $item)
                            <div class="bg-white dark:bg-slate-900 border-2 border-slate-300 dark:border-slate-600 rounded-xl p-4 shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5 space-y-3">
                                {{-- Baris Atas: Nama, NIS, Nonaktif, Persentase, Perlu Perhatian --}}
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-bold text-base text-slate-900 dark:text-white leading-snug break-words">
                                                {{ $item['nama'] }}
                                            </span>
                                            @if($item['is_nonaktif'])
                                                <span class="text-xs text-slate-600 dark:text-slate-400 font-normal">(nonaktif)</span>
                                            @endif
                                        </div>
                                        <p class="text-xs font-mono font-bold text-slate-600 dark:text-slate-400 mt-0.5">
                                            NIS: {{ $item['nis'] }}
                                        </p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="text-base font-extrabold {{ \App\Support\Ui::persenWarna($item['persen']) }} tabular-nums block">
                                            {{ $item['persen'] }}%
                                        </span>
                                        @if($item['is_perlu_perhatian'])
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-900 border border-red-400 dark:bg-red-500/20 dark:text-red-300 dark:border-red-500/50 shadow-xs mt-0.5">
                                                Perlu perhatian
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Ringkasan Jumlah H/I/S/A --}}
                                <div class="flex items-center gap-1.5 flex-wrap text-xs font-bold pt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-900 border border-emerald-400 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/50 tabular-nums">
                                        H: {{ $item['h'] }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-100 text-blue-900 border border-blue-400 dark:bg-blue-500/15 dark:text-blue-300 dark:border-blue-500/50 tabular-nums">
                                        I: {{ $item['i'] }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 border border-amber-400 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/50 tabular-nums">
                                        S: {{ $item['s'] }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-red-100 text-red-900 border border-red-400 dark:bg-red-500/15 dark:text-red-300 dark:border-red-500/50 tabular-nums">
                                        A: {{ $item['a'] }}
                                    </span>
                                </div>

                                {{-- Grid Kotak Pertemuan --}}
                                <div class="pt-2.5 border-t-2 border-slate-200 dark:border-slate-700">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200 block mb-2">Riwayat Pertemuan:</span>
                                    <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-2">
                                        @foreach($sesiList as $idx => $sesi)
                                            @php
                                                $st = $detailMap[$sesi->id][$item['siswa']->id] ?? null;
                                                $tglCarbon = \Carbon\Carbon::parse($sesi->tanggal);
                                            @endphp
                                            <x-ui.pertemuan-chip
                                                :p="$idx + 1"
                                                :tanggal="$tglCarbon"
                                                :status="$st"
                                            />
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-8 text-center text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-900 border-2 border-slate-300 dark:border-slate-600 rounded-xl">
                                @if($search !== '')
                                    Tidak ada siswa yang sesuai dengan pencarian "{{ $search }}".
                                @else
                                    Belum ada data siswa di kelas ini.
                                @endif
                            </div>
                        @endforelse
                    </div>

                    {{-- 2. TABEL MATRIKS DESKTOP (Tampil di md ke atas: >= 768px) --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="border-collapse text-sm w-full" style="min-width: max-content">
                            <thead>
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
                                    <th class="px-2 py-3 border-r-2 border-slate-300 dark:border-slate-600 text-center font-extrabold text-emerald-900 dark:text-emerald-300 w-12 bg-emerald-100/70 dark:bg-emerald-500/20">H</th>
                                    <th class="px-2 py-3 border-r-2 border-slate-300 dark:border-slate-600 text-center font-extrabold text-blue-900 dark:text-blue-300 w-12 bg-blue-100/70 dark:bg-blue-500/20">I</th>
                                    <th class="px-2 py-3 border-r-2 border-slate-300 dark:border-slate-600 text-center font-extrabold text-amber-900 dark:text-amber-300 w-12 bg-amber-100/70 dark:bg-amber-500/20">S</th>
                                    <th class="px-2 py-3 border-r-2 border-slate-300 dark:border-slate-600 text-center font-extrabold text-red-900 dark:text-red-300 w-12 bg-red-100/70 dark:bg-red-500/20">A</th>
                                    <th class="px-3 py-3 text-center font-extrabold text-slate-900 dark:text-white w-20">%</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                                @forelse($siswaProcessed as $item)
                                    @php
                                        $rowBg = $item['is_perlu_perhatian']
                                            ? 'bg-red-50/70 dark:bg-red-950/40 hover:bg-red-100/70 dark:hover:bg-red-950/60'
                                            : 'hover:bg-slate-50 dark:hover:bg-slate-800/60';
                                        $stickyBg = $item['is_perlu_perhatian']
                                            ? 'bg-red-50 dark:bg-red-950'
                                            : 'bg-white dark:bg-slate-900';
                                    @endphp
                                    <tr class="{{ $rowBg }} transition-colors">
                                        <td class="sticky left-0 z-10 {{ $stickyBg }} px-3 py-3 text-center font-semibold text-slate-600 dark:text-slate-300 border-r-2 border-slate-200 dark:border-slate-700 w-12">{{ $item['no'] }}</td>
                                        <td class="sticky left-12 z-10 {{ $stickyBg }} px-3 py-3 text-slate-700 dark:text-slate-200 border-r-2 border-slate-200 dark:border-slate-700 font-mono text-xs w-32 font-bold">{{ $item['nis'] }}</td>
                                        <td class="sticky left-[11rem] z-10 {{ $stickyBg }} px-4 py-3 font-bold text-slate-900 dark:text-slate-100 border-r-2 border-slate-300 dark:border-slate-600 w-60 shadow-[3px_0_5px_-2px_rgba(0,0,0,0.15)]">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span>{{ $item['nama'] }}</span>
                                                @if($item['is_nonaktif'])
                                                    <span class="text-xs text-slate-600 dark:text-slate-400 font-normal">(nonaktif)</span>
                                                @endif
                                                @if($item['is_perlu_perhatian'])
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-900 border border-red-400 dark:bg-red-500/20 dark:text-red-300 dark:border-red-500/50">
                                                        Perlu perhatian</span>
                                                @endif
                                            </div>
                                        </td>
                                        @foreach($sesiList as $sesi)
                                            @php
                                                $st = $detailMap[$sesi->id][$item['siswa']->id] ?? null;
                                                $chipClass = \App\Support\Ui::pertemuanChip($st);
                                                $huruf = \App\Support\Ui::statusHuruf($st);
                                                $label = \App\Support\Ui::statusLabel($st);
                                            @endphp
                                            <td class="px-2 py-3 border-r-2 border-slate-200 dark:border-slate-700 text-center">
                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-md font-extrabold text-xs {{ $chipClass }}" title="{{ $label }}">
                                                    {{ $huruf }}
                                                </span>
                                            </td>
                                        @endforeach
                                        <td class="px-2 py-3 border-r-2 border-slate-300 dark:border-slate-600 text-center font-bold text-emerald-900 dark:text-emerald-300 bg-emerald-50/50 dark:bg-emerald-500/10">{{ $item['h'] }}</td>
                                        <td class="px-2 py-3 border-r-2 border-slate-300 dark:border-slate-600 text-center font-bold text-blue-900 dark:text-blue-300 bg-blue-50/50 dark:bg-blue-500/10">{{ $item['i'] }}</td>
                                        <td class="px-2 py-3 border-r-2 border-slate-300 dark:border-slate-600 text-center font-bold text-amber-900 dark:text-amber-300 bg-amber-50/50 dark:bg-amber-500/10">{{ $item['s'] }}</td>
                                        <td class="px-2 py-3 border-r-2 border-slate-300 dark:border-slate-600 text-center font-bold text-red-900 dark:text-red-300 bg-red-50/50 dark:bg-red-500/10">{{ $item['a'] }}</td>
                                        <td class="px-3 py-3 text-center font-extrabold {{ \App\Support\Ui::persenWarna($item['persen']) }}">{{ $item['persen'] }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 4 + $jumlahSesi + 5 }}" class="px-6 py-8 text-center text-slate-600 dark:text-slate-400">
                                            @if($search !== '')
                                                Tidak ada siswa yang sesuai dengan pencarian "{{ $search }}".
                                            @else
                                                Belum ada data siswa di kelas ini.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
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
