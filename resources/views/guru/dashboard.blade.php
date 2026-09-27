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
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
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
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>

                <!-- Total Jadwal Mingguan -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Jadwal</span>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ $total_jadwal }}
                        </div>
                        <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Sesi mengajar per minggu
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Jadwal Mengajar Hari Ini -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Jadwal Mengajar Hari Ini
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Daftar mata pelajaran yang Anda ampu hari ini
                        </p>
                    </div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2.5 py-1 rounded-md">
                        {{ $tanggal->isoFormat('dddd') }}
                    </span>
                </div>

                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($jadwalHariIni->isEmpty())
                        <div class="py-10 text-center">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-500 dark:text-blue-400 mb-3">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <p class="text-base font-semibold text-gray-900 dark:text-gray-100">Tidak ada jadwal mengajar hari ini</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-md mx-auto">
                                Anda tidak memiliki jadwal mata pelajaran yang aktif untuk hari ini. Anda dapat memeriksa rekapan atau riwayat absensi sebelumnya.
                            </p>
                            <div class="mt-5 flex justify-center gap-3">
                                <a href="{{ route('guru.riwayat') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold uppercase tracking-widest rounded-lg shadow-xs transition gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                                                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
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
                                                    <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
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
