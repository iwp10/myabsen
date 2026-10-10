<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Jadwal Pelajaran') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Banner Header / Info Siswa & Periode Aktif -->
            <div class="bg-blue-600 dark:bg-blue-700 rounded-2xl p-6 text-white shadow-md relative overflow-hidden" style="background-color: #2563eb;">
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

            <!-- Kontainer Jadwal & Filter -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"/><path d="M16 3v4"/><path d="M8 3v4"/><path d="M4 11h16"/><path d="M11 15h1"/><path d="M12 15v3"/></svg>
                            Jadwal Mingguan Pelajaran
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Dikelompokkan berdasarkan hari dan diurutkan sesuai jam pelajaran
                        </p>
                    </div>

                    {{-- Filter Hari --}}
                    <div class="flex items-center gap-2">
                        <form method="GET" action="{{ route('siswa.jadwal') }}" class="flex items-center gap-2">
                            <label for="filter-hari" class="text-xs font-medium text-gray-600 dark:text-gray-400 sr-only">Filter Hari</label>
                            <select id="filter-hari" name="hari" onchange="this.form.submit()" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-xs py-1.5 px-3">
                                <option value="">Semua Hari</option>
                                @foreach(['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ strtolower((string)$filterHari) === $val ? 'selected' : '' }}>
                                        {{ $lbl }} {{ strtolower($hariIni) === $val ? '(Hari Ini)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @if(!empty($filterHari))
                                <a href="{{ route('siswa.jadwal') }}" class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 px-2 py-1.5 border border-gray-300 dark:border-gray-700 rounded-md">
                                    Reset
                                </a>
                            @endif
                        </form>
                    </div>
                </div>

                {{-- Isi Konten --}}
                <div class="p-6">
                    @if(! $siswa || ! $siswa->kelas_id)
                        {{-- Empty State: Siswa Belum Ada Kelas --}}
                        <div class="py-12 text-center px-4">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-amber-50 dark:bg-amber-950/40 text-amber-500 dark:text-amber-400 mb-3">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100">Belum Terdaftar di Kelas</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-md mx-auto">
                                Akun Anda belum terhubung dengan kelas aktif manapun. Silakan hubungi bagian kurikulum atau wali kelas untuk penempatan kelas Anda.
                            </p>
                        </div>
                    @elseif($totalJadwal === 0)
                        {{-- Empty State: Kelas Belum Ada Jadwal / Hari Kosong --}}
                        <div class="py-12 text-center px-4">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-500 dark:text-blue-400 mb-3">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                @if(!empty($filterHari))
                                    Tidak Ada Pelajaran di Hari {{ ucfirst($filterHari) }}
                                @else
                                    Belum Ada Jadwal Pelajaran
                                @endif
                            </h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-md mx-auto">
                                @if(!empty($filterHari))
                                    Kelas Anda tidak memiliki jadwal pelajaran pada hari {{ ucfirst($filterHari) }}. Coba pilih hari lain atau tampilkan seluruh hari.
                                @else
                                    Jadwal pelajaran untuk kelas Anda pada periode ini belum diterbitkan oleh bagian kurikulum.
                                @endif
                            </p>
                            @if(!empty($filterHari))
                                <div class="mt-4">
                                    <a href="{{ route('siswa.jadwal') }}" class="inline-flex items-center px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-md transition">
                                        Tampilkan Semua Hari
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
                                    <div class="rounded-xl border transition-all duration-150 overflow-hidden {{ $isHariIni ? 'border-blue-300 dark:border-blue-700 bg-blue-50/30 dark:bg-blue-950/20 shadow-xs' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50' }}">
                                        {{-- Header Kelompok Hari --}}
                                        <div class="px-5 py-3.5 border-b flex items-center justify-between {{ $isHariIni ? 'border-blue-200 dark:border-blue-800 bg-blue-100/60 dark:bg-blue-900/40 text-blue-900 dark:text-blue-100' : 'border-gray-200 dark:border-gray-700 bg-gray-100/70 dark:bg-gray-750 text-gray-800 dark:text-gray-200' }}">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-sm sm:text-base capitalize">
                                                    Hari {{ ucfirst($hariNama) }}
                                                </span>
                                                @if($isHariIni)
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-600 text-white dark:bg-blue-500">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        Hari ini
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                {{ $jadwalsHari->count() }} Mata Pelajaran
                                            </span>
                                        </div>

                                        {{-- Kartu / Baris Jadwal Pada Hari Ini --}}
                                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                            @foreach($jadwalsHari as $item)
                                                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 shadow-2xs hover:shadow-xs transition duration-150 flex flex-col justify-between">
                                                    <div>
                                                        {{-- Jam Pelajaran --}}
                                                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-2">
                                                            <span class="inline-flex items-center gap-1 font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/60 px-2 py-0.5 rounded">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M12 7v5l3 3"/></svg>
                                                                {{ substr($item->jam_mulai, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }}
                                                            </span>
                                                        </div>

                                                        {{-- Mata Pelajaran --}}
                                                        <h5 class="font-bold text-base text-gray-900 dark:text-gray-100">
                                                            {{ $item->mapel->nama }}
                                                        </h5>

                                                        {{-- Guru Pengampu --}}
                                                        <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700 flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 shrink-0" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg>
                                                            <span class="truncate font-medium" title="{{ $item->guru->user->name }}">
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
