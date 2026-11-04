<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 dark:text-white leading-tight">
            {{ __('Dashboard Siswa') }}
        </h2>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            @if (session('status') || session('success'))
                <x-alert type="success" :autoDismiss="true">
                    {{ session('status') ?: session('success') }}
                </x-alert>
            @endif

            <!-- Banner Sapaan & Identitas Siswa Kompak -->
            <div class="bg-blue-600 dark:bg-blue-700 rounded-2xl p-4 sm:p-6 text-white shadow-md relative overflow-hidden border-2 border-blue-700 dark:border-blue-600">
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white mb-1.5">
                            SMK Mandiri 02 Balaraja
                        </span>
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Halo, {{ Auth::user()->name }}!</h1>
                        <p class="text-white/90 text-xs sm:text-sm mt-0.5">
                            Kelas: <strong class="text-white font-semibold">{{ Auth::user()->siswa?->kelas?->nama ?? '-' }}</strong> &bull;
                            NIS: <strong class="text-white font-semibold">{{ Auth::user()->siswa?->nis ?? Auth::user()->username }}</strong>
                        </p>
                    </div>
                    <div class="flex items-center justify-between sm:flex-col sm:items-end pt-2 sm:pt-0 border-t border-white/20 sm:border-0">
                        <span class="text-xs text-white/80 uppercase font-medium tracking-wider">Tanggal Hari Ini</span>
                        <span class="text-xs sm:text-sm font-bold text-white">
                            {{ \Illuminate\Support\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- 1. JADWAL & STATUS HARI INI (TAMPIL DI PALING ATAS PADA LAYAR PERTAMA) -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border-2 border-slate-300 dark:border-slate-600 overflow-hidden">
                <div class="p-4 sm:p-5 border-b-2 border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <x-ui.icon name="calendar" size="md" class="text-emerald-700 dark:text-emerald-400" />
                            <span>Jadwal &amp; Status Hari Ini</span>
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                            Status absensi per mata pelajaran pada hari ini ({{ \Illuminate\Support\Carbon::now()->locale('id')->isoFormat('dddd') }})
                        </p>
                    </div>
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 px-2.5 py-1 rounded-lg">
                        {{ $statusHariIni->count() }} Mapel
                    </span>
                </div>

                <div class="p-4 sm:p-5">
                    @if($statusHariIni->isEmpty())
                        <div class="py-8 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 mb-3 border border-slate-300 dark:border-slate-600">
                                <x-ui.icon name="clock" size="lg" />
                            </div>
                            <p class="text-base font-bold text-slate-900 dark:text-white">Tidak ada jadwal mata pelajaran hari ini</p>
                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1">Anda tidak memiliki jadwal pembelajaran yang perlu diabsen untuk hari ini.</p>
                        </div>
                    @else
                        {{-- Kartu Mobile (< md) --}}
                        <div class="md:hidden space-y-3">
                            @foreach($statusHariIni as $item)
                                <div class="bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-xl p-4 shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5 space-y-2">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs font-mono font-bold bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 py-1 px-2.5 rounded-md">
                                            {{ substr($item['jadwal']->jam_mulai, 0, 5) }} - {{ substr($item['jadwal']->jam_selesai, 0, 5) }} WIB
                                        </span>
                                        <x-ui.status-badge :status="$item['status_value']" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-base text-slate-900 dark:text-white leading-snug">
                                            {{ $item['jadwal']->mapel->nama }}
                                        </h4>
                                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 mt-0.5">
                                            Guru: <strong class="text-slate-800 dark:text-slate-200">{{ $item['jadwal']->guru->user->name }}</strong>
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Tabel Desktop (>= md) --}}
                        <div class="hidden md:block overflow-x-auto rounded-lg border-2 border-slate-300 dark:border-slate-600">
                            <table class="min-w-full divide-y-2 divide-slate-200 dark:divide-slate-700">
                                <thead class="bg-slate-100 dark:bg-slate-800 border-b-2 border-slate-400 dark:border-slate-500">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Jam</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Mata Pelajaran</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Guru</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Status Kehadiran</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-slate-900 divide-y-2 divide-slate-200 dark:divide-slate-700">
                                    @foreach($statusHariIni as $item)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-slate-100 font-medium tabular-nums">
                                                {{ substr($item['jadwal']->jam_mulai, 0, 5) }} - {{ substr($item['jadwal']->jam_selesai, 0, 5) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white font-semibold">
                                                {{ $item['jadwal']->mapel->nama }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                                                {{ $item['jadwal']->guru->user->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <x-ui.status-badge :status="$item['status_value']" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 2. BELUM DIABSEN (7 HARI TERAKHIR) -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border-2 border-slate-300 dark:border-slate-600 overflow-hidden">
                <div class="p-4 sm:p-5 border-b-2 border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <x-ui.icon name="clock" size="md" class="text-amber-600 dark:text-amber-400" />
                            <span>Belum Diabsen (7 Hari Terakhir)</span>
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                            Guru belum mencatat kehadiran. Ini bukan berarti kamu alpa.
                        </p>
                    </div>
                    @if($belumDiabsen['total'] > 0)
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 px-2.5 py-1 rounded-lg shrink-0">
                            {{ $belumDiabsen['total'] }} Pertemuan
                        </span>
                    @endif
                </div>

                <div class="p-4 sm:p-5">
                    @if($belumDiabsen['total'] === 0)
                        <div class="py-8 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 mb-3 border border-emerald-300 dark:border-emerald-700">
                                <x-ui.icon name="check" size="lg" />
                            </div>
                            <p class="text-base font-bold text-slate-900 dark:text-white">Semua pertemuan 7 hari terakhir sudah dicatat.</p>
                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1">Tidak ada jadwal pembelajaran dalam 7 hari terakhir yang terlewat atau belum dicatat oleh bapak/ibu guru.</p>
                        </div>
                    @else
                        {{-- Kartu Mobile (< md) --}}
                        <div class="md:hidden space-y-3">
                            @foreach($belumDiabsen['items'] as $item)
                                <div class="bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-xl p-4 shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5 space-y-2">
                                    <div class="flex items-center justify-between gap-2 flex-wrap">
                                        <span class="text-xs font-mono font-bold bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 py-1 px-2.5 rounded-md">
                                            {{ $item->jam_mulai }} - {{ $item->jam_selesai }} WIB
                                        </span>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-800 border-2 border-dashed border-slate-400 dark:bg-slate-700/50 dark:text-slate-200 dark:border-slate-400 shrink-0">
                                            <x-ui.icon name="clock" size="sm" class="shrink-0" />
                                            <span>Belum dicatat guru</span>
                                        </span>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-base text-slate-900 dark:text-white leading-snug">
                                            {{ $item->mapel_nama }}
                                        </h4>
                                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 mt-0.5">
                                            Guru: <strong class="text-slate-800 dark:text-slate-200">{{ $item->guru_nama }}</strong>
                                        </p>
                                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 mt-1 flex items-center gap-1">
                                            <x-ui.icon name="calendar" size="sm" class="text-slate-600 dark:text-slate-400 shrink-0" />
                                            <span>{{ $item->tanggal_label }}</span>
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Tabel Desktop (>= md) --}}
                        <div class="hidden md:block overflow-x-auto rounded-lg border-2 border-slate-300 dark:border-slate-600">
                            <table class="min-w-full divide-y-2 divide-slate-200 dark:divide-slate-700">
                                <thead class="bg-slate-100 dark:bg-slate-800 border-b-2 border-slate-400 dark:border-slate-500">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Tanggal &amp; Hari</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Jam</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Mata Pelajaran</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Guru</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-slate-900 divide-y-2 divide-slate-200 dark:divide-slate-700">
                                    @foreach($belumDiabsen['items'] as $item)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-slate-100 font-semibold">
                                                {{ $item->tanggal_label }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-slate-100 font-mono font-medium tabular-nums">
                                                {{ $item->jam_mulai }} - {{ $item->jam_selesai }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white font-semibold">
                                                {{ $item->mapel_nama }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                                                {{ $item->guru_nama }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-800 border-2 border-dashed border-slate-400 dark:bg-slate-700/50 dark:text-slate-200 dark:border-slate-400">
                                                    <x-ui.icon name="clock" size="sm" class="shrink-0" />
                                                    <span>Belum dicatat guru</span>
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($belumDiabsen['sisa'] > 0)
                            <div class="mt-3 p-3 text-center text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800/60 rounded-xl border border-slate-300 dark:border-slate-700">
                                <span>dan {{ $belumDiabsen['sisa'] }} lainnya</span>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- 3. DUA KARTU: RINGKASAN MINGGU INI & PERLU PERHATIAN -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                <!-- KARTU A: RINGKASAN MINGGU INI -->
                <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border-2 border-slate-300 dark:border-slate-600 overflow-hidden flex flex-col justify-between">
                    <div class="p-4 sm:p-5 border-b-2 border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <x-ui.icon name="calendar" size="md" class="text-blue-600 dark:text-blue-400" />
                                <span>Ringkasan Minggu Ini</span>
                            </h3>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                                Catatan kehadiran 7 hari terakhir ({{ $ringkasanMingguIni['rentang_label'] }})
                            </p>
                        </div>
                    </div>

                    <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between">
                        @if(! $ringkasanMingguIni['has_data'])
                            <div class="py-8 text-center my-auto">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 mb-3 border border-slate-300 dark:border-slate-600">
                                    <x-ui.icon name="clock" size="lg" />
                                </div>
                                <p class="text-base font-bold text-slate-900 dark:text-white">Belum ada catatan minggu ini</p>
                                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1">Belum ada data kehadiran yang tercatat untukmu dalam 7 hari terakhir.</p>
                            </div>
                        @else
                            <div class="space-y-4">
                                <div class="flex items-baseline justify-between gap-2 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 block">
                                            Persentase Minggu Ini
                                        </span>
                                        <span class="text-xs text-slate-600 dark:text-slate-400 mt-0.5 block">
                                            dari {{ $ringkasanMingguIni['total_sesi'] }} pertemuan
                                        </span>
                                    </div>
                                    <span class="text-3xl sm:text-4xl font-extrabold tracking-tight {{ \App\Support\Ui::persenWarna($ringkasanMingguIni['persentase']) }}">
                                        {{ $ringkasanMingguIni['persentase'] }}%
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                    <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-300 dark:border-emerald-500/40 flex flex-col items-center text-center">
                                        <span class="text-xs font-bold text-emerald-900 dark:text-emerald-300 flex items-center gap-1">
                                            <x-ui.icon name="check" size="sm" />
                                            <span>Hadir</span>
                                        </span>
                                        <span class="text-xl font-extrabold text-emerald-800 dark:text-emerald-200 mt-1">
                                            {{ $ringkasanMingguIni['total_hadir'] }}
                                        </span>
                                    </div>

                                    <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-500/10 border border-blue-300 dark:border-blue-500/40 flex flex-col items-center text-center">
                                        <span class="text-xs font-bold text-blue-900 dark:text-blue-300 flex items-center gap-1">
                                            <x-ui.icon name="info" size="sm" />
                                            <span>Izin</span>
                                        </span>
                                        <span class="text-xl font-extrabold text-blue-800 dark:text-blue-200 mt-1">
                                            {{ $ringkasanMingguIni['total_izin'] }}
                                        </span>
                                    </div>

                                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-300 dark:border-amber-500/40 flex flex-col items-center text-center">
                                        <span class="text-xs font-bold text-amber-900 dark:text-amber-300 flex items-center gap-1">
                                            <x-ui.icon name="plus" size="sm" />
                                            <span>Sakit</span>
                                        </span>
                                        <span class="text-xl font-extrabold text-amber-800 dark:text-amber-200 mt-1">
                                            {{ $ringkasanMingguIni['total_sakit'] }}
                                        </span>
                                    </div>

                                    <div class="p-3 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-300 dark:border-red-500/40 flex flex-col items-center text-center">
                                        <span class="text-xs font-bold text-red-900 dark:text-red-300 flex items-center gap-1">
                                            <x-ui.icon name="x" size="sm" />
                                            <span>Alpa</span>
                                        </span>
                                        <span class="text-xl font-extrabold text-red-800 dark:text-red-200 mt-1">
                                            {{ $ringkasanMingguIni['total_alpa'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- KARTU B: PERLU PERHATIAN -->
                <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border-2 border-slate-300 dark:border-slate-600 overflow-hidden flex flex-col justify-between">
                    <div class="p-4 sm:p-5 border-b-2 border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <x-ui.icon name="info" size="md" class="text-amber-600 dark:text-amber-400" />
                                <span>Perlu Perhatian</span>
                            </h3>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                                Mapel dengan persentase di bawah {{ config('absensi.batas_kehadiran_rendah', 75) }}%
                            </p>
                        </div>
                    </div>

                    <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between">
                        @if($mapelPerluPerhatian->isEmpty())
                            <div class="py-8 text-center my-auto">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 mb-3 border border-emerald-300 dark:border-emerald-700">
                                    <x-ui.icon name="check" size="lg" />
                                </div>
                                <p class="text-base font-bold text-slate-900 dark:text-white">Semua mata pelajaran aman.</p>
                                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1">Kehadiranmu pada seluruh mata pelajaran telah memenuhi batas target minimal sekolah.</p>
                            </div>
                        @else
                            <div class="divide-y-2 divide-slate-200 dark:divide-slate-700">
                                @foreach($mapelPerluPerhatian as $item)
                                    <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <h4 class="font-bold text-sm text-slate-900 dark:text-white truncate">
                                                {{ $item->mapel }}
                                            </h4>
                                            <p class="text-xs text-slate-600 dark:text-slate-300 truncate">
                                                Guru: <strong class="text-slate-800 dark:text-slate-200">{{ $item->guru }}</strong> &bull; Total {{ $item->total_sesi }} Sesi
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span class="text-base font-extrabold {{ \App\Support\Ui::persenWarna($item->persentase) }}">
                                                {{ $item->persentase }}%
                                            </span>
                                            <a href="{{ route('siswa.riwayat.mapel', ['mapel' => $item->id, 'periode' => $filterPeriodeValue]) }}"
                                               class="inline-flex items-center justify-center min-h-[44px] px-2.5 py-1 text-xs font-bold text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 hover:underline">
                                                <span>Detail &rarr;</span>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 4. RINGKASAN & BREAKDOWN KEHADIRAN (PERSENTASE MENONJOL & 4 KARTU 2 KOLOM DI HP) -->
            @php
                $persentase = $ringkasanKehadiran['persentase'] ?? 0;
                $totalSesi = $ringkasanKehadiran['total_sesi'] ?? 0;
                $isTargetReached = $persentase >= 80;
            @endphp

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <x-ui.icon name="chart-bar" size="md" class="text-emerald-700 dark:text-emerald-400" />
                        <span>Rincian &amp; Persentase Kehadiran</span>
                    </h3>
                    <a href="{{ route('siswa.riwayat') }}" class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 hover:underline flex items-center gap-1 min-h-[44px]">
                        <span>Lihat Riwayat Lengkap &rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-4">
                    <!-- Kartu Persentase Utama Menonjol -->
                    <div class="lg:col-span-4 bg-white dark:bg-slate-900 rounded-xl p-4 sm:p-5 shadow-xs border-2 border-slate-300 dark:border-slate-600 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                    Total Persentase Kehadiran
                                </span>
                                @if($totalSesi > 0)
                                    <span class="{{ \App\Support\Ui::persenBadge($persentase) }}">
                                        {{ $persentase >= config('absensi.batas_kehadiran_rendah', 75) ? 'Memenuhi Target' : 'Di Bawah Target' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600">
                                        Belum Ada Data
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3 flex items-baseline gap-2">
                                <span class="text-4xl font-extrabold tracking-tight {{ $totalSesi > 0 ? \App\Support\Ui::persenWarna($persentase) : 'text-slate-700 dark:text-slate-300' }}">
                                    {{ $persentase }}%
                                </span>
                                <span class="text-xs text-slate-600 dark:text-slate-400">
                                    dari {{ $totalSesi }} total pertemuan
                                </span>
                            </div>

                            <!-- Progress Bar Persentase -->
                            <div class="w-full bg-slate-200 dark:bg-slate-700 h-2.5 rounded-full mt-3 overflow-hidden">
                                <div class="h-2.5 rounded-full transition-all duration-500 {{ $totalSesi > 0 ? ($persentase >= config('absensi.batas_kehadiran_rendah', 75) ? 'bg-emerald-600 dark:bg-emerald-400' : 'bg-red-600 dark:bg-red-400') : 'bg-slate-400' }}"
                                     style="width: {{ min(100, max(0, $persentase)) }}%;">
                                </div>
                            </div>
                        </div>

                        <!-- Keterangan Rumus Transparan -->
                        <div class="mt-4 pt-3 border-t-2 border-slate-200 dark:border-slate-700 text-[11px] text-slate-600 dark:text-slate-300 flex items-start gap-1.5">
                            <x-ui.icon name="info" size="sm" class="text-emerald-700 dark:text-emerald-400 shrink-0 mt-0.5" />
                            <span>
                                Rumus: <span class="font-bold text-slate-900 dark:text-slate-100">((Hadir + Izin + Sakit) / Total Pertemuan) &times; 100</span>. Kategori Alpa mengurangi persentase.
                            </span>
                        </div>
                    </div>

                    <!-- 4 Kartu Breakdown (2 Kolom di HP) -->
                    <div class="lg:col-span-8 grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                        <!-- Total Hadir -->
                        <x-ui.stat-card
                            label="Total Hadir"
                            :value="$ringkasanKehadiran['total_hadir']"
                            color="hadir"
                            description="Mengikuti KBM"
                            :href="route('siswa.riwayat', ['tab' => 'semua', 'status' => 'hadir', 'periode' => 'semua'])"
                            aria-label="Lihat rincian riwayat hadir: {{ $ringkasanKehadiran['total_hadir'] }} pertemuan"
                        >
                            <x-slot:iconSlot>
                                <x-ui.icon name="check" size="lg" class="text-emerald-700 dark:text-emerald-300" />
                            </x-slot:iconSlot>
                        </x-ui.stat-card>

                        <!-- Total Izin -->
                        <x-ui.stat-card
                            label="Total Izin"
                            :value="$ringkasanKehadiran['total_izin']"
                            color="izin"
                            description="Dihitung Hadir"
                            :href="route('siswa.riwayat', ['tab' => 'semua', 'status' => 'izin', 'periode' => 'semua'])"
                            aria-label="Lihat rincian riwayat izin: {{ $ringkasanKehadiran['total_izin'] }} pertemuan"
                        >
                            <x-slot:iconSlot>
                                <x-ui.icon name="info" size="lg" class="text-blue-700 dark:text-blue-300" />
                            </x-slot:iconSlot>
                        </x-ui.stat-card>

                        <!-- Total Sakit -->
                        <x-ui.stat-card
                            label="Total Sakit"
                            :value="$ringkasanKehadiran['total_sakit']"
                            color="sakit"
                            description="Dihitung Hadir"
                            :href="route('siswa.riwayat', ['tab' => 'semua', 'status' => 'sakit', 'periode' => 'semua'])"
                            aria-label="Lihat rincian riwayat sakit: {{ $ringkasanKehadiran['total_sakit'] }} pertemuan"
                        >
                            <x-slot:iconSlot>
                                <x-ui.icon name="plus" size="lg" class="text-amber-700 dark:text-amber-300" />
                            </x-slot:iconSlot>
                        </x-ui.stat-card>

                        <!-- Total Alpa -->
                        <x-ui.stat-card
                            label="Total Alpa"
                            :value="$ringkasanKehadiran['total_alpa']"
                            color="alpa"
                            description="Mengurangi %"
                            :href="route('siswa.riwayat', ['tab' => 'semua', 'status' => 'alpa', 'periode' => 'semua'])"
                            aria-label="Lihat rincian riwayat alpa: {{ $ringkasanKehadiran['total_alpa'] }} pertemuan"
                        >
                            <x-slot:iconSlot>
                                <x-ui.icon name="x" size="lg" class="text-red-700 dark:text-red-300" />
                            </x-slot:iconSlot>
                        </x-ui.stat-card>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi Tambahan -->
            <div class="flex justify-end pt-2">
                <a href="{{ route('siswa.riwayat') }}"
                   class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950 rounded-xl font-bold text-xs uppercase tracking-wider transition shadow-xs gap-2 min-h-[44px]">
                    <x-ui.icon name="history" size="sm" />
                    <span>Lihat Riwayat &amp; Persentase per Mapel</span>
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
