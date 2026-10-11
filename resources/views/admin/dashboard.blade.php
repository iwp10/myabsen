<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Dashboard Admin') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status') || session('success'))
                <x-alert type="success" :autoDismiss="true">
                    {{ session('status') ?: session('success') }}
                </x-alert>
            @endif

            <!-- Banner Sapaan Atas (Emerald Primer) -->
            <div class="bg-emerald-700 dark:bg-emerald-800 rounded-2xl p-6 text-white shadow-md border-2 border-emerald-800 dark:border-emerald-700 relative overflow-hidden">
                <div class="absolute -right-8 -bottom-10 opacity-10 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-56 h-56 text-white">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                        <path d="M12 7v5l3 3" />
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
                        <div class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-white/20 text-white border border-white/30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                <path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" />
                            </svg>
                            <span>Periode aktif: {{ $activePeriode['semester'] }} {{ $activePeriode['tahun_ajaran'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pengingat Pergantian Periode (Jika berbeda dengan saran sistem) -->
            @if ($isBerbedaDenganSaran)
                <div class="bg-amber-100 dark:bg-amber-500/15 border-2 border-amber-400 dark:border-amber-500/50 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-200 dark:bg-amber-500/25 text-amber-900 dark:text-amber-300 flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M12 9v4" />
                                <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.875h16.214a1.914 1.914 0 0 0 1.636 -2.875l-8.106 -13.534a1.914 1.914 0 0 0 -3.274 0z" />
                                <path d="M12 16h.01" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-amber-900 dark:text-amber-200">
                                Pengingat Periode Akademik
                            </h4>
                            <p class="text-xs text-amber-800 dark:text-amber-300 mt-0.5">
                                Sepertinya sudah masuk semester {{ $saranPeriode['semester'] }} ({{ $saranPeriode['tahun_ajaran'] }}). Ganti periode?
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('admin.pengaturan-periode.index') }}"
                       class="inline-flex items-center justify-center gap-1.5 px-4 py-2 min-h-[44px] bg-amber-600 hover:bg-amber-700 active:bg-amber-800 dark:bg-amber-400 dark:hover:bg-amber-300 text-white dark:text-slate-950 text-xs font-semibold rounded-xl shadow-xs transition flex-shrink-0">
                        <span>Ganti periode</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M9 6l6 6l-6 6" />
                        </svg>
                    </a>
                </div>
            @endif

            <!-- Kartu Statistik Master Data (Grid 2 Kolom di HP, 4 Kolom di Desktop) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <!-- Total Siswa -->
                <x-ui.stat-card
                    label="Total Siswa"
                    :value="number_format($total_siswa, 0, ',', '.')"
                    color="blue"
                    description="Siswa terdaftar aktif"
                    :href="route('admin.siswa.index')"
                >
                    <x-slot:iconSlot>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                    </x-slot:iconSlot>
                </x-ui.stat-card>

                <!-- Total Guru -->
                <x-ui.stat-card
                    label="Total Guru"
                    :value="number_format($total_guru, 0, ',', '.')"
                    color="hadir"
                    description="Guru pendidik aktif"
                    :href="route('admin.guru.index')"
                >
                    <x-slot:iconSlot>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /><path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" /><path d="M12 12l0 .01" /><path d="M3 13a20 20 0 0 0 18 0" /></svg>
                    </x-slot:iconSlot>
                </x-ui.stat-card>

                <!-- Total Kelas -->
                <x-ui.stat-card
                    label="Total Kelas"
                    :value="number_format($total_kelas, 0, ',', '.')"
                    color="sakit"
                    description="Rombel aktif"
                    :href="route('admin.kelas.index')"
                >
                    <x-slot:iconSlot>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M9 8l1 0" /><path d="M9 12l1 0" /><path d="M9 16l1 0" /><path d="M14 8l1 0" /><path d="M14 12l1 0" /><path d="M14 16l1 0" /><path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16" /></svg>
                    </x-slot:iconSlot>
                </x-ui.stat-card>

                <!-- Total Mapel -->
                <x-ui.stat-card
                    label="Total Mapel"
                    :value="number_format($total_mapel, 0, ',', '.')"
                    color="indigo"
                    description="Mata pelajaran aktif"
                    :href="route('admin.mapel.index')"
                >
                    <x-slot:iconSlot>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 19a9 9 0 0 1 9 0a9 9 0 0 1 9 0" /><path d="M3 6a9 9 0 0 1 9 0a9 9 0 0 1 9 0" /><path d="M3 6l0 13" /><path d="M12 6l0 13" /><path d="M21 6l0 13" /></svg>
                    </x-slot:iconSlot>
                </x-ui.stat-card>
            </div>

            <!-- Aksi Cepat (Quick Actions) -->
            <div class="bg-white dark:bg-slate-900 rounded-xl p-6 shadow-xs border-2 border-slate-300 dark:border-slate-600">
                <div class="flex items-center justify-between pb-4 border-b-2 border-slate-200 dark:border-slate-700">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-emerald-700 dark:text-emerald-400">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M13 3l0 7l6 0l-8 11l0 -7l-6 0z" />
                            </svg>
                            Aksi Cepat
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                            Pintasan navigasi langsung untuk mempermudah operasional harian administrator.
                        </p>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Pintasan Laporan -->
                    <a href="{{ route('admin.laporan.index') }}"
                       class="group p-4 rounded-xl border-2 border-slate-300 dark:border-slate-600 hover:border-emerald-500 dark:hover:border-emerald-400 bg-white dark:bg-slate-800 shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5 transition duration-150 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-500/15 text-blue-700 dark:text-blue-300 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-700 dark:text-blue-400" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 17h6" /><path d="M9 13h6" /></svg>
                                </div>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-500/15 text-blue-800 dark:text-blue-300 border border-blue-400 dark:border-blue-500/50">
                                    Rekap
                                </span>
                            </div>
                            <h4 class="mt-3 text-sm font-bold text-slate-900 dark:text-white group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">
                                Rekap Laporan Absensi
                            </h4>
                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">
                                Pantau kehadiran siswa dan ekspor laporan ke format Excel atau PDF resmi.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t-2 border-slate-200 dark:border-slate-700 flex items-center text-xs font-semibold text-emerald-700 dark:text-emerald-400">
                            <span>Buka Laporan</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M9 6l6 6l-6 6" />
                            </svg>
                        </div>
                    </a>

                    <!-- Pintasan Koreksi Absensi -->
                    <a href="{{ route('admin.koreksi-absensi.index') }}"
                       class="group p-4 rounded-xl border-2 border-slate-300 dark:border-slate-600 hover:border-emerald-500 dark:hover:border-emerald-400 bg-white dark:bg-slate-800 shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5 transition duration-150 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-500/15 text-purple-700 dark:text-purple-300 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-purple-700 dark:text-purple-400" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h10l2 2l-2 2h-10a2 2 0 0 1 -2 -2a2 2 0 0 1 2 -2" /><path d="M13 13h6l2 2l-2 2h-6a2 2 0 0 1 -2 -2a2 2 0 0 1 2 -2" /><path d="M7 21h8l2 2l-2 2h-8a2 2 0 0 1 -2 -2a2 2 0 0 1 2 -2" /></svg>
                                </div>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-500/15 text-purple-800 dark:text-purple-300 border border-purple-400 dark:border-purple-500/50">
                                    Koreksi
                                </span>
                            </div>
                            <h4 class="mt-3 text-sm font-bold text-slate-900 dark:text-white group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">
                                Koreksi Absensi
                            </h4>
                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">
                                Ubah atau lengkapi data absensi siswa pada tanggal lampau per kelas.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t-2 border-slate-200 dark:border-slate-700 flex items-center text-xs font-semibold text-emerald-700 dark:text-emerald-400">
                            <span>Koreksi Absensi</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M9 6l6 6l-6 6" />
                            </svg>
                        </div>
                    </a>

                    <!-- Pintasan Kelola Siswa -->
                    <a href="{{ route('admin.siswa.index') }}"
                       class="group p-4 rounded-xl border-2 border-slate-300 dark:border-slate-600 hover:border-emerald-500 dark:hover:border-emerald-400 bg-white dark:bg-slate-800 shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5 transition duration-150 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-emerald-700 dark:text-emerald-400" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M16 19h6" /><path d="M19 16v6" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4" /></svg>
                                </div>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 border border-emerald-400 dark:border-emerald-500/50">
                                    Master
                                </span>
                            </div>
                            <h4 class="mt-3 text-sm font-bold text-slate-900 dark:text-white group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">
                                Kelola Data Siswa
                            </h4>
                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">
                                Tambah data siswa baru, impor berkas Excel, atau perbarui profil siswa.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t-2 border-slate-200 dark:border-slate-700 flex items-center text-xs font-semibold text-emerald-700 dark:text-emerald-400">
                            <span>Kelola Siswa</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M9 6l6 6l-6 6" />
                            </svg>
                        </div>
                    </a>

                    <!-- Pintasan Jadwal Pelajaran -->
                    <a href="{{ route('admin.jadwal.index') }}"
                       class="group p-4 rounded-xl border-2 border-slate-300 dark:border-slate-600 hover:border-emerald-500 dark:hover:border-emerald-400 bg-white dark:bg-slate-800 shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5 transition duration-150 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-amber-700 dark:text-amber-400" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M11 15h1" /><path d="M12 15v3" /></svg>
                                </div>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-500/15 text-amber-800 dark:text-amber-300 border border-amber-400 dark:border-amber-500/50">
                                    Jadwal
                                </span>
                            </div>
                            <h4 class="mt-3 text-sm font-bold text-slate-900 dark:text-white group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">
                                Jadwal Pelajaran
                            </h4>
                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">
                                Atur alokasi mata pelajaran, kelas, guru pengajar, dan jam absensi harian.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t-2 border-slate-200 dark:border-slate-700 flex items-center text-xs font-semibold text-emerald-700 dark:text-emerald-400">
                            <span>Atur Jadwal</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M9 6l6 6l-6 6" />
                            </svg>
                        </div>
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
