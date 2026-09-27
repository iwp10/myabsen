<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Siswa') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Banner Sambutan & Identitas -->
            <div class="bg-blue-600 dark:bg-blue-700 rounded-2xl p-6 text-white shadow-md relative overflow-hidden" style="background-color: #2563eb;">
                <div class="absolute -right-8 -bottom-10 opacity-10 pointer-events-none">
                    <svg class="w-56 h-56 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                    </svg>
                </div>
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white mb-2">
                            SMK Mandiri 02 Balaraja
                        </span>
                        <h1 class="text-2xl font-bold tracking-tight text-white">Halo, {{ Auth::user()->name }}!</h1>
                        <p class="text-white text-sm mt-1">
                            Kelas: <span class="font-semibold text-white">{{ Auth::user()->siswa?->kelas?->nama ?? '-' }}</span> &bull; 
                            NIS: <span class="font-semibold text-white">{{ Auth::user()->siswa?->nis ?? Auth::user()->username }}</span>
                        </p>
                    </div>
                    <div class="sm:text-right flex-shrink-0">
                        <span class="text-xs text-white block uppercase font-medium tracking-wider">Tanggal Hari Ini</span>
                        <span class="text-sm font-semibold text-white">
                            {{ \Illuminate\Support\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Ringkasan & Breakdown Kehadiran Transparan -->
            @php
                $persentase = $ringkasanKehadiran['persentase'] ?? 0;
                $totalSesi = $ringkasanKehadiran['total_sesi'] ?? 0;
                $isTargetReached = $persentase >= 80;
            @endphp

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-blue-600 dark:text-blue-400">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M3 12m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
                            <path d="M9 8m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
                            <path d="M15 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v14a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
                            <path d="M4 20l14 0" />
                        </svg>
                        Rincian & Persentase Kehadiran
                    </h3>
                    <a href="{{ route('siswa.riwayat') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 hover:underline flex items-center gap-1">
                        Lihat Riwayat Lengkap &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                    <!-- Kartu Persentase Utama -->
                    <div class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-xl p-5 shadow-xs border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Total Persentase Kehadiran
                                </span>
                                @if($totalSesi > 0)
                                    @if($isTargetReached)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            &ge; 80% Memenuhi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                            &lt; 80% Di Bawah Target
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        Belum Ada Data
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3 flex items-baseline gap-2">
                                <span class="text-4xl font-extrabold tracking-tight {{ $totalSesi > 0 ? ($isTargetReached ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400') : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ $persentase }}%
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    dari {{ $totalSesi }} total pertemuan
                                </span>
                            </div>

                            <!-- Progress Bar Persentase -->
                            <div class="w-full bg-gray-200 dark:bg-gray-700 h-2.5 rounded-full mt-3 overflow-hidden">
                                <div class="h-2.5 rounded-full transition-all duration-500 {{ $totalSesi > 0 ? ($isTargetReached ? 'bg-emerald-500 dark:bg-emerald-400' : 'bg-rose-500 dark:bg-rose-400') : 'bg-gray-400' }}"
                                     style="width: {{ min(100, max(0, $persentase)) }}%;">
                                </div>
                            </div>
                        </div>

                        <!-- Keterangan Rumus Transparan -->
                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60 text-[11px] text-gray-500 dark:text-gray-400 flex items-start gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                <path d="M12 9h.01" />
                                <path d="M11 12h1v4h1" />
                            </svg>
                            <span>
                                Rumus: <span class="font-medium text-gray-700 dark:text-gray-300">((Hadir + Izin + Sakit) / Total Pertemuan) &times; 100</span>. Kategori Alpa adalah satu-satunya yang mengurangi persentase.
                            </span>
                        </div>
                    </div>

                    <!-- 4 Grid Cards Breakdown Transparan -->
                    <div class="lg:col-span-8 grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <!-- Total Hadir -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-xs border border-gray-200 dark:border-gray-700 flex flex-col justify-between hover:border-emerald-300 dark:hover:border-emerald-700 transition">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Total Hadir</span>
                                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-500" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4" /><path d="M15 19l2 2l4 -4" /></svg>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ $ringkasanKehadiran['total_hadir'] }}
                                </div>
                                <p class="mt-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Mengikuti KBM
                                </p>
                            </div>
                        </div>

                        <!-- Total Izin -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-xs border border-gray-200 dark:border-gray-700 flex flex-col justify-between hover:border-blue-300 dark:hover:border-blue-700 transition">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Total Izin</span>
                                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-500" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 17h6" /><path d="M9 13h6" /></svg>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ $ringkasanKehadiran['total_izin'] }}
                                </div>
                                <p class="mt-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Dihitung Hadir
                                </p>
                            </div>
                        </div>

                        <!-- Total Sakit -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-xs border border-gray-200 dark:border-gray-700 flex flex-col justify-between hover:border-amber-300 dark:hover:border-amber-700 transition">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Total Sakit</span>
                                <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-yellow-500" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12h4.5l1.5 -6l4 12l2 -9l1.5 3h4.5" /></svg>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ $ringkasanKehadiran['total_sakit'] }}
                                </div>
                                <p class="mt-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Dihitung Hadir
                                </p>
                            </div>
                        </div>

                        <!-- Total Alpa -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-xs border border-gray-200 dark:border-gray-700 flex flex-col justify-between hover:border-rose-300 dark:hover:border-rose-700 transition">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Total Alpa</span>
                                <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-500" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-2xl sm:text-3xl font-bold {{ $ringkasanKehadiran['total_alpa'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-gray-100' }}">
                                    {{ $ringkasanKehadiran['total_alpa'] }}
                                </div>
                                <p class="mt-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Mengurangi %
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jadwal & Status Hari Ini -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-blue-600 dark:text-blue-400">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M4 5m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" />
                                <path d="M16 3l0 4" />
                                <path d="M8 3l0 4" />
                                <path d="M4 11l16 0" />
                            </svg>
                            Jadwal & Status Hari Ini
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Status absensi per mata pelajaran pada hari ini
                        </p>
                    </div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2.5 py-1 rounded-md">
                        {{ \Illuminate\Support\Carbon::now()->locale('id')->isoFormat('dddd') }}
                    </span>
                </div>

                <div class="p-5">
                    @if($statusHariIni->isEmpty())
                        <div class="py-10 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-500 dark:text-blue-400 mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19.823 19.824a2 2 0 0 1 -1.823 1.176h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 1.175 -1.823m3.825 -.177h9a2 2 0 0 1 2 2v9" />
                                    <path d="M16 3v4" />
                                    <path d="M8 3v1" />
                                    <path d="M4 11h7m4 0h5" />
                                    <path d="M3 3l18 18" />
                                </svg>
                            </div>
                            <p class="text-base font-medium text-gray-900 dark:text-gray-100">Tidak ada jadwal mata pelajaran hari ini</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Anda tidak memiliki jadwal pembelajaran yang perlu diabsen untuk hari ini.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/60">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Jam</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Mata Pelajaran</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Guru</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status Kehadiran</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($statusHariIni as $item)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-medium">
                                                {{ substr($item['jadwal']->jam_mulai, 0, 5) }} - {{ substr($item['jadwal']->jam_selesai, 0, 5) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $item['jadwal']->mapel->nama }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $item['jadwal']->guru->user->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @php
                                                    $statusValue = $item['status_value'] ?? '';
                                                    $badgeClass = match($statusValue) {
                                                        'hadir' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800',
                                                        'izin' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800',
                                                        'sakit' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800',
                                                        'alpa' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800',
                                                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600',
                                                    };
                                                @endphp
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeClass }}">
                                                    {{ $item['status_label'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tombol Aksi Tambahan -->
            <div class="flex justify-end pt-2">
                <a href="{{ route('siswa.riwayat') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-xs gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M12 8l0 4l2 2" />
                        <path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5" />
                    </svg>
                    Lihat Riwayat & Persentase per Mapel
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
