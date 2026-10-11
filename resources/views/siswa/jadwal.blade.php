<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 dark:text-white leading-tight">
            {{ __('Jadwal Pelajaran') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Banner Header / Info Siswa & Periode Aktif -->
            <div class="bg-blue-600 dark:bg-blue-700 rounded-2xl p-6 text-white shadow-md relative overflow-hidden border-2 border-blue-700 dark:border-blue-600" style="background-color: #2563eb;">
                <div class="absolute -right-8 -bottom-10 opacity-10 pointer-events-none">
                    <svg class="w-56 h-56 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20a2 2 0 002 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/>
                    </svg>
                </div>
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white mb-2">
                            SMK Mandiri 02 Balaraja &bull; Portal Siswa
                        </span>
                        <h1 class="text-2xl font-bold tracking-tight text-white">Jadwal Pelajaran Kelas</h1>
                        <p class="text-white text-sm mt-1">
                            Siswa: <span class="font-semibold text-white">{{ Auth::user()->name }}</span> 
                            &bull; Kelas: <span class="font-semibold text-white">{{ $siswa?->kelas?->nama ?? 'Belum ada kelas' }}</span>
                            @if($siswa?->kelas?->jurusan)
                                <span class="text-white/80">({{ $siswa->kelas->jurusan->nama }})</span>
                            @endif
                        </p>
                    </div>
                    <div class="sm:text-right flex-shrink-0">
                        <span class="text-xs text-white block uppercase font-medium tracking-wider">Periode Aktif</span>
                        <span class="text-base sm:text-lg font-bold text-white">
                            {{ $activePeriode['tahun_ajaran'] ?? '-' }} &bull; Semester {{ $activePeriode['semester'] ?? '-' }}
                        </span>
                        <span class="text-xs text-white/90 block mt-0.5">
                            Total: {{ $totalJadwal }} Mata Pelajaran
                        </span>
                    </div>
                </div>
            </div>

            <!-- Error Alert -->
            @if($errors->any())
                <div>
                    <x-alert type="danger" :dismissible="true">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-alert>
                </div>
            @endif

            <!-- Kontainer Jadwal & Filter -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border-2 border-slate-300 dark:border-slate-600 overflow-hidden">
                <div class="p-5 border-b-2 border-slate-300 dark:border-slate-600 space-y-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"/><path d="M16 3v4"/><path d="M8 3v4"/><path d="M4 11h16"/><path d="M11 15h1"/><path d="M12 15v3"/></svg>
                            Jadwal Mingguan Pelajaran
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                            Dikelompokkan berdasarkan hari dan diurutkan sesuai jam pelajaran
                        </p>
                    </div>

                    {{-- Filter Hari & Mapel --}}
                    <div class="w-full">
                        <form method="GET" action="{{ route('siswa.jadwal') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 w-full items-end">
                            <div class="w-full min-w-0">
                                <label for="filter-hari" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Hari</label>
                                <select id="filter-hari" name="hari" onchange="this.form.submit()" class="w-full min-w-0 border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 dark:[color-scheme:dark] focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 rounded-lg text-base sm:text-xs min-h-[44px] py-2 pl-3 pr-8 font-medium">
                                    <option value="">Semua Hari</option>
                                    @foreach(['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ strtolower((string)$filterHari) === $val ? 'selected' : '' }}>
                                            {{ $lbl }} {{ strtolower($hariIni) === $val ? '(Hari Ini)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="w-full min-w-0">
                                <label for="filter-mapel" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mata Pelajaran</label>
                                <select id="filter-mapel" name="mapel_id" onchange="this.form.submit()" class="w-full min-w-0 border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 dark:[color-scheme:dark] focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 rounded-lg text-base sm:text-xs min-h-[44px] py-2 pl-3 pr-8 font-medium">
                                    <option value="">Semua Mapel</option>
                                    @foreach($daftarMapel as $m)
                                        <option value="{{ $m->id }}" {{ $filterMapelId == $m->id ? 'selected' : '' }}>
                                            {{ $m->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @if(!empty($filterHari) || !empty($filterMapelId))
                                <div class="w-full min-w-0 sm:col-span-2 md:col-span-1">
                                    <a href="{{ route('siswa.jadwal') }}" class="w-full inline-flex items-center justify-center text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 px-4 py-2 border-2 border-slate-400 dark:border-slate-500 rounded-lg min-h-[44px] transition shadow-xs">
                                        Reset Filter
                                    </a>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>

                {{-- Banner Filter Aktif --}}
                @if($filterHari || $filterMapelId)
                    <div class="px-5 py-3 bg-emerald-50 dark:bg-emerald-500/10 border-b-2 border-emerald-400 dark:border-emerald-500/50 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2 flex-wrap text-xs">
                            <span class="font-bold text-emerald-900 dark:text-emerald-300">Filter aktif:</span>
                            @if($filterHari)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-900 dark:bg-emerald-500/15 dark:text-emerald-300 font-semibold border border-emerald-400 dark:border-emerald-500/50">
                                    Hari: {{ ucfirst($filterHari) }}
                                    <a href="{{ route('siswa.jadwal', array_filter(['mapel_id' => $filterMapelId])) }}" class="hover:text-emerald-950 dark:hover:text-white font-bold ml-1" aria-label="Hapus filter hari">&times;</a>
                                </span>
                            @endif
                            @if($activeMapel)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-900 dark:bg-emerald-500/15 dark:text-emerald-300 font-semibold border border-emerald-400 dark:border-emerald-500/50">
                                    Mata pelajaran: {{ $activeMapel->nama }}
                                    <a href="{{ route('siswa.jadwal', array_filter(['hari' => $filterHari])) }}" class="hover:text-emerald-950 dark:hover:text-white font-bold ml-1" aria-label="Hapus filter mata pelajaran">&times;</a>
                                </span>
                            @endif
                        </div>
                        <a href="{{ route('siswa.jadwal') }}" class="text-xs font-bold text-emerald-700 dark:text-emerald-400 hover:text-emerald-900 dark:hover:text-emerald-200 underline">
                            Reset Semua Filter
                        </a>
                    </div>
                @endif

                {{-- Isi Konten --}}
                <div class="p-6">
                    @if(! $siswa || ! $siswa->kelas_id)
                        {{-- Empty State: Siswa Belum Ada Kelas --}}
                        <div class="py-12 text-center px-4">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-amber-100 dark:bg-amber-500/15 text-amber-900 dark:text-amber-300 mb-3 border-2 border-amber-400 dark:border-amber-500/50">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <h4 class="text-base font-bold text-slate-900 dark:text-white">Belum Terdaftar di Kelas</h4>
                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1 max-w-md mx-auto">
                                Akun Anda belum terhubung dengan kelas aktif manapun. Silakan hubungi bagian kurikulum atau wali kelas untuk penempatan kelas Anda.
                            </p>
                        </div>
                    @elseif($totalJadwal === 0)
                        {{-- Empty State: Kelas Belum Ada Jadwal / Hari Kosong / Filter Kosong --}}
                        <div class="py-12 text-center px-4">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-100 dark:bg-blue-500/15 text-blue-900 dark:text-blue-300 mb-3 border-2 border-blue-400 dark:border-blue-500/50">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h4 class="text-base font-bold text-slate-900 dark:text-white">
                                @if(!empty($filterHari) || !empty($filterMapelId))
                                    Tidak Ada Jadwal Pelajaran yang Cocok
                                @else
                                    Belum Ada Jadwal Pelajaran
                                @endif
                            </h4>
                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1 max-w-md mx-auto">
                                @if(!empty($filterHari) || !empty($filterMapelId))
                                    Tidak ditemukan jadwal pelajaran yang sesuai dengan filter. Coba ubah atau reset filter untuk menampilkan semua jadwal.
                                @else
                                    Jadwal pelajaran untuk kelas Anda pada periode ini belum diterbitkan oleh bagian kurikulum.
                                @endif
                            </p>
                            @if(!empty($filterHari) || !empty($filterMapelId))
                                <div class="mt-4">
                                    <a href="{{ route('siswa.jadwal') }}" class="inline-flex items-center px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950 text-xs font-bold uppercase tracking-wider rounded-xl transition min-h-[44px]">
                                        Tampilkan Semua Jadwal
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- Daftar Jadwal Dikelompokkan Per Hari --}}
                        @php
                            $urutanHari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
                            if (!empty($filterHari) && in_array(strtolower($filterHari), $urutanHari)) {
                                $daftarHariTampil = [strtolower($filterHari)];
                            } else {
                                $daftarHariTampil = $urutanHari;
                            }
                        @endphp

                        <div class="space-y-6">
                            @foreach($daftarHariTampil as $hariNama)
                                @php
                                    $jadwalsHari = $jadwalsByHari->get($hariNama, collect());
                                    $isHariIni = (strtolower($hariNama) === strtolower($hariIni));
                                @endphp

                                @if($jadwalsHari->isNotEmpty())
                                    <div class="rounded-xl border-2 transition-all duration-150 overflow-hidden border-slate-300 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-900/40">
                                        {{-- Header Kelompok Hari --}}
                                        <div class="px-5 py-3.5 border-b-2 flex items-center justify-between {{ $isHariIni ? 'bg-slate-100 dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white' : 'bg-slate-100 dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white' }}">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-sm sm:text-base capitalize text-slate-900 dark:text-white">
                                                    Hari {{ ucfirst($hariNama) }}
                                                </span>
                                                @if($isHariIni)
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-700 text-white dark:bg-emerald-400 dark:text-slate-950">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        Hari ini
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-200/90 text-slate-800 dark:bg-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600">
                                                {{ $jadwalsHari->count() }} Mata Pelajaran
                                            </span>
                                        </div>

                                        {{-- Kartu / Baris Jadwal Pada Hari Ini --}}
                                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                            @foreach($jadwalsHari as $item)
                                                @php
                                                    $isMatchFilter = ($filterMapelId && $item->mapel_id == $filterMapelId);
                                                    if ($isMatchFilter) {
                                                        $cardClasses = 'bg-emerald-50 dark:bg-emerald-500/10 border-2 border-l-8 border-l-emerald-600 border-emerald-400 dark:border-emerald-500/50 dark:border-l-emerald-400 shadow-xs dark:shadow-lg dark:shadow-black/40';
                                                    } else {
                                                        $cardClasses = 'bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5 hover:border-emerald-500 dark:hover:border-emerald-400';
                                                    }
                                                @endphp
                                                <div class="rounded-xl p-4 transition duration-150 flex flex-col justify-between {{ $cardClasses }}">
                                                    <div>
                                                        {{-- Jam Pelajaran & Badge Cocok --}}
                                                        <div class="flex items-center justify-between text-xs text-slate-600 dark:text-slate-400 mb-2">
                                                            <span class="inline-flex items-center gap-1 font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700/60 border border-slate-300 dark:border-slate-600 px-2 py-0.5 rounded tabular-nums">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-600 dark:text-slate-400" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M12 7v5l3 3"/></svg>
                                                                {{ substr($item->jam_mulai, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }}
                                                            </span>
                                                            @if($isMatchFilter)
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-400 dark:border-emerald-500/50">
                                                                    Sesuai filter
                                                                </span>
                                                            @endif
                                                        </div>

                                                        {{-- Mata Pelajaran --}}
                                                        <h5 class="font-bold text-base text-slate-900 dark:text-white">
                                                            {{ $item->mapel->nama }}
                                                        </h5>

                                                        {{-- Guru Pengampu --}}
                                                        <div class="mt-2 pt-2 border-t-2 border-slate-200 dark:border-slate-700 flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-600 dark:text-slate-400 shrink-0" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg>
                                                            <span class="truncate font-semibold text-slate-900 dark:text-slate-100" title="{{ $item->guru->user->name }}">
                                                                {{ $item->guru->user->name }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
