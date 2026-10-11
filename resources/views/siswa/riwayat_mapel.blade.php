<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('siswa.riwayat', ['periode' => $filterPeriodeValue, 'tab' => 'per_mapel']) }}"
               class="text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition-colors"
               title="Kembali ke Riwayat">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M15 6l-6 6l6 6" />
                </svg>
            </a>
            <h2 class="font-bold text-xl text-slate-900 dark:text-white leading-tight">
                {{ $mapel->nama }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Tombol Navigasi Kembali & Pilihan Periode -->
            <div class="bg-white dark:bg-slate-900 shadow-xs rounded-xl p-5 sm:p-6 border-2 border-slate-300 dark:border-slate-600">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <a href="{{ route('siswa.riwayat', ['periode' => $filterPeriodeValue, 'tab' => 'per_mapel']) }}"
                               class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M15 6l-6 6l6 6" />
                                </svg>
                                Kembali ke Riwayat
                            </a>
                        </div>
                        <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white">
                            {{ $mapel->nama }}
                        </h3>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-600 dark:text-slate-300 mt-1">
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-600 dark:text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg>
                                Guru: <strong class="text-slate-900 dark:text-slate-100">{{ $guru }}</strong>
                            </span>
                            <span>&bull;</span>
                            <span class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                <span>Periode: <strong class="text-slate-900 dark:text-slate-100">{{ $selectedPeriode['tahun_ajaran'] }} - Semester {{ $selectedPeriode['semester'] }}</strong></span>
                                @if($selectedPeriode['tahun_ajaran'] === ($activePeriode['tahun_ajaran'] ?? '') && $selectedPeriode['semester'] === ($activePeriode['semester'] ?? ''))
                                    <span class="shrink-0 whitespace-nowrap inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-400 dark:border-emerald-500/50">
                                        (aktif)
                                    </span>
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- Dropdown Periode untuk Mapel Ini -->
                    @if(count($daftarPeriodeMapel) > 1)
                        <form method="GET" action="{{ route('siswa.riwayat.mapel', $mapel) }}" class="flex items-center gap-2 w-full sm:w-auto">
                            <label for="periode_select" class="text-xs font-bold text-slate-800 dark:text-slate-200 whitespace-nowrap">Ganti Periode:</label>
                            <x-ui.select id="periode_select" name="periode" onchange="this.form.submit()" wrapper-class="w-full sm:w-60">
                                @foreach($daftarPeriodeMapel as $p)
                                    <option value="{{ $p['value'] }}" {{ $filterPeriodeValue === $p['value'] ? 'selected' : '' }}>
                                        {{ $p['label'] }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                            <noscript>
                                <button type="submit" class="bg-emerald-700 dark:bg-emerald-400 text-white dark:text-slate-950 text-xs px-3 py-2 rounded-lg font-bold min-h-[44px]">Ubah</button>
                            </noscript>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Kartu Ringkasan Kehadiran Siswa (Strip Rekap H/I/S/A + Persentase & Total) -->
            @php
                $batasRendah = (int) config('absensi.batas_kehadiran_rendah', 75);
                $isMemenuhi = ($ringkasan['persentase'] >= $batasRendah);
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border-2 border-slate-300 dark:border-slate-600 {{ $isMemenuhi ? 'border-l-8 border-l-emerald-600 dark:border-l-emerald-400' : 'border-l-8 border-l-red-600 dark:border-l-red-400' }} shadow-xs dark:shadow-lg dark:shadow-black/40">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 block">Persentase</span>
                    <span class="text-2xl sm:text-3xl font-extrabold mt-1 block {{ \App\Support\Ui::persenWarna($ringkasan['persentase']) }}">
                        {{ $ringkasan['persentase'] }}%
                    </span>
                    <span class="text-xs font-bold {{ $isMemenuhi ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                        {{ $isMemenuhi ? 'Memenuhi' : 'Di Bawah Target' }}
                    </span>
                </div>
                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border-2 border-slate-300 dark:border-slate-600 border-l-8 border-l-slate-600 dark:border-l-slate-400 shadow-xs dark:shadow-lg dark:shadow-black/40">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 block">Total Pertemuan</span>
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-1 block">
                        {{ $ringkasan['total_sesi'] }}
                    </span>
                    <span class="text-xs text-slate-600 dark:text-slate-300 font-semibold">Sesi tercatat</span>
                </div>
                <div class="{{ \App\Support\Ui::rekapCard('hadir') }} p-4 rounded-xl">
                    <span class="{{ \App\Support\Ui::rekapLabel() }} block">Hadir (H)</span>
                    <span class="{{ \App\Support\Ui::rekapAngka('hadir') }} mt-1 block">
                        {{ $ringkasan['total_hadir'] }}
                    </span>
                    <span class="text-xs text-slate-700 dark:text-slate-300 font-semibold">Sesi</span>
                </div>
                <div class="{{ \App\Support\Ui::rekapCard('izin') }} p-4 rounded-xl">
                    <span class="{{ \App\Support\Ui::rekapLabel() }} block">Izin (I)</span>
                    <span class="{{ \App\Support\Ui::rekapAngka('izin') }} mt-1 block">
                        {{ $ringkasan['total_izin'] }}
                    </span>
                    <span class="text-xs text-slate-700 dark:text-slate-300 font-semibold">Sesi</span>
                </div>
                <div class="{{ \App\Support\Ui::rekapCard('sakit') }} p-4 rounded-xl">
                    <span class="{{ \App\Support\Ui::rekapLabel() }} block">Sakit (S)</span>
                    <span class="{{ \App\Support\Ui::rekapAngka('sakit') }} mt-1 block">
                        {{ $ringkasan['total_sakit'] }}
                    </span>
                    <span class="text-xs text-slate-700 dark:text-slate-300 font-semibold">Sesi</span>
                </div>
                <div class="{{ \App\Support\Ui::rekapCard('alpa') }} p-4 rounded-xl">
                    <span class="{{ \App\Support\Ui::rekapLabel() }} block">Alpa (A)</span>
                    <span class="{{ \App\Support\Ui::rekapAngka('alpa') }} mt-1 block">
                        {{ $ringkasan['total_alpa'] }}
                    </span>
                    <span class="text-xs text-slate-700 dark:text-slate-300 font-semibold">Sesi</span>
                </div>
            </div>

            <!-- Matriks Absensi Pertemuan Siswa (Horizontal Seperti Riwayat Guru) -->
            <div class="bg-white dark:bg-slate-900 shadow-xs rounded-xl overflow-hidden border-2 border-slate-300 dark:border-slate-600">
                <div class="p-5 sm:p-6 border-b-2 border-slate-300 dark:border-slate-600">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                        Matriks Presensi Pertemuan
                    </h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                        Rincian status kehadiran per pertemuan (P1 hingga P{{ $pertemuanList->count() }}).
                    </p>
                </div>

                {{-- 1. KARTU RINGKASAN & PERTEMUAN MOBILE (Hanya tampil di bawah breakpoint md: tanpa tabel dan tanpa geser horizontal) --}}
                <div class="md:hidden p-4 space-y-4">
                    <div class="bg-white dark:bg-slate-900 border-2 border-slate-300 dark:border-slate-600 rounded-xl p-4 shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <h4 class="font-bold text-base text-slate-900 dark:text-white leading-snug break-words">
                                    {{ $mapel->nama }}
                                </h4>
                                <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                                    Guru: <strong class="text-slate-900 dark:text-slate-100">{{ $guru }}</strong>
                                </p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="text-base font-extrabold {{ \App\Support\Ui::persenWarna($ringkasan['persentase']) }} tabular-nums block">
                                    {{ $ringkasan['persentase'] }}%
                                </span>
                                <span class="text-xs font-bold {{ $isMemenuhi ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                                    {{ $isMemenuhi ? 'Memenuhi' : 'Di Bawah Target' }}
                                </span>
                            </div>
                        </div>

                        {{-- Ringkasan Jumlah H/I/S/A --}}
                        <div class="flex items-center gap-1.5 flex-wrap text-xs font-bold pt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-900 border border-emerald-400 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/50 tabular-nums">
                                H: {{ $ringkasan['total_hadir'] }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-100 text-blue-900 border border-blue-400 dark:bg-blue-500/15 dark:text-blue-300 dark:border-blue-500/50 tabular-nums">
                                I: {{ $ringkasan['total_izin'] }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 border border-amber-400 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/50 tabular-nums">
                                S: {{ $ringkasan['total_sakit'] }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-red-100 text-red-900 border border-red-400 dark:bg-red-500/15 dark:text-red-300 dark:border-red-500/50 tabular-nums">
                                A: {{ $ringkasan['total_alpa'] }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-800 border border-slate-400 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-600 tabular-nums">
                                Total: {{ $ringkasan['total_sesi'] }} Sesi
                            </span>
                        </div>

                        {{-- Grid Kotak Pertemuan --}}
                        <div class="pt-3 border-t-2 border-slate-200 dark:border-slate-700">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-200 block mb-2">Riwayat Pertemuan:</span>
                            <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-2">
                                @foreach($pertemuanList as $idx => $detail)
                                    @php
                                        $statusStr = $detail->status instanceof \App\Enums\StatusKehadiran ? $detail->status->value : (string) $detail->status;
                                        $tglCarbon = \Carbon\Carbon::parse($detail->sesiAbsensi->tanggal);
                                    @endphp
                                    <x-ui.pertemuan-chip
                                        :p="$idx + 1"
                                        :tanggal="$tglCarbon"
                                        :status="$statusStr"
                                        :keterangan="$detail->keterangan"
                                    />
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. TABEL MATRIKS DESKTOP (Tampil di md ke atas: >= 768px) --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="border-collapse text-sm w-full" style="min-width: max-content">
                        <thead>
                            <tr class="bg-slate-100 dark:bg-slate-800 border-b-2 border-slate-400 dark:border-slate-500">
                                <th class="sticky left-0 z-20 bg-slate-100 dark:bg-slate-800 px-3 py-3 text-center font-bold text-slate-900 dark:text-white border-r-2 border-slate-300 dark:border-slate-600 w-12">No</th>
                                <th class="sticky left-12 z-20 bg-slate-100 dark:bg-slate-800 px-3 py-3 text-left font-bold text-slate-900 dark:text-white border-r-2 border-slate-300 dark:border-slate-600 w-32">NIS</th>
                                <th class="sticky left-[11rem] z-20 bg-slate-100 dark:bg-slate-800 px-4 py-3 text-left font-bold text-slate-900 dark:text-white border-r-2 border-slate-300 dark:border-slate-600 w-60 shadow-[3px_0_5px_-2px_rgba(0,0,0,0.15)]">Nama Siswa</th>
                                @foreach($pertemuanList as $idx => $detail)
                                    <th class="px-2 py-2 border-r-2 border-slate-200 dark:border-slate-700 text-center font-bold text-slate-700 dark:text-slate-200 w-16">
                                        <div class="text-xs font-extrabold text-blue-700 dark:text-blue-400">P{{ $idx + 1 }}</div>
                                        <div class="text-xs text-slate-600 dark:text-slate-300 font-semibold">
                                            {{ \Carbon\Carbon::parse($detail->sesiAbsensi->tanggal)->locale('id')->isoFormat('D MMM') }}
                                        </div>
                                        <div class="text-[11px] text-slate-600 dark:text-slate-400 font-normal">
                                            {{ substr(ucfirst(\Carbon\Carbon::parse($detail->sesiAbsensi->tanggal)->locale('id')->isoFormat('dddd')), 0, 3) }}
                                        </div>
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
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                                <td class="sticky left-0 z-10 bg-white dark:bg-slate-900 px-3 py-3 text-center font-semibold text-slate-600 dark:text-slate-300 border-r-2 border-slate-200 dark:border-slate-700 w-12">1</td>
                                <td class="sticky left-12 z-10 bg-white dark:bg-slate-900 px-3 py-3 text-slate-700 dark:text-slate-200 border-r-2 border-slate-200 dark:border-slate-700 font-mono text-xs w-32 font-bold">{{ $siswa->nis }}</td>
                                <td class="sticky left-[11rem] z-10 bg-white dark:bg-slate-900 px-4 py-3 font-bold text-slate-900 dark:text-slate-100 border-r-2 border-slate-300 dark:border-slate-600 w-60 shadow-[3px_0_5px_-2px_rgba(0,0,0,0.15)]">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span>{{ $siswa->user->name ?? $siswa->nama }}</span>
                                    </div>
                                </td>
                                @foreach($pertemuanList as $idx => $detail)
                                    @php
                                        $statusStr = $detail->status instanceof \App\Enums\StatusKehadiran ? $detail->status->value : (string) $detail->status;
                                        [$kode, $bgKelas] = match($statusStr) {
                                            'hadir' => ['H', 'bg-emerald-100 text-emerald-900 border border-emerald-400 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/50'],
                                            'izin'  => ['I', 'bg-blue-100 text-blue-900 border border-blue-400 dark:bg-blue-500/15 dark:text-blue-300 dark:border-blue-500/50'],
                                            'sakit' => ['S', 'bg-amber-100 text-amber-900 border border-amber-400 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/50'],
                                            'alpa'  => ['A', 'bg-red-100 text-red-900 border border-red-400 dark:bg-red-500/15 dark:text-red-300 dark:border-red-500/50'],
                                            default => ['·', 'text-slate-400 dark:text-slate-400'],
                                        };
                                        $tooltipKeterangan = $detail->keterangan ? ' - Ket: '.$detail->keterangan : '';
                                        $titleText = 'P'.($idx+1).': '.$kode.' ('.ucfirst($statusStr).')'.$tooltipKeterangan;
                                    @endphp
                                    <td class="px-1 py-2 border-r-2 border-slate-200 dark:border-slate-700 w-16 text-center" title="{{ $titleText }}">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg font-extrabold text-xs {{ $bgKelas }} shadow-xs">
                                            {{ $kode }}
                                        </span>
                                    </td>
                                @endforeach
                                {{-- Kolom Ringkasan --}}
                                <td class="px-2 py-3 text-center font-extrabold text-emerald-900 dark:text-emerald-300 border-r-2 border-slate-200 dark:border-slate-700 w-12 bg-emerald-100/50 dark:bg-emerald-500/15">{{ $ringkasan['total_hadir'] }}</td>
                                <td class="px-2 py-3 text-center font-extrabold text-blue-900 dark:text-blue-300 border-r-2 border-slate-200 dark:border-slate-700 w-12 bg-blue-100/50 dark:bg-blue-500/15">{{ $ringkasan['total_izin'] }}</td>
                                <td class="px-2 py-3 text-center font-extrabold text-amber-900 dark:text-amber-300 border-r-2 border-slate-200 dark:border-slate-700 w-12 bg-amber-100/50 dark:bg-amber-500/15">{{ $ringkasan['total_sakit'] }}</td>
                                <td class="px-2 py-3 text-center font-extrabold text-red-900 dark:text-red-300 border-r-2 border-slate-200 dark:border-slate-700 w-12 bg-red-100/50 dark:bg-red-500/15">{{ $ringkasan['total_alpa'] }}</td>
                                <td class="px-3 py-3 text-center text-xs font-extrabold w-20 {{ \App\Support\Ui::persenWarna($ringkasan['persentase']) }}">
                                    {{ $ringkasan['persentase'] }}%
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Keterangan Legenda Simbol --}}
                <div class="px-6 py-3 border-t-2 border-slate-300 dark:border-slate-600 text-xs text-slate-600 dark:text-slate-300 flex flex-wrap gap-4 items-center bg-slate-50 dark:bg-slate-800">
                    <span class="inline-flex items-center gap-1.5"><span class="w-5 h-5 rounded-md bg-emerald-100 text-emerald-900 border border-emerald-400 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/50 flex items-center justify-center font-bold text-[10px]">H</span> = Hadir</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-5 h-5 rounded-md bg-blue-100 text-blue-900 border border-blue-400 dark:bg-blue-500/15 dark:text-blue-300 dark:border-blue-500/50 flex items-center justify-center font-bold text-[10px]">I</span> = Izin</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-5 h-5 rounded-md bg-amber-100 text-amber-900 border border-amber-400 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/50 flex items-center justify-center font-bold text-[10px]">S</span> = Sakit</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-5 h-5 rounded-md bg-red-100 text-red-900 border border-red-400 dark:bg-red-500/15 dark:text-red-300 dark:border-red-500/50 flex items-center justify-center font-bold text-[10px]">A</span> = Alpa</span>
                </div>
            </div>

            {{-- Catatan Keterangan Sesi (Jika ada keterangan izin/sakit/alpa) --}}
            @php
                $pertemuanDenganKeterangan = $pertemuanList->filter(function($d) {
                    return !empty(trim((string)$d->keterangan));
                });
            @endphp
            @if($pertemuanDenganKeterangan->isNotEmpty())
                <div class="bg-white dark:bg-slate-900 shadow-xs rounded-xl p-5 sm:p-6 border-2 border-slate-300 dark:border-slate-600">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-3 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9h.01"/><path d="M11 12h1v4h1"/><path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z"/></svg>
                        Catatan & Keterangan Pertemuan
                    </h4>
                    <div class="divide-y-2 divide-slate-200 dark:divide-slate-700 text-xs">
                        @foreach($pertemuanDenganKeterangan as $det)
                            @php
                                $idxPertemuan = $pertemuanList->search(function($item) use ($det) { return $item->id === $det->id; });
                                $stValue = $det->status instanceof \App\Enums\StatusKehadiran ? $det->status->value : (string) $det->status;
                            @endphp
                            <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-800 dark:text-slate-200">
                                        P{{ $idxPertemuan !== false ? $idxPertemuan + 1 : '-' }} ({{ \Carbon\Carbon::parse($det->sesiAbsensi->tanggal)->locale('id')->isoFormat('D MMMM Y') }})
                                    </span>
                                    <x-ui.status-badge :status="$stValue" />
                                </div>
                                <div class="text-slate-700 dark:text-slate-200 italic font-medium">
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
