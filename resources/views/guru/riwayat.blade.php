<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 dark:text-white leading-tight">
            Pilih Kelas & Mapel
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Filter Periode dan Kotak Pencarian --}}
            <div class="bg-white dark:bg-slate-900 shadow-xs rounded-xl p-5 sm:p-6 border-2 border-slate-300 dark:border-slate-600">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                            Riwayat Absensi
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-0.5">
                            Periode Dilihat:
                            <span class="font-bold text-slate-900 dark:text-slate-100">
                                {{ $selectedPeriode['tahun_ajaran'] }} - Semester {{ $selectedPeriode['semester'] }}
                            </span>
                            @if($selectedPeriode['tahun_ajaran'] === $activePeriode['tahun_ajaran'] && $selectedPeriode['semester'] === $activePeriode['semester'])
                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-400 dark:border-emerald-500/50">
                                    (aktif)
                                </span>
                            @endif
                        </p>
                    </div>

                    <form action="{{ route('guru.riwayat') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto">
                        <select name="periode" class="w-full sm:w-60 border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 dark:[color-scheme:dark] focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 rounded-lg text-base sm:text-xs min-h-[44px] py-2 px-3 font-medium">
                            @foreach($daftarPeriode as $item)
                                <option value="{{ $item['value'] }}" {{ $filterPeriode === $item['value'] ? 'selected' : '' }}>
                                    {{ $item['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <input type="text" name="q" value="{{ $search }}" placeholder="Cari kelas atau mapel..." class="w-full sm:w-64 border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 placeholder:text-slate-500 dark:placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 rounded-lg text-base sm:text-xs min-h-[44px] py-2 px-3 font-medium">
                        <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950 font-bold py-2 px-5 rounded-lg text-xs uppercase tracking-wider min-h-[44px] transition text-center shadow-xs">
                            Cari
                        </button>
                        @if($search !== '' || (request()->filled('periode') && request('periode') !== ($activePeriode['tahun_ajaran'].'|'.$activePeriode['semester'])) || request()->filled('tahun_ajaran') || request()->filled('semester'))
                            <a href="{{ route('guru.riwayat') }}" class="border-2 border-slate-400 dark:border-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold py-2 px-4 rounded-lg text-xs uppercase tracking-wider min-h-[44px] flex items-center justify-center transition shadow-xs">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            @if($jadwalList->isEmpty())
                <x-ui.empty-state>
                    <x-slot name="title">
                        @if($search !== '')
                            Tidak ada hasil untuk '{{ $search }}'.
                        @else
                            Tidak ada kelas dan mata pelajaran pada periode ini.
                        @endif
                    </x-slot>
                    <x-slot name="description">
                        @if($search !== '')
                            Coba gunakan kata kunci kelas atau mata pelajaran yang lain.
                        @else
                            Silakan pilih periode lain melalui menu dropdown di atas.
                        @endif
                    </x-slot>
                </x-ui.empty-state>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($jadwalList as $jadwal)
                        <a href="{{ route('guru.riwayat.detail', ['kelas' => $jadwal->kelas_id, 'mapel' => $jadwal->mapel_id, 'tahun_ajaran' => $selectedPeriode['tahun_ajaran'], 'semester' => $selectedPeriode['semester']]) }}"
                           class="block bg-white dark:bg-slate-800 shadow-xs hover:shadow-md transition rounded-xl overflow-hidden border-2 border-slate-300 dark:border-slate-600 dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5 hover:border-emerald-500 dark:hover:border-emerald-400 group p-5">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">
                                    {{ $jadwal->kelas->nama }}
                                </h3>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600 dark:text-slate-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                {{ $jadwal->mapel->nama }}
                            </p>
                        </a>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
