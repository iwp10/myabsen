<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Admin') }}
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
                            SMK Mandiri 02 Balaraja &bull; Pusat Kendali Utama
                        </span>
                        <h1 class="text-2xl font-bold tracking-tight text-white">Halo, Administrator!</h1>
                        <p class="text-white/90 text-sm mt-1">
                            Selamat datang di Pusat Kendali Utama MyAbsen SMK Mandiri 02 Balaraja
                        </p>
                    </div>
                    <div class="sm:text-right flex-shrink-0">
                        <span class="text-xs text-white/80 block uppercase font-medium tracking-wider">Tanggal Hari Ini</span>
                        <span class="text-sm font-semibold text-white">
                            {{ \Illuminate\Support\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Kartu Statistik Master Data (Grid 4 Kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Siswa -->
                <a href="{{ route('admin.siswa.index') }}" class="group bg-white dark:bg-gray-800 rounded-xl p-5 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700 flex items-center justify-between transition duration-150 hover:border-blue-400 dark:hover:border-blue-500 hover:shadow-md">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Total Siswa</span>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($total_siswa, 0, ',', '.') }}
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Siswa terdaftar aktif
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </a>

                <!-- Total Guru -->
                <a href="{{ route('admin.guru.index') }}" class="group bg-white dark:bg-gray-800 rounded-xl p-5 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700 flex items-center justify-between transition duration-150 hover:border-emerald-400 dark:hover:border-emerald-500 hover:shadow-md">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Total Guru</span>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($total_guru, 0, ',', '.') }}
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Guru pengajar terdaftar
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                </a>

                <!-- Total Kelas -->
                <a href="{{ route('admin.kelas.index') }}" class="group bg-white dark:bg-gray-800 rounded-xl p-5 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700 flex items-center justify-between transition duration-150 hover:border-amber-400 dark:hover:border-amber-500 hover:shadow-md">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">Total Kelas</span>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($total_kelas, 0, ',', '.') }}
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Rombongan belajar
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                        </svg>
                    </div>
                </a>

                <!-- Total Mapel -->
                <a href="{{ route('admin.mapel.index') }}" class="group bg-white dark:bg-gray-800 rounded-xl p-5 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700 flex items-center justify-between transition duration-150 hover:border-purple-400 dark:hover:border-purple-500 hover:shadow-md">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">Total Mapel</span>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($total_mapel, 0, ',', '.') }}
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Mata pelajaran kurikulum
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </a>
            </div>

            <!-- Aksi Cepat (Quick Actions) -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700/60">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Aksi Cepat
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Pintasan navigasi langsung untuk mempermudah operasional harian administrator.
                        </p>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Pintasan Laporan -->
                    <a href="{{ route('admin.laporan.index') }}"
                       class="group p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-500 bg-gray-50/50 dark:bg-gray-900/40 hover:bg-blue-50/50 dark:hover:bg-blue-950/20 transition duration-150 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300">
                                    Rekap
                                </span>
                            </div>
                            <h4 class="mt-3 text-sm font-bold text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                Rekap Laporan Absensi
                            </h4>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Pantau kehadiran siswa dan ekspor laporan ke format Excel atau PDF resmi.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-200/60 dark:border-gray-700/60 flex items-center text-xs font-semibold text-blue-600 dark:text-blue-400">
                            <span>Buka Laporan</span>
                            <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </a>

                    <!-- Pintasan Kelola Siswa -->
                    <a href="{{ route('admin.siswa.index') }}"
                       class="group p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-emerald-500 dark:hover:border-emerald-500 bg-gray-50/50 dark:bg-gray-900/40 hover:bg-emerald-50/50 dark:hover:bg-emerald-950/20 transition duration-150 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300">
                                    Master
                                </span>
                            </div>
                            <h4 class="mt-3 text-sm font-bold text-gray-900 dark:text-gray-100 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                Kelola Data Siswa
                            </h4>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Tambah data siswa baru, impor berkas Excel, atau perbarui profil siswa.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-200/60 dark:border-gray-700/60 flex items-center text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                            <span>Kelola Siswa</span>
                            <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </a>

                    <!-- Pintasan Jadwal Pelajaran -->
                    <a href="{{ route('admin.jadwal.index') }}"
                       class="group p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-amber-500 dark:hover:border-amber-500 bg-gray-50/50 dark:bg-gray-900/40 hover:bg-amber-50/50 dark:hover:bg-amber-950/20 transition duration-150 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300">
                                    Jadwal
                                </span>
                            </div>
                            <h4 class="mt-3 text-sm font-bold text-gray-900 dark:text-gray-100 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                                Jadwal Pelajaran
                            </h4>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Atur alokasi mata pelajaran, kelas, guru pengajar, dan jam absensi harian.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-200/60 dark:border-gray-700/60 flex items-center text-xs font-semibold text-amber-600 dark:text-amber-400">
                            <span>Atur Jadwal</span>
                            <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
