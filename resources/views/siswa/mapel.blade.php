<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 dark:text-white leading-tight">
            {{ __('Mata Pelajaran') }}
        </h2>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            <!-- Banner Header Kompak -->
            <div class="bg-blue-600 dark:bg-blue-700 rounded-2xl p-4 sm:p-6 text-white shadow-md relative overflow-hidden border-2 border-blue-700 dark:border-blue-600">
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white mb-1.5">
                            SMK Mandiri 02 Balaraja &bull; Portal Siswa
                        </span>
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Mata Pelajaran Kelas</h1>
                        <p class="text-white/90 text-xs sm:text-sm mt-0.5">
                            Siswa: <strong class="text-white font-semibold">{{ Auth::user()->name }}</strong>
                            &bull; Kelas: <strong class="text-white font-semibold">{{ $siswa?->kelas?->nama ?? 'Belum ada kelas' }}</strong>
                            @if($siswa?->kelas?->jurusan)
                                <span class="text-white/80">({{ $siswa->kelas->jurusan->nama }})</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center justify-between sm:flex-col sm:items-end pt-2 sm:pt-0 border-t border-white/20 sm:border-0">
                        <span class="text-xs text-white/80 uppercase font-medium tracking-wider">Periode Aktif</span>
                        <span class="text-sm sm:text-base font-bold text-white">
                            {{ $activePeriode['tahun_ajaran'] ?? '-' }} &bull; Sem {{ $activePeriode['semester'] ?? '-' }}
                        </span>
                        <span class="text-xs text-white/90 block mt-0.5">
                            Total: {{ $mapels->count() }} Mata Pelajaran
                        </span>
                    </div>
                </div>
            </div>

            <!-- Kontainer Daftar Mata Pelajaran -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border-2 border-slate-300 dark:border-slate-600 overflow-hidden">
                <div class="p-4 sm:p-5 border-b-2 border-slate-300 dark:border-slate-600 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <x-ui.icon name="book" size="md" class="text-emerald-700 dark:text-emerald-400" />
                            <span>Daftar Mata Pelajaran</span>
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                            Ketuk kartu mata pelajaran untuk melihat jadwal pembelajarannya
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('siswa.jadwal') }}" class="inline-flex items-center text-xs font-bold text-emerald-700 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300 gap-1 min-h-[44px]">
                            <span>Lihat Jadwal Lengkap &rarr;</span>
                        </a>
                    </div>
                </div>

                {{-- Isi Konten --}}
                <div class="p-4 sm:p-6">
                    @if(! $siswa || ! $siswa->kelas_id)
                        {{-- Empty State: Siswa Belum Ada Kelas --}}
                        <div class="py-10 text-center px-4">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-amber-100 dark:bg-amber-500/15 text-amber-900 dark:text-amber-300 mb-3 border-2 border-amber-400 dark:border-amber-500/50">
                                <x-ui.icon name="info" size="lg" />
                            </div>
                            <h4 class="text-base font-bold text-slate-900 dark:text-white">Belum Terdaftar di Kelas</h4>
                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1 max-w-md mx-auto">
                                Akun Anda belum terhubung dengan kelas aktif manapun. Silakan hubungi kurikulum atau wali kelas.
                            </p>
                        </div>
                    @elseif($mapels->isEmpty())
                        {{-- Empty State: Belum Ada Mata Pelajaran --}}
                        <div class="py-10 text-center px-4">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-100 dark:bg-blue-500/15 text-blue-900 dark:text-blue-300 mb-3 border-2 border-blue-400 dark:border-blue-500/50">
                                <x-ui.icon name="book" size="lg" />
                            </div>
                            <h4 class="text-base font-bold text-slate-900 dark:text-white">Belum Ada Mata Pelajaran</h4>
                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1 max-w-md mx-auto">
                                Kelas Anda belum memiliki jadwal mata pelajaran pada periode aktif ini.
                            </p>
                        </div>
                    @else
                        {{-- Grid Kartu Mata Pelajaran (Seluruh Kartu Bisa Diketuk) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                            @foreach($mapels as $item)
                                <a href="{{ route('siswa.jadwal', ['mapel_id' => $item['mapel']->id]) }}"
                                   class="group block bg-white dark:bg-slate-800 rounded-xl p-4 sm:p-5 border-2 border-slate-300 dark:border-slate-600 shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5 hover:border-emerald-500 dark:hover:border-emerald-400 transition flex flex-col justify-between min-h-[140px] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500">
                                    <div>
                                        {{-- Header Kartu: Kode Mapel & Total Sesi --}}
                                        <div class="flex items-center justify-between gap-2 mb-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-mono font-bold bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600">
                                                {{ $item['mapel']->kode }}
                                            </span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600">
                                                {{ $item['total_sesi'] }} sesi/minggu
                                            </span>
                                        </div>

                                        {{-- Nama Mapel --}}
                                        <h4 class="font-bold text-base sm:text-lg text-slate-900 dark:text-white group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors leading-snug">
                                            {{ $item['mapel']->nama }}
                                        </h4>

                                        {{-- Guru Pengampu --}}
                                        <div class="mt-2 flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                                            <x-ui.icon name="user" size="sm" class="w-4 h-4 text-slate-600 dark:text-slate-400 shrink-0" />
                                            <span class="truncate font-semibold text-slate-900 dark:text-slate-100" title="{{ $item['guru_nama'] }}">
                                                {{ $item['guru_nama'] }}
                                            </span>
                                        </div>

                                        {{-- Ringkasan Jadwal --}}
                                        <div class="mt-3 pt-2.5 border-t-2 border-slate-200 dark:border-slate-700">
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 block mb-1.5">Jadwal Pertemuan:</span>
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach($item['jadwals'] as $jStr)
                                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700/60 px-2 py-0.5 rounded-lg border border-slate-300 dark:border-slate-600">
                                                        <x-ui.icon name="clock" size="sm" class="w-3.5 h-3.5 text-slate-600 dark:text-slate-400" />
                                                        <span>{{ $jStr }}</span>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Footer Aksi --}}
                                    <div class="mt-3 pt-2.5 border-t-2 border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs text-emerald-700 dark:text-emerald-400 font-bold group-hover:underline">
                                        <span>Buka di Jadwal Pelajaran</span>
                                        <x-ui.icon name="chevron-right" size="sm" class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
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
