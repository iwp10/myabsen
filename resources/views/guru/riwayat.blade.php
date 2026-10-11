<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-900 dark:text-gray-100 leading-tight">
            Pilih Kelas & Mapel
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Filter Periode dan Kotak Pencarian --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 sm:p-6 border-2 border-gray-300 dark:border-gray-600">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                            Riwayat Absensi
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-0.5">
                            Periode Dilihat:
                            <span class="font-bold text-blue-600 dark:text-blue-400">
                                {{ $selectedPeriode['tahun_ajaran'] }} - Semester {{ $selectedPeriode['semester'] }}
                            </span>
                            @if($selectedPeriode['tahun_ajaran'] === $activePeriode['tahun_ajaran'] && $selectedPeriode['semester'] === $activePeriode['semester'])
                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700">
                                    (aktif)
                                </span>
                            @endif
                        </p>
                    </div>

                    <form action="{{ route('guru.riwayat') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto">
                        <select name="periode" class="w-full sm:w-60 border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 rounded-lg text-base sm:text-xs min-h-[44px] py-2 px-3 font-medium">
                            @foreach($daftarPeriode as $item)
                                <option value="{{ $item['value'] }}" {{ $filterPeriode === $item['value'] ? 'selected' : '' }}>
                                    {{ $item['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <input type="text" name="q" value="{{ $search }}" placeholder="Cari kelas atau mapel..." class="w-full sm:w-64 border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 rounded-lg text-base sm:text-xs min-h-[44px] py-2 px-3 font-medium">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-lg text-xs uppercase tracking-wider min-h-[44px] transition text-center shadow-xs">
                            Cari
                        </button>
                        @if($search !== '' || (request()->filled('periode') && request('periode') !== ($activePeriode['tahun_ajaran'].'|'.$activePeriode['semester'])) || request()->filled('tahun_ajaran') || request()->filled('semester'))
                            <a href="{{ route('guru.riwayat') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg text-xs uppercase tracking-wider min-h-[44px] flex items-center justify-center transition shadow-xs">
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
                           class="block bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition rounded-xl overflow-hidden border-2 border-gray-300 dark:border-gray-600 hover:border-blue-500 dark:hover:border-blue-400 group p-5">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                    {{ $jadwal->kelas->nama }}
                                </h3>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-hover:text-blue-500 group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                                {{ $jadwal->mapel->nama }}
                            </p>
                        </a>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
