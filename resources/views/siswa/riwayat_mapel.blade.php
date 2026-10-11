<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('siswa.riwayat', ['periode' => $filterPeriodeValue, 'tab' => 'per_mapel']) }}"
               class="text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white transition-colors"
               title="Kembali ke Riwayat">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M15 6l-6 6l6 6" />
                </svg>
            </a>
            <h2 class="font-bold text-xl text-gray-900 dark:text-gray-100 leading-tight">
                {{ $mapel->nama }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Tombol Navigasi Kembali & Pilihan Periode -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 sm:p-6 border-2 border-gray-300 dark:border-gray-600">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <a href="{{ route('siswa.riwayat', ['periode' => $filterPeriodeValue, 'tab' => 'per_mapel']) }}"
                               class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M15 6l-6 6l6 6" />
                                </svg>
                                Kembali ke Riwayat
                            </a>
                        </div>
                        <h3 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">
                            {{ $mapel->nama }}
                        </h3>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-600 dark:text-gray-300 mt-1">
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg>
                                Guru: <strong class="text-gray-900 dark:text-gray-100">{{ $guru }}</strong>
                            </span>
                            <span>&bull;</span>
                            <span>
                                Periode: <strong class="text-blue-600 dark:text-blue-400">{{ $selectedPeriode['tahun_ajaran'] }} - Semester {{ $selectedPeriode['semester'] }}</strong>
                                @if($selectedPeriode['tahun_ajaran'] === ($activePeriode['tahun_ajaran'] ?? '') && $selectedPeriode['semester'] === ($activePeriode['semester'] ?? ''))
                                    <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700">
                                        (aktif)
                                    </span>
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- Dropdown Periode untuk Mapel Ini -->
                    @if(count($daftarPeriodeMapel) > 1)
                        <form method="GET" action="{{ route('siswa.riwayat.mapel', $mapel) }}" class="flex items-center gap-2 w-full sm:w-auto">
                            <label for="periode_select" class="text-xs font-bold text-gray-700 dark:text-gray-300 whitespace-nowrap">Ganti Periode:</label>
                            <select id="periode_select" name="periode" onchange="this.form.submit()" class="w-full sm:w-60 border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 rounded-lg text-base sm:text-xs min-h-[44px] py-2 px-3 font-medium">
                                @foreach($daftarPeriodeMapel as $p)
                                    <option value="{{ $p['value'] }}" {{ $filterPeriodeValue === $p['value'] ? 'selected' : '' }}>
                                        {{ $p['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <noscript>
                                <button type="submit" class="bg-blue-600 text-white text-xs px-3 py-2 rounded-lg font-bold min-h-[44px]">Ubah</button>
                            </noscript>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Kartu Ringkasan Kehadiran Siswa -->
            @php
                $batasRendah = (int) config('absensi.batas_kehadiran_rendah', 75);
                $isMemenuhi = ($ringkasan['persentase'] >= $batasRendah);
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border-2 border-gray-300 dark:border-gray-600 {{ $isMemenuhi ? 'border-l-8 border-l-emerald-600' : 'border-l-8 border-l-red-600' }} shadow-sm">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Persentase</span>
                    <span class="text-3xl font-extrabold mt-1 block {{ \App\Support\Ui::persenWarna($ringkasan['persentase']) }}">
                        {{ $ringkasan['persentase'] }}%
                    </span>
                    <span class="text-xs font-bold {{ $isMemenuhi ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $isMemenuhi ? 'Memenuhi' : 'Di Bawah Target' }}
                    </span>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border-2 border-gray-300 dark:border-gray-600 border-l-8 border-l-blue-600 shadow-sm">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Total Pertemuan</span>
                    <span class="text-3xl font-extrabold text-gray-900 dark:text-gray-100 mt-1 block">
                        {{ $ringkasan['total_sesi'] }}
                    </span>
                    <span class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Sesi tercatat</span>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border-2 border-gray-300 dark:border-gray-600 border-l-8 border-l-emerald-600 shadow-sm">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 block">Hadir (H)</span>
                    <span class="text-3xl font-extrabold text-emerald-700 dark:text-emerald-400 mt-1 block">
                        {{ $ringkasan['total_hadir'] }}
                    </span>
                    <span class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Sesi</span>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border-2 border-gray-300 dark:border-gray-600 border-l-8 border-l-blue-600 shadow-sm">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-blue-600 dark:text-blue-400 block">Izin (I)</span>
                    <span class="text-3xl font-extrabold text-blue-600 dark:text-blue-400 mt-1 block">
                        {{ $ringkasan['total_izin'] }}
                    </span>
                    <span class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Sesi</span>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border-2 border-gray-300 dark:border-gray-600 border-l-8 border-l-amber-500 shadow-sm">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-600 dark:text-amber-400 block">Sakit (S)</span>
                    <span class="text-3xl font-extrabold text-amber-600 dark:text-amber-400 mt-1 block">
                        {{ $ringkasan['total_sakit'] }}
                    </span>
                    <span class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Sesi</span>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border-2 border-gray-300 dark:border-gray-600 border-l-8 border-l-red-600 shadow-sm">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-red-600 dark:text-red-400 block">Alpa (A)</span>
                    <span class="text-3xl font-extrabold {{ $ringkasan['total_alpa'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-700 dark:text-gray-300' }} mt-1 block">
                        {{ $ringkasan['total_alpa'] }}
                    </span>
                    <span class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Sesi</span>
                </div>
            </div>

            <!-- Matriks Absensi Pertemuan Siswa (Horizontal Seperti Riwayat Guru) -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden border-2 border-gray-300 dark:border-gray-600">
                <div class="p-5 sm:p-6 border-b-2 border-gray-300 dark:border-gray-600">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                        Matriks Presensi Pertemuan
                    </h3>
                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">
                        Rincian status kehadiran per pertemuan (P1 hingga P{{ $pertemuanList->count() }}).
                    </p>
                </div>

                {{-- Petunjuk Geser di Mobile --}}
                <div class="block sm:hidden px-4 py-2 bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 text-xs font-semibold border-b-2 border-blue-200 dark:border-blue-800">
                    &larr; Geser tabel ke samping untuk melihat seluruh pertemuan &rarr;
                </div>

                <div class="overflow-x-auto">
                    <table class="border-collapse text-sm w-full" style="min-width: max-content">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-800 border-b-2 border-gray-400 dark:border-gray-500">
                                <th class="sticky left-0 z-20 bg-gray-100 dark:bg-gray-800 px-3 py-3 text-center font-bold text-gray-800 dark:text-gray-200 border-r-2 border-gray-300 dark:border-gray-600 w-12">No</th>
                                <th class="sticky left-12 z-20 bg-gray-100 dark:bg-gray-800 px-3 py-3 text-left font-bold text-gray-800 dark:text-gray-200 border-r-2 border-gray-300 dark:border-gray-600 w-32">NIS</th>
                                <th class="sticky left-[11rem] z-20 bg-gray-100 dark:bg-gray-800 px-4 py-3 text-left font-bold text-gray-800 dark:text-gray-200 border-r-2 border-gray-300 dark:border-gray-600 w-60 shadow-[3px_0_5px_-2px_rgba(0,0,0,0.15)]">Nama Siswa</th>
                                @foreach($pertemuanList as $idx => $detail)
                                    <th class="px-2 py-2 border-r-2 border-gray-200 dark:border-gray-700 text-center font-bold text-gray-700 dark:text-gray-200 w-16">
                                        <div class="text-xs font-extrabold text-blue-600 dark:text-blue-400">P{{ $idx + 1 }}</div>
                                        <div class="text-xs text-gray-600 dark:text-gray-300 font-semibold">
                                            {{ \Carbon\Carbon::parse($detail->sesiAbsensi->tanggal)->locale('id')->isoFormat('D MMM') }}
                                        </div>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 font-normal">
                                            {{ substr(ucfirst(\Carbon\Carbon::parse($detail->sesiAbsensi->tanggal)->locale('id')->isoFormat('dddd')), 0, 3) }}
                                        </div>
                                    </th>
                                @endforeach
                                {{-- Kolom Ringkasan --}}
                                <th class="px-2 py-3 border-r-2 border-gray-300 dark:border-gray-600 text-center font-extrabold text-emerald-700 dark:text-emerald-400 w-12 bg-emerald-50/50 dark:bg-emerald-950/20">H</th>
                                <th class="px-2 py-3 border-r-2 border-gray-300 dark:border-gray-600 text-center font-extrabold text-blue-600 dark:text-blue-400 w-12 bg-blue-50/50 dark:bg-blue-950/20">I</th>
                                <th class="px-2 py-3 border-r-2 border-gray-300 dark:border-gray-600 text-center font-extrabold text-amber-600 dark:text-amber-400 w-12 bg-amber-50/50 dark:bg-amber-950/20">S</th>
                                <th class="px-2 py-3 border-r-2 border-gray-300 dark:border-gray-600 text-center font-extrabold text-red-600 dark:text-red-400 w-12 bg-red-50/50 dark:bg-red-950/20">A</th>
                                <th class="px-3 py-3 text-center font-extrabold text-gray-800 dark:text-gray-200 w-20">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-750 transition-colors">
                                <td class="sticky left-0 z-10 bg-white dark:bg-gray-800 px-3 py-3 text-center font-semibold text-gray-600 dark:text-gray-400 border-r-2 border-gray-200 dark:border-gray-700 w-12">1</td>
                                <td class="sticky left-12 z-10 bg-white dark:bg-gray-800 px-3 py-3 text-gray-700 dark:text-gray-300 border-r-2 border-gray-200 dark:border-gray-700 font-mono text-xs w-32 font-bold">{{ $siswa->nis }}</td>
                                <td class="sticky left-[11rem] z-10 bg-white dark:bg-gray-800 px-4 py-3 font-bold text-gray-900 dark:text-gray-100 border-r-2 border-gray-300 dark:border-gray-600 w-60 shadow-[3px_0_5px_-2px_rgba(0,0,0,0.15)]">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span>{{ $siswa->user->name ?? $siswa->nama }}</span>
                                    </div>
                                </td>
                                @foreach($pertemuanList as $idx => $detail)
                                    @php
                                        $statusStr = $detail->status instanceof \App\Enums\StatusKehadiran ? $detail->status->value : (string) $detail->status;
                                        [$kode, $bgKelas] = match($statusStr) {
                                            'hadir' => ['H', 'bg-emerald-700 text-white dark:bg-emerald-500 dark:text-gray-900'],
                                            'izin'  => ['I', 'bg-blue-600 text-white dark:bg-blue-400 dark:text-gray-900'],
                                            'sakit' => ['S', 'bg-amber-400 text-gray-900 dark:bg-amber-400 dark:text-gray-900'],
                                            'alpa'  => ['A', 'bg-red-600 text-white dark:bg-red-400 dark:text-gray-900'],
                                            default => ['·', 'text-gray-400 dark:text-gray-500'],
                                        };
                                        $tooltipKeterangan = $detail->keterangan ? ' - Ket: '.$detail->keterangan : '';
                                        $titleText = 'P'.($idx+1).': '.$kode.' ('.ucfirst($statusStr).')'.$tooltipKeterangan;
                                    @endphp
                                    <td class="px-1 py-2 border-r-2 border-gray-200 dark:border-gray-700 w-16 text-center" title="{{ $titleText }}">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg font-extrabold text-xs {{ $bgKelas }} shadow-xs">
                                            {{ $kode }}
                                        </span>
                                    </td>
                                @endforeach
                                {{-- Kolom Ringkasan --}}
                                <td class="px-2 py-3 text-center font-extrabold text-emerald-700 dark:text-emerald-400 border-r-2 border-gray-200 dark:border-gray-700 w-12 bg-emerald-50/30 dark:bg-emerald-950/10">{{ $ringkasan['total_hadir'] }}</td>
                                <td class="px-2 py-3 text-center font-extrabold text-blue-600 dark:text-blue-400 border-r-2 border-gray-200 dark:border-gray-700 w-12 bg-blue-50/30 dark:bg-blue-950/10">{{ $ringkasan['total_izin'] }}</td>
                                <td class="px-2 py-3 text-center font-extrabold text-amber-600 dark:text-amber-400 border-r-2 border-gray-200 dark:border-gray-700 w-12 bg-amber-50/30 dark:bg-amber-950/10">{{ $ringkasan['total_sakit'] }}</td>
                                <td class="px-2 py-3 text-center font-extrabold text-red-600 dark:text-red-400 border-r-2 border-gray-200 dark:border-gray-700 w-12 bg-red-50/30 dark:bg-red-950/10">{{ $ringkasan['total_alpa'] }}</td>
                                <td class="px-3 py-3 text-center text-xs font-bold w-20 {{ \App\Support\Ui::persenWarna($ringkasan['persentase']) }}">
                                    {{ $ringkasan['persentase'] }}%
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Keterangan Legenda Simbol --}}
                <div class="px-6 py-3 border-t-2 border-gray-300 dark:border-gray-600 text-xs text-gray-600 dark:text-gray-300 flex flex-wrap gap-4 items-center bg-gray-50/50 dark:bg-gray-850">
                    <span class="inline-flex items-center gap-1.5"><span class="w-4 h-4 rounded-md bg-emerald-700 text-white flex items-center justify-center font-bold text-[10px]">H</span> = Hadir</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-4 h-4 rounded-md bg-blue-600 text-white flex items-center justify-center font-bold text-[10px]">I</span> = Izin</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-4 h-4 rounded-md bg-amber-400 text-gray-900 flex items-center justify-center font-bold text-[10px]">S</span> = Sakit</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-4 h-4 rounded-md bg-red-600 text-white flex items-center justify-center font-bold text-[10px]">A</span> = Alpa</span>
                </div>
            </div>

            {{-- Catatan Keterangan Sesi (Jika ada keterangan izin/sakit/alpa) --}}
            @php
                $pertemuanDenganKeterangan = $pertemuanList->filter(function($d) {
                    return !empty(trim((string)$d->keterangan));
                });
            @endphp
            @if($pertemuanDenganKeterangan->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 sm:p-6 border-2 border-gray-300 dark:border-gray-600">
                    <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9h.01"/><path d="M11 12h1v4h1"/><path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z"/></svg>
                        Catatan & Keterangan Pertemuan
                    </h4>
                    <div class="divide-y-2 divide-gray-200 dark:divide-gray-700 text-xs">
                        @foreach($pertemuanDenganKeterangan as $det)
                            @php
                                $idxPertemuan = $pertemuanList->search(function($item) use ($det) { return $item->id === $det->id; });
                                $stValue = $det->status instanceof \App\Enums\StatusKehadiran ? $det->status->value : (string) $det->status;
                            @endphp
                            <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-gray-800 dark:text-gray-200">
                                        P{{ $idxPertemuan !== false ? $idxPertemuan + 1 : '-' }} ({{ \Carbon\Carbon::parse($det->sesiAbsensi->tanggal)->locale('id')->isoFormat('D MMMM Y') }})
                                    </span>
                                    <x-ui.status-badge :status="$stValue" />
                                </div>
                                <div class="text-gray-700 dark:text-gray-300 italic font-medium">
                                    "{{ $det->keterangan }}"
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
