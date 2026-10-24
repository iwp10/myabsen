<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 dark:text-white leading-tight">
            {{ __('Pilih Kelas & Mapel') }}
        </h2>
    </x-slot>

    @php
        $activeFilters = [];
        if (!empty($search)) {
            $activeFilters['q'] = [
                'label' => 'Pencarian: ' . $search,
                'clear_url' => route('guru.riwayat', array_filter(['periode' => $filterPeriode])),
            ];
        }
        if (!empty($filterPeriode) && $filterPeriode !== ($activePeriode['tahun_ajaran'] . '|' . $activePeriode['semester'])) {
            $activeFilters['periode'] = [
                'label' => 'Periode: ' . $selectedPeriode['tahun_ajaran'] . ' Sem ' . $selectedPeriode['semester'],
                'clear_url' => route('guru.riwayat', array_filter(['q' => $search])),
            ];
        }
    @endphp

    <div class="py-4 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            <!-- Banner Header Riwayat Kompak -->
            <div class="bg-white dark:bg-slate-900 shadow-xs rounded-xl p-4 sm:p-5 border-2 border-slate-300 dark:border-slate-600">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                            Riwayat Absensi Guru
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-0.5">
                            Periode: <strong class="text-slate-900 dark:text-white font-bold">{{ $selectedPeriode['tahun_ajaran'] }} - Semester {{ $selectedPeriode['semester'] }}</strong>
                            @if($selectedPeriode['tahun_ajaran'] === $activePeriode['tahun_ajaran'] && $selectedPeriode['semester'] === $activePeriode['semester'])
                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-900 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-400 dark:border-emerald-500/50">
                                    (aktif)
                                </span>
                            @endif
                        </p>
                    </div>

                    <div class="text-xs text-slate-600 dark:text-slate-400">
                        Total <strong class="text-slate-900 dark:text-white font-bold">{{ $jadwalList->count() }}</strong> kelas &amp; mapel diajar
                    </div>
                </div>
            </div>

            <!-- Panel Filter & Pencarian Terlipat Mobile -->
            <x-ui.filter-panel :action="route('guru.riwayat')" :active-filters="$activeFilters" :reset-url="route('guru.riwayat')">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-end">
                    <div>
                        <label for="filter-riwayat-periode" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Periode Tahun Ajaran</label>
                        <x-ui.select id="filter-riwayat-periode" name="periode">
                            @foreach($daftarPeriode as $item)
                                <option value="{{ $item['value'] }}" {{ $filterPeriode === $item['value'] ? 'selected' : '' }}>
                                    {{ $item['label'] }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div>
                        <label for="filter-riwayat-q" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Cari Kelas atau Mapel</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-600 dark:text-slate-400">
                                <x-ui.icon name="search" size="sm" class="w-4 h-4" />
                            </div>
                            <input type="text"
                                   id="filter-riwayat-q"
                                   name="q"
                                   value="{{ $search }}"
                                   placeholder="Cari kelas atau mapel..."
                                   class="block w-full pl-9 pr-3 py-2 border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 placeholder:text-slate-500 dark:placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-emerald-500/20 rounded-xl text-base sm:text-xs min-h-[44px] font-medium shadow-xs">
                        </div>
                    </div>
                </div>
            </x-ui.filter-panel>

            <!-- Grid Kartu Kelas & Mapel (Seluruh Kartu Bisa Diketuk) -->
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
                            Silakan pilih periode lain melalui menu pilihan periode di atas.
                        @endif
                    </x-slot>
                </x-ui.empty-state>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    @foreach($jadwalList as $jadwal)
                        <a href="{{ route('guru.riwayat.detail', ['kelas' => $jadwal->kelas_id, 'mapel' => $jadwal->mapel_id, 'tahun_ajaran' => $selectedPeriode['tahun_ajaran'], 'semester' => $selectedPeriode['semester']]) }}"
                           class="group block bg-white dark:bg-slate-800 rounded-xl p-4 sm:p-5 border-2 border-slate-300 dark:border-slate-600 shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5 hover:border-emerald-500 dark:hover:border-emerald-400 transition flex flex-col justify-between min-h-[120px]">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <h3 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">
                                        {{ $jadwal->kelas->nama }}
                                    </h3>
                                    <x-ui.icon name="chevron-right" size="sm" class="text-slate-600 dark:text-slate-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 group-hover:translate-x-1 transition-all" />
                                </div>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $jadwal->mapel->nama }}
                                </p>
                            </div>

                            <div class="mt-4 pt-3 border-t-2 border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs text-slate-600 dark:text-slate-400 font-medium">
                                <span>Lihat rekapitulasi</span>
                                <span class="font-bold text-emerald-700 dark:text-emerald-400 group-hover:underline">
                                    Buka Detail &rarr;
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
