<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Dashboard Guru') }}
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

            <!-- Kartu Statistik (2 Kolom di HP, 3 Kolom di Desktop) -->
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                <!-- Total Kelas -->
                <div role="button"
                     tabindex="0"
                     aria-haspopup="dialog"
                     aria-label="Lihat daftar kelas yang diajar"
                     @click="$dispatch('open-modal', 'modal-daftar-kelas')"
                     @keydown.enter="$dispatch('open-modal', 'modal-daftar-kelas')"
                     @keydown.space.prevent="$dispatch('open-modal', 'modal-daftar-kelas')"
                     class="cursor-pointer bg-white dark:bg-slate-800 rounded-xl p-4 sm:p-5 shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5 border-2 border-slate-300 dark:border-slate-600 border-l-8 border-l-blue-600 dark:border-l-blue-400 hover:border-slate-400 dark:hover:border-slate-500 transition duration-150 flex flex-col justify-between focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500/50">
                    <div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Total Kelas</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded font-semibold bg-blue-100 text-blue-800 dark:bg-blue-500/15 dark:text-blue-300 border border-blue-400 dark:border-blue-500/50">Klik rincian</span>
                        </div>
                        <div class="mt-2 text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tabular-nums">
                            {{ $total_kelas }}
                        </div>
                        <p class="mt-1 text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-300">
                            Kelas unik diajar
                        </p>
                    </div>
                    <div class="mt-3 pt-2 border-t-2 border-slate-200 dark:border-slate-700 flex items-center justify-end text-xs font-semibold text-emerald-700 dark:text-emerald-400">
                        <span>Lihat daftar &rarr;</span>
                    </div>
                </div>

                <!-- Total Mapel -->
                <div role="button"
                     tabindex="0"
                     aria-haspopup="dialog"
                     aria-label="Lihat daftar mata pelajaran yang diampu"
                     @click="$dispatch('open-modal', 'modal-daftar-mapel')"
                     @keydown.enter="$dispatch('open-modal', 'modal-daftar-mapel')"
                     @keydown.space.prevent="$dispatch('open-modal', 'modal-daftar-mapel')"
                     class="cursor-pointer bg-white dark:bg-slate-800 rounded-xl p-4 sm:p-5 shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5 border-2 border-slate-300 dark:border-slate-600 border-l-8 border-l-indigo-600 dark:border-l-indigo-400 hover:border-slate-400 dark:hover:border-slate-500 transition duration-150 flex flex-col justify-between focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500/50">
                    <div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Total Mapel</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-500/15 dark:text-indigo-300 border border-indigo-400 dark:border-indigo-500/50">Klik rincian</span>
                        </div>
                        <div class="mt-2 text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tabular-nums">
                            {{ $total_mapel }}
                        </div>
                        <p class="mt-1 text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-300">
                            Mata pelajaran aktif
                        </p>
                    </div>
                    <div class="mt-3 pt-2 border-t-2 border-slate-200 dark:border-slate-700 flex items-center justify-end text-xs font-semibold text-emerald-700 dark:text-emerald-400">
                        <span>Lihat daftar &rarr;</span>
                    </div>
                </div>

                <!-- Total Jadwal Mingguan -->
                <x-ui.stat-card
                    label="Total Jadwal"
                    :value="$total_jadwal"
                    color="teal"
                    description="Jadwal mengajar seminggu"
                    :href="route('guru.koreksi-absensi')"
                    class="col-span-2 lg:col-span-1"
                >
                    <x-slot:iconSlot>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-teal-700 dark:text-teal-400" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M11 15h1" /><path d="M12 15v3" /></svg>
                    </x-slot:iconSlot>
                </x-ui.stat-card>
            </div>

            <!-- Pengingat Jadwal Belum Diabsen (7 Hari Terakhir) -->
            <div class="bg-white dark:bg-slate-900 rounded-xl p-5 shadow-xs border-2 border-slate-300 dark:border-slate-600">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 {{ ($pengingatBelumDiabsen['total'] ?? 0) > 0 ? 'pb-3 border-b-2 border-slate-200 dark:border-slate-700' : '' }}">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg {{ ($pengingatBelumDiabsen['total'] ?? 0) > 0 ? 'bg-amber-100 text-amber-900 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-400 dark:border-amber-500/50' : 'bg-emerald-100 text-emerald-900 dark:bg-emerald-500/20 dark:text-emerald-300 border border-emerald-400 dark:border-emerald-500/50' }} flex items-center justify-center shrink-0">
                            @if(($pengingatBelumDiabsen['total'] ?? 0) > 0)
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M5 12l5 5l10 -10"/></svg>
                            @endif
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                                @if(($pengingatBelumDiabsen['total'] ?? 0) > 0)
                                    <span>{{ $pengingatBelumDiabsen['total'] }} jadwal belum diabsen dalam 7 hari terakhir</span>
                                @else
                                    <span>Semua jadwal sudah diabsen.</span>
                                @endif
                            </h4>
                            <p class="text-xs text-slate-600 dark:text-slate-300">
                                @if(($pengingatBelumDiabsen['total'] ?? 0) > 0)
                                    Jadwal yang melewati jam tayang dalam jendela koreksi presensi
                                @else
                                    Tidak ada tanggungan pengisian presensi dalam 7 hari terakhir
                                @endif
                            </p>
                        </div>
                    </div>
                    @if(($pengingatBelumDiabsen['total'] ?? 0) > 0)
                        <a href="{{ route('guru.koreksi-absensi') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300 inline-flex items-center gap-1 self-start sm:self-auto">
                            Lihat semua &rarr;
                        </a>
                    @endif
                </div>

                @if(($pengingatBelumDiabsen['total'] ?? 0) > 0)
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5">
                        @foreach($pengingatBelumDiabsen['items'] as $item)
                            <a href="{{ route('guru.absensi.show', ['jadwal' => $item->jadwal_id, 'tanggal' => $item->tanggal]) }}"
                               class="group block p-3 rounded-xl border-2 border-amber-300 dark:border-amber-500/50 bg-amber-100 dark:bg-amber-500/15 hover:bg-amber-200 dark:hover:bg-amber-500/25 transition">
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <span class="text-[11px] font-bold text-amber-900 dark:text-amber-300">
                                        {{ $item->tanggal_label }}
                                    </span>
                                    <span class="text-[10px] text-slate-600 dark:text-slate-300 font-mono">
                                        {{ $item->jam_mulai }} - {{ $item->jam_selesai }}
                                    </span>
                                </div>
                                <div class="font-bold text-sm text-slate-900 dark:text-white group-hover:text-emerald-700 dark:group-hover:text-emerald-300 transition-colors truncate">
                                    {{ $item->mapel_nama }}
                                </div>
                                <div class="text-xs text-slate-700 dark:text-slate-300 mt-0.5 flex items-center justify-between">
                                    <span>Kelas: {{ $item->kelas_nama }}</span>
                                    <span class="text-emerald-700 dark:text-emerald-400 font-semibold group-hover:translate-x-0.5 transition-transform text-[11px]">
                                        Absen &rarr;
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Jadwal Mengajar Hari Ini -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border-2 border-slate-300 dark:border-slate-600 overflow-hidden">
                <div class="p-5 border-b-2 border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-emerald-700 dark:text-emerald-400">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M4 5m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" />
                                <path d="M16 3l0 4" />
                                <path d="M8 3l0 4" />
                                <path d="M4 11l16 0" />
                            </svg>
                            Jadwal Mengajar Hari Ini
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                            Daftar mata pelajaran yang Anda ampu hari ini
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 px-2.5 py-1 rounded-md">
                            {{ $tanggal->isoFormat('dddd') }}
                        </span>
                        <a href="{{ route('guru.koreksi-absensi') }}" class="inline-flex items-center px-3.5 py-1.5 min-h-[44px] bg-emerald-700 hover:bg-emerald-800 text-white dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950 text-xs font-semibold rounded-lg shadow-xs transition gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M11.5 21h-5.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v6" />
                                <path d="M16 3v4" />
                                <path d="M8 3v4" />
                                <path d="M4 11h16" />
                                <path d="M15 19l2 2l4 -4" />
                            </svg>
                            <span>Jadwal & Koreksi Absensi</span>
                        </a>
                    </div>
                </div>

                <div class="p-6 text-slate-800 dark:text-slate-100">
                    @if($jadwalHariIni->isEmpty())
                        <div class="py-10 text-center">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 mb-3 border border-slate-300 dark:border-slate-700">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19.823 19.824a2 2 0 0 1 -1.823 1.176h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 1.175 -1.823m3.825 -.177h9a2 2 0 0 1 2 2v9" />
                                    <path d="M16 3v4" />
                                    <path d="M8 3v1" />
                                    <path d="M4 11h7m4 0h5" />
                                    <path d="M3 3l18 18" />
                                </svg>
                            </div>
                            <p class="text-base font-bold text-slate-900 dark:text-white">Tidak ada jadwal mengajar hari ini</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1 max-w-md mx-auto">
                                Anda tidak memiliki jadwal mata pelajaran yang aktif untuk hari ini. Anda dapat memeriksa rekapan atau riwayat absensi sebelumnya.
                            </p>
                            <div class="mt-5 flex flex-wrap justify-center gap-3">
                                <a href="{{ route('guru.koreksi-absensi') }}" class="inline-flex items-center min-h-[44px] px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950 text-xs font-semibold uppercase tracking-widest rounded-lg shadow-xs transition gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M11.5 21h-5.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v6" />
                                        <path d="M16 3v4" />
                                        <path d="M8 3v4" />
                                        <path d="M4 11h16" />
                                        <path d="M15 19l2 2l4 -4" />
                                    </svg>
                                    Jadwal & Koreksi Absensi
                                </a>
                                <a href="{{ route('guru.riwayat') }}" class="inline-flex items-center min-h-[44px] px-4 py-2 bg-white dark:bg-slate-800 border-2 border-slate-400 dark:border-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-900 dark:text-slate-100 text-xs font-semibold uppercase tracking-widest rounded-lg shadow-xs transition gap-2">
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
                                <div class="bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-xl p-5 shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5 flex flex-col justify-between hover:border-slate-400 dark:hover:border-slate-500 transition">
                                    <div>
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <h4 class="font-bold text-lg text-slate-900 dark:text-white">{{ $jadwal->mapel->nama }}</h4>
                                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Kelas: <span class="text-slate-900 dark:text-white font-bold">{{ $jadwal->kelas->nama }}</span></p>
                                            </div>
                                            <div class="text-right">
                                                <span class="whitespace-nowrap text-xs font-mono font-bold bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 py-1 px-2.5 rounded-md">
                                                    {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 pt-3 border-t-2 border-slate-200 dark:border-slate-700">
                                        @if($jadwal->sesi_hari_ini)
                                            <div class="mb-3">
                                                <div class="mb-2">
                                                    <x-ui.status-badge status="sudah_diabsen" />
                                                </div>
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
                                                    <div class="bg-emerald-100 dark:bg-emerald-500/15 p-1.5 rounded-lg border border-emerald-400 dark:border-emerald-500/50 text-emerald-900 dark:text-emerald-300">
                                                        <span class="block text-[10px] uppercase font-bold">Hadir</span>
                                                        <span class="font-extrabold text-sm">{{ $hadir }}</span>
                                                    </div>
                                                    <div class="bg-blue-100 dark:bg-blue-500/15 p-1.5 rounded-lg border border-blue-400 dark:border-blue-500/50 text-blue-900 dark:text-blue-300">
                                                        <span class="block text-[10px] uppercase font-bold">Izin</span>
                                                        <span class="font-extrabold text-sm">{{ $izin }}</span>
                                                    </div>
                                                    <div class="bg-amber-100 dark:bg-amber-500/15 p-1.5 rounded-lg border border-amber-400 dark:border-amber-500/50 text-amber-900 dark:text-amber-300">
                                                        <span class="block text-[10px] uppercase font-bold">Sakit</span>
                                                        <span class="font-extrabold text-sm">{{ $sakit }}</span>
                                                    </div>
                                                    <div class="bg-red-100 dark:bg-red-500/15 p-1.5 rounded-lg border border-red-400 dark:border-red-500/50 text-red-900 dark:text-red-300">
                                                        <span class="block text-[10px] uppercase font-bold">Alpa</span>
                                                        <span class="font-extrabold text-sm">{{ $alpa }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <a href="{{ route('guru.absensi.show', $jadwal->id) }}" class="inline-flex justify-center w-full items-center min-h-[44px] px-4 py-2.5 bg-white dark:bg-slate-800 border-2 border-slate-400 dark:border-slate-500 rounded-xl font-bold text-xs text-slate-900 dark:text-slate-100 uppercase tracking-wider shadow-xs hover:bg-slate-100 dark:hover:bg-slate-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500 transition">
                                                Edit Absensi
                                            </a>
                                        @else
                                            <div class="mb-3">
                                                <x-ui.status-badge status="belum_diabsen" />
                                            </div>
                                            <a href="{{ route('guru.absensi.show', $jadwal->id) }}" class="inline-flex justify-center w-full items-center min-h-[44px] px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950 rounded-xl font-bold text-xs uppercase tracking-wider shadow-xs focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500 transition">
                                                Isi Absensi
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

    <!-- Modal Daftar Kelas yang Diajar -->
    <x-modal name="modal-daftar-kelas" maxWidth="lg">
        <div class="p-6 bg-white dark:bg-slate-900 border-2 border-slate-300 dark:border-slate-600 rounded-2xl">
            <div class="flex items-center justify-between pb-4 border-b-2 border-slate-200 dark:border-slate-700">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-700 dark:text-emerald-400" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M3 21l18 0" /><path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16" /></svg>
                        Daftar Kelas yang Diajar
                    </h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                        Periode Aktif: {{ $activePeriode['tahun_ajaran'] ?? '-' }} &bull; Semester {{ $activePeriode['semester'] ?? '-' }}
                    </p>
                </div>
                <button type="button" @click="$dispatch('close-modal', 'modal-daftar-kelas')" class="text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 min-h-[44px] min-w-[44px] flex items-center justify-center" aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="mt-4 max-h-96 overflow-y-auto space-y-2">
                @forelse($daftarKelas as $item)
                    <a href="{{ route('guru.jadwal', ['kelas_id' => $item['id']]) }}"
                       class="group flex items-center justify-between p-3.5 rounded-xl border-2 border-slate-200 dark:border-slate-700 hover:border-emerald-500 dark:hover:border-emerald-400 bg-slate-50 dark:bg-slate-800/80 transition">
                        <div>
                            <h4 class="font-bold text-sm text-slate-900 dark:text-white group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">
                                {{ $item['nama'] }}
                            </h4>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                                Jurusan: {{ $item['jurusan'] }} &bull; {{ $item['jumlah_jadwal'] }} jadwal mengajar
                            </p>
                        </div>
                        <span class="inline-flex items-center text-xs font-semibold text-emerald-700 dark:text-emerald-400 group-hover:translate-x-0.5 transition-transform gap-1">
                            Buka Jadwal &rarr;
                        </span>
                    </a>
                @empty
                    <div class="py-8 text-center text-sm text-slate-600 dark:text-slate-300">
                        Tidak ada kelas yang diajar pada periode ini.
                    </div>
                @endforelse
            </div>

            <div class="mt-5 pt-3 border-t-2 border-slate-200 dark:border-slate-700 flex justify-between items-center text-xs text-slate-600 dark:text-slate-300">
                <span>Total: <strong>{{ count($daftarKelas) }}</strong> kelas</span>
                <button type="button" @click="$dispatch('close-modal', 'modal-daftar-kelas')" class="px-3.5 py-2 min-h-[44px] bg-white dark:bg-slate-800 border-2 border-slate-400 dark:border-slate-500 text-slate-900 dark:text-slate-100 rounded-xl font-semibold hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                    Tutup
                </button>
            </div>
        </div>
    </x-modal>

    <!-- Modal Daftar Mapel yang Diampu -->
    <x-modal name="modal-daftar-mapel" maxWidth="lg">
        <div class="p-6 bg-white dark:bg-slate-900 border-2 border-slate-300 dark:border-slate-600 rounded-2xl">
            <div class="flex items-center justify-between pb-4 border-b-2 border-slate-200 dark:border-slate-700">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-700 dark:text-indigo-400" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M3 19a9 9 0 0 1 9 0a9 9 0 0 1 9 0" /><path d="M3 6a9 9 0 0 1 9 0a9 9 0 0 1 9 0" /></svg>
                        Daftar Mata Pelajaran yang Diampu
                    </h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                        Periode Aktif: {{ $activePeriode['tahun_ajaran'] ?? '-' }} &bull; Semester {{ $activePeriode['semester'] ?? '-' }}
                    </p>
                </div>
                <button type="button" @click="$dispatch('close-modal', 'modal-daftar-mapel')" class="text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 min-h-[44px] min-w-[44px] flex items-center justify-center" aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="mt-4 max-h-96 overflow-y-auto space-y-2">
                @forelse($daftarMapel as $item)
                    <a href="{{ route('guru.jadwal', ['mapel_id' => $item['id']]) }}"
                       class="group flex items-center justify-between p-3.5 rounded-xl border-2 border-slate-200 dark:border-slate-700 hover:border-indigo-500 dark:hover:border-indigo-400 bg-slate-50 dark:bg-slate-800/80 transition">
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-sm text-slate-900 dark:text-white group-hover:text-indigo-700 dark:group-hover:text-indigo-400 transition-colors">
                                    {{ $item['nama'] }}
                                </h4>
                                @if(!empty($item['kode']) && $item['kode'] !== '-')
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-mono">
                                        {{ $item['kode'] }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                                {{ $item['jumlah_jadwal'] }} jadwal &bull; {{ $item['jumlah_kelas'] }} kelas
                            </p>
                        </div>
                        <span class="inline-flex items-center text-xs font-semibold text-indigo-700 dark:text-indigo-400 group-hover:translate-x-0.5 transition-transform gap-1">
                            Buka Jadwal &rarr;
                        </span>
                    </a>
                @empty
                    <div class="py-8 text-center text-sm text-slate-600 dark:text-slate-300">
                        Tidak ada mata pelajaran yang diampu pada periode ini.
                    </div>
                @endforelse
            </div>

            <div class="mt-5 pt-3 border-t-2 border-slate-200 dark:border-slate-700 flex justify-between items-center text-xs text-slate-600 dark:text-slate-300">
                <span>Total: <strong>{{ count($daftarMapel) }}</strong> mata pelajaran</span>
                <button type="button" @click="$dispatch('close-modal', 'modal-daftar-mapel')" class="px-3.5 py-2 min-h-[44px] bg-white dark:bg-slate-800 border-2 border-slate-400 dark:border-slate-500 text-slate-900 dark:text-slate-100 rounded-xl font-semibold hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                    Tutup
                </button>
            </div>
        </div>
    </x-modal>
</x-app-layout>
