<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Mata Pelajaran') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Banner Header / Info Siswa & Periode Aktif -->
            <div class="bg-blue-600 dark:bg-blue-700 rounded-2xl p-6 text-white shadow-md relative overflow-hidden" style="background-color: #2563eb;">
                <div class="absolute -right-8 -bottom-10 opacity-10 pointer-events-none">
                    <svg class="w-56 h-56 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82z"/>
                    </svg>
                </div>
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white mb-2">
                            SMK Mandiri 02 Balaraja &bull; Portal Siswa
                        </span>
                        <h1 class="text-2xl font-bold tracking-tight text-white">Mata Pelajaran Kelas</h1>
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
                            Total: {{ $mapels->count() }} Mata Pelajaran
                        </span>
                    </div>
                </div>
            </div>

            <!-- Kontainer Daftar Mata Pelajaran -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg>
                            Daftar Mata Pelajaran
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Mata pelajaran yang diajarkan di kelas Anda pada semester ini. Klik pada kartu untuk melihat jadwal pelajaran.
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('siswa.jadwal') }}" class="inline-flex items-center text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 gap-1">
                            Lihat Jadwal Lengkap &rarr;
                        </a>
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
                    @elseif($mapels->isEmpty())
                        {{-- Empty State: Belum Ada Mata Pelajaran --}}
                        <div class="py-12 text-center px-4">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-500 dark:text-blue-400 mb-3">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100">Belum Ada Mata Pelajaran</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-md mx-auto">
                                Kelas Anda belum memiliki jadwal mata pelajaran pada periode aktif ini.
                            </p>
                        </div>
                    @else
                        {{-- Grid Kartu Mata Pelajaran --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                            @foreach($mapels as $item)
                                <a href="{{ route('siswa.jadwal', ['mapel_id' => $item['mapel']->id]) }}"
                                   class="group block bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-2xs hover:shadow-md hover:border-blue-500 dark:hover:border-blue-400 transition duration-150 flex flex-col justify-between focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400">
                                    <div>
                                        {{-- Header Kartu: Kode Mapel & Total Sesi --}}
                                        <div class="flex items-center justify-between gap-2 mb-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                {{ $item['mapel']->kode }}
                                            </span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                {{ $item['total_sesi'] }} sesi/minggu
                                            </span>
                                        </div>

                                        {{-- Nama Mapel --}}
                                        <h4 class="font-bold text-lg text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                            {{ $item['mapel']->nama }}
                                        </h4>

                                        {{-- Guru Pengampu --}}
                                        <div class="mt-2.5 flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 dark:text-gray-500 shrink-0" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg>
                                            <span class="truncate font-medium text-gray-700 dark:text-gray-200" title="{{ $item['guru_nama'] }}">
                                                {{ $item['guru_nama'] }}
                                            </span>
                                        </div>

                                        {{-- Ringkasan Jadwal --}}
                                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/80">
                                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-2">Jadwal Pertemuan:</span>
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach($item['jadwals'] as $jStr)
                                                    <span class="inline-flex items-center gap-1 text-xs text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/60 px-2 py-1 rounded border border-gray-200 dark:border-gray-700">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-blue-500 dark:text-blue-400" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M12 7v5l3 3"/></svg>
                                                        {{ $jStr }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Footer Aksi --}}
                                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/80 flex items-center justify-between text-xs text-blue-600 dark:text-blue-400 font-semibold group-hover:underline">
                                        <span>Lihat di Jadwal Pelajaran</span>
                                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
