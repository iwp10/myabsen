<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Guru') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            @if (session('status'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative shadow-xs" role="alert">
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            <!-- Banner Sapaan Atas (Solid Blue & Shadow) -->
            <div class="bg-blue-600 dark:bg-blue-700 rounded-2xl p-6 text-white shadow-md relative overflow-hidden" style="background-color: #2563eb;">
                <div class="absolute -right-8 -bottom-10 opacity-10 pointer-events-none">
                    <svg class="w-56 h-56 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                    </svg>
                </div>
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white mb-2">
                            SMK Mandiri 02 Balaraja &bull; Portal Guru
                        </span>
                        <h1 class="text-2xl font-bold tracking-tight text-white">Halo, {{ Auth::user()->name }}!</h1>
                        <p class="text-white text-sm mt-1">
                            Selamat datang di Dashboard Guru MyAbsen &bull; NIP: <span class="font-semibold text-white">{{ Auth::user()->guru?->nip ?? '-' }}</span>
                        </p>
                    </div>
                    <div class="sm:text-right flex-shrink-0">
                        <span class="text-xs text-white block uppercase font-medium tracking-wider">Tanggal Hari Ini</span>
                        <span class="text-sm font-semibold text-white">
                            {{ $tanggal->isoFormat('dddd, D MMMM YYYY') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Kartu Statistik (3 Kolom) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Total Kelas -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Kelas</span>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ $total_kelas }}
                        </div>
                        <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Kelas unik diajar
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-blue-500" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M9 8l1 0" /><path d="M9 12l1 0" /><path d="M9 16l1 0" /><path d="M14 8l1 0" /><path d="M14 12l1 0" /><path d="M14 16l1 0" /><path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16" /></svg>
                    </div>
                </div>

                <!-- Total Mapel -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Mapel</span>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ $total_mapel }}
                        </div>
                        <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Mata pelajaran aktif
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-purple-500" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 19a9 9 0 0 1 9 0a9 9 0 0 1 9 0" /><path d="M3 6a9 9 0 0 1 9 0a9 9 0 0 1 9 0" /><path d="M3 6l0 13" /><path d="M12 6l0 13" /><path d="M21 6l0 13" /></svg>
                    </div>
                </div>

                <!-- Total Jadwal Mingguan -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Jadwal</span>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ $total_jadwal }}
                        </div>
                        <a href="{{ route('guru.jadwal') }}" class="mt-1 inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 gap-1">
                            Lihat Seluruh Jadwal &rarr;
                        </a>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-green-500" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M11 15h1" /><path d="M12 15v3" /></svg>
                    </div>
                </div>
            </div>

            <!-- Jadwal Mengajar Hari Ini -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-blue-600 dark:text-blue-400">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M4 5m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" />
                                <path d="M16 3l0 4" />
                                <path d="M8 3l0 4" />
                                <path d="M4 11l16 0" />
                            </svg>
                            Jadwal Mengajar Hari Ini
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Daftar mata pelajaran yang Anda ampu hari ini
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2.5 py-1 rounded-md">
                            {{ $tanggal->isoFormat('dddd') }}
                        </span>
                        <a href="{{ route('guru.jadwal') }}" class="inline-flex items-center text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 gap-1">
                            <span>Lihat Seluruh Jadwal</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M9 6l6 6l-6 6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($jadwalHariIni->isEmpty())
                        <div class="py-10 text-center">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-500 dark:text-blue-400 mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19.823 19.824a2 2 0 0 1 -1.823 1.176h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 1.175 -1.823m3.825 -.177h9a2 2 0 0 1 2 2v9" />
                                    <path d="M16 3v4" />
                                    <path d="M8 3v1" />
                                    <path d="M4 11h7m4 0h5" />
                                    <path d="M3 3l18 18" />
                                </svg>
                            </div>
                            <p class="text-base font-semibold text-gray-900 dark:text-gray-100">Tidak ada jadwal mengajar hari ini</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-md mx-auto">
                                Anda tidak memiliki jadwal mata pelajaran yang aktif untuk hari ini. Anda dapat memeriksa rekapan atau riwayat absensi sebelumnya.
                            </p>
                            <div class="mt-5 flex flex-wrap justify-center gap-3">
                                <a href="{{ route('guru.jadwal') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold uppercase tracking-widest rounded-lg shadow-xs transition gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4" />
                                        <path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                        <path d="M15 3v4" />
                                        <path d="M7 3v4" />
                                        <path d="M3 11h16" />
                                        <path d="M18 16.496v1.504l1 1" />
                                    </svg>
                                    Lihat Seluruh Jadwal
                                </a>
                                <a href="{{ route('guru.riwayat') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-semibold uppercase tracking-widest rounded-lg shadow-xs transition gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M12 8l0 4l2 2" />
                                        <path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5" />
                                    </svg>
                                    Lihat Riwayat Absensi
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($jadwalHariIni as $jadwal)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-xs flex flex-col justify-between hover:border-blue-300 dark:hover:border-blue-600 transition">
                                    <div>
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <h4 class="font-bold text-lg text-gray-900 dark:text-gray-100">{{ $jadwal->mapel->nama }}</h4>
                                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Kelas: <span class="text-gray-900 dark:text-gray-200 font-semibold">{{ $jadwal->kelas->nama }}</span></p>
                                            </div>
                                            <div class="text-right">
                                                <span class="whitespace-nowrap text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800 py-1 px-2.5 rounded-full">
                                                    {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60">
                                        @if($jadwal->sesi_hari_ini)
                                            <div class="bg-emerald-50 dark:bg-emerald-950/30 p-3 rounded-lg mb-3 border border-emerald-200 dark:border-emerald-800">
                                                <p class="text-xs font-semibold text-emerald-800 dark:text-emerald-300 mb-2 flex items-center gap-1.5">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M5 12l5 5l10 -10" />
                                                    </svg>
                                                    Sudah diabsen
                                                </p>
                                                
                                                @php
                                                    $hadir = 0; $izin = 0; $sakit = 0; $alpa = 0;
                                                    foreach($jadwal->sesi_hari_ini->detailAbsensi as $detail) {
                                                        if($detail->status->value === 'hadir') $hadir++;
                                                        elseif($detail->status->value === 'izin') $izin++;
                                                        elseif($detail->status->value === 'sakit') $sakit++;
                                                        elseif($detail->status->value === 'alpa') $alpa++;
                                                    }
                                                @endphp
                                                <div class="grid grid-cols-4 gap-1.5 text-center text-xs">
                                                    <div class="bg-white dark:bg-gray-800 p-1.5 rounded border border-gray-200 dark:border-gray-700">
                                                        <span class="block text-[10px] text-gray-500 dark:text-gray-400 font-medium">Hadir</span>
                                                        <span class="font-bold text-gray-900 dark:text-gray-100">{{ $hadir }}</span>
                                                    </div>
                                                    <div class="bg-white dark:bg-gray-800 p-1.5 rounded border border-gray-200 dark:border-gray-700">
                                                        <span class="block text-[10px] text-gray-500 dark:text-gray-400 font-medium">Izin</span>
                                                        <span class="font-bold text-gray-900 dark:text-gray-100">{{ $izin }}</span>
                                                    </div>
                                                    <div class="bg-white dark:bg-gray-800 p-1.5 rounded border border-gray-200 dark:border-gray-700">
                                                        <span class="block text-[10px] text-gray-500 dark:text-gray-400 font-medium">Sakit</span>
                                                        <span class="font-bold text-gray-900 dark:text-gray-100">{{ $sakit }}</span>
                                                    </div>
                                                    <div class="bg-white dark:bg-gray-800 p-1.5 rounded border border-gray-200 dark:border-gray-700">
                                                        <span class="block text-[10px] text-gray-500 dark:text-gray-400 font-medium">Alpa</span>
                                                        <span class="font-bold text-gray-900 dark:text-gray-100">{{ $alpa }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <a href="{{ route('guru.absensi.show', $jadwal->id) }}" class="inline-flex justify-center w-full items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-xs hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                Edit Absensi
                                            </a>
                                        @else
                                            <div class="bg-amber-50 dark:bg-amber-950/30 p-3 rounded-lg mb-3 border border-amber-200 dark:border-amber-800">
                                                <p class="text-xs font-semibold text-amber-800 dark:text-amber-300 flex items-center gap-1.5">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M12 9v4" />
                                                        <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" />
                                                        <path d="M12 16h.01" />
                                                    </svg>
                                                    Belum diabsen
                                                </p>
                                            </div>
                                            <a href="{{ route('guru.absensi.show', $jadwal->id) }}" class="inline-flex justify-center w-full items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-xs">
                                                Absen Sekarang
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
