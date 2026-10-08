<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Pilih Kelas & Mapel
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Filter Periode dan Kotak Pencarian --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 border border-gray-100 dark:border-gray-700">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                            Riwayat Absensi
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            Periode Dilihat:
                            <span class="font-semibold text-blue-600 dark:text-blue-400">
                                {{ $selectedPeriode['tahun_ajaran'] }} - Semester {{ $selectedPeriode['semester'] }}
                            </span>
                            @if($selectedPeriode['tahun_ajaran'] === $activePeriode['tahun_ajaran'] && $selectedPeriode['semester'] === $activePeriode['semester'])
                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                    (aktif)
                                </span>
                            @endif
                        </p>
                    </div>

                    <form action="{{ route('guru.riwayat') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto">
                        <select name="periode" class="w-full sm:w-60 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                            @foreach($daftarPeriode as $item)
                                <option value="{{ $item['value'] }}" {{ $filterPeriode === $item['value'] ? 'selected' : '' }}>
                                    {{ $item['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <input type="text" name="q" value="{{ $search }}" placeholder="Cari kelas atau mata pelajaran..." class="w-full sm:w-64 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md text-sm transition text-center">
                            Cari
                        </button>
                        @if($search !== '' || (request()->filled('periode') && request('periode') !== ($activePeriode['tahun_ajaran'].'|'.$activePeriode['semester'])) || request()->filled('tahun_ajaran') || request()->filled('semester'))
                            <a href="{{ route('guru.riwayat') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-3 rounded-md text-sm flex items-center justify-center transition">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            @if($jadwalList->isEmpty())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-10 text-center text-gray-500 dark:text-gray-400 border border-gray-100 dark:border-gray-700">
                    <svg class="mx-auto h-12 w-12 mb-3 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    @if($search !== '')
                        <p class="font-medium text-gray-700 dark:text-gray-300">Tidak ada hasil untuk '{{ $search }}'.</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Coba gunakan kata kunci kelas atau mata pelajaran yang lain.</p>
                    @else
                        <p class="font-medium text-gray-700 dark:text-gray-300">Tidak ada kelas dan mata pelajaran pada periode ini.</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Silakan pilih periode lain melalui menu dropdown di atas.</p>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($jadwalList as $jadwal)
                        <a href="{{ route('guru.riwayat.detail', ['kelas' => $jadwal->kelas_id, 'mapel' => $jadwal->mapel_id, 'tahun_ajaran' => $selectedPeriode['tahun_ajaran'], 'semester' => $selectedPeriode['semester']]) }}"
                           class="block bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-shadow sm:rounded-lg overflow-hidden border border-gray-100 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-700">
                            <div class="px-6 py-5">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                        {{ $jadwal->kelas->nama }}
                                    </h3>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-hover:text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-blue-600 dark:text-blue-400 mb-1">
                                    {{ $jadwal->mapel->nama }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
