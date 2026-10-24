<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 dark:text-white leading-tight">
            {{ __('Jadwal Pelajaran') }}
        </h2>
    </x-slot>

    @php
        $urutanHari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
        $daftarHariLabel = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu'];
        $hariAktif = strtolower($hariIni);
        $adaFilter = !empty($filterHari) || !empty($filterMapelId);

        if (!empty($filterHari) && in_array(strtolower($filterHari), $urutanHari)) {
            $daftarHariTampil = [strtolower($filterHari)];
        } else {
            $daftarHariTampil = $urutanHari;
        }

        $hanyaSatuHari = count($daftarHariTampil) === 1;

        // Data filter aktif untuk chip
        $activeFilters = [];
        if (!empty($filterHari)) {
            $activeFilters['hari'] = [
                'label' => 'Hari: ' . ucfirst($filterHari),
                'clear_url' => route('siswa.jadwal', array_filter(['mapel_id' => $filterMapelId])),
            ];
        }
        if (!empty($filterMapelId) && $activeMapel) {
            $activeFilters['mapel_id'] = [
                'label' => 'Mata pelajaran: ' . $activeMapel->nama,
                'clear_url' => route('siswa.jadwal', array_filter(['hari' => $filterHari])),
            ];
        }
    @endphp

    <div class="py-4 sm:py-8"
         x-data="{
             openDays: {
                 @foreach($urutanHari as $hariKode)
                     '{{ $hariKode }}': {{ ($adaFilter || $hanyaSatuHari || $hariKode === $hariAktif) ? 'true' : 'false' }},
                 @endforeach
             },
             toggleDay(day) {
                 this.openDays[day] = !this.openDays[day];
             }
         }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            <!-- Banner Header Kompak -->
            <div class="bg-blue-600 dark:bg-blue-700 rounded-2xl p-4 sm:p-6 text-white shadow-md relative overflow-hidden border-2 border-blue-700 dark:border-blue-600">
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white mb-1.5">
                            SMK Mandiri 02 Balaraja &bull; Portal Siswa
                        </span>
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Jadwal Pelajaran Kelas</h1>
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
                            Total: {{ $totalJadwal }} Mata Pelajaran
                        </span>
                    </div>
                </div>
            </div>

            <!-- Error Alert -->
            @if($errors->any())
                <x-alert type="danger" :dismissible="true">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-alert>
            @endif

            <!-- Panel Filter Hari & Mapel (Mobile Lipat, Desktop Inline) -->
            <x-ui.filter-panel :action="route('siswa.jadwal')" :active-filters="$activeFilters" :reset-url="route('siswa.jadwal')">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-end">
                    <div>
                        <label for="filter-hari" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Hari</label>
                        <x-ui.select id="filter-hari" name="hari" onchange="this.form.submit()">
                            <option value="">Semua Hari</option>
                            @foreach($daftarHariLabel as $val => $lbl)
                                <option value="{{ $val }}" {{ strtolower((string)$filterHari) === $val ? 'selected' : '' }}>
                                    {{ $lbl }} {{ $hariAktif === $val ? '(Hari Ini)' : '' }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div>
                        <label for="filter-mapel" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Mata Pelajaran</label>
                        <x-ui.select id="filter-mapel" name="mapel_id" onchange="this.form.submit()">
                            <option value="">Semua Mapel</option>
                            @foreach($daftarMapel as $m)
                                <option value="{{ $m->id }}" {{ $filterMapelId == $m->id ? 'selected' : '' }}>
                                    {{ $m->nama }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>
                </div>
            </x-ui.filter-panel>

            <!-- Isi Jadwal Siswa -->
            @if(! $siswa || ! $siswa->kelas_id)
                {{-- Empty State: Siswa Belum Ada Kelas --}}
                <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border-2 border-slate-300 dark:border-slate-600 p-8 text-center">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-amber-100 dark:bg-amber-500/15 text-amber-900 dark:text-amber-300 mb-3 border-2 border-amber-400 dark:border-amber-500/50">
                        <x-ui.icon name="info" size="lg" />
                    </div>
                    <h4 class="text-base font-bold text-slate-900 dark:text-white">Belum Terdaftar di Kelas</h4>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1 max-w-md mx-auto">
                        Akun Anda belum terhubung dengan kelas aktif manapun. Silakan hubungi bagian kurikulum atau wali kelas untuk penempatan kelas.
                    </p>
                </div>
            @elseif($totalJadwal === 0)
                {{-- Empty State: Belum Ada Jadwal / Filter Kosong --}}
                <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border-2 border-slate-300 dark:border-slate-600 p-8 text-center">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-100 dark:bg-blue-500/15 text-blue-900 dark:text-blue-300 mb-3 border-2 border-blue-400 dark:border-blue-500/50">
                        <x-ui.icon name="calendar" size="lg" />
                    </div>
                    <h4 class="text-base font-bold text-slate-900 dark:text-white">
                        @if($adaFilter)
                            Tidak Ada Jadwal Pelajaran yang Cocok
                        @else
                            Belum Ada Jadwal Pelajaran
                        @endif
                    </h4>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1 max-w-md mx-auto">
                        @if($adaFilter)
                            Tidak ditemukan jadwal pelajaran yang sesuai dengan filter. Coba ubah atau reset filter.
                        @else
                            Jadwal pelajaran untuk kelas Anda pada periode ini belum diterbitkan oleh bagian kurikulum.
                        @endif
                    </p>
                    @if($adaFilter)
                        <div class="mt-4">
                            <a href="{{ route('siswa.jadwal') }}" class="inline-flex items-center px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950 text-xs font-bold uppercase tracking-wider rounded-xl transition min-h-[44px] shadow-xs">
                                Tampilkan Semua Jadwal
                            </a>
                        </div>
                    @endif
                </div>
            @else
                <!-- Daftar Jadwal Dikelompokkan Per Hari dengan Accordion -->
                <div class="space-y-3 sm:space-y-4">
                    @foreach($daftarHariTampil as $hariNama)
                        @php
                            $jadwalsHari = $jadwalsByHari->get($hariNama, collect());
                            $isHariIni = (strtolower($hariNama) === $hariAktif);
                        @endphp

                        @if($jadwalsHari->isNotEmpty())
                            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border-2 border-slate-300 dark:border-slate-600 overflow-hidden">
                                {{-- Header Accordion Kelompok Hari --}}
                                <button type="button"
                                        @click="toggleDay('{{ $hariNama }}')"
                                        class="w-full px-4 py-3 sm:px-5 sm:py-3.5 border-b-2 border-slate-300 dark:border-slate-600 flex items-center justify-between gap-3 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white text-left transition hover:bg-slate-200/70 dark:hover:bg-slate-700/70 min-h-[44px]"
                                        :aria-expanded="openDays['{{ $hariNama }}'] ? 'true' : 'false'">
                                    <div class="flex items-center gap-2.5 flex-wrap">
                                        <span class="font-bold text-sm sm:text-base capitalize text-slate-900 dark:text-white">
                                            Hari {{ $daftarHariLabel[$hariNama] ?? ucfirst($hariNama) }}
                                        </span>
                                        @if($isHariIni)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-700 text-white dark:bg-emerald-400 dark:text-slate-950 shadow-xs">
                                                <x-ui.icon name="clock" size="sm" class="w-3.5 h-3.5" />
                                                <span>Hari ini</span>
                                            </span>
                                        @endif
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-white text-slate-700 dark:bg-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600">
                                            {{ $jadwalsHari->count() }} Mata Pelajaran
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-400 hidden sm:inline"
                                              x-text="openDays['{{ $hariNama }}'] ? 'Tutup' : 'Buka'"></span>
                                        <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 transition-transform duration-200"
                                             :class="openDays['{{ $hariNama }}'] ? 'rotate-180' : ''"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </button>

                                {{-- Kartu Jadwal Pada Hari Ini --}}
                                <div x-show="openDays['{{ $hariNama }}']"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 -translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     class="p-4 sm:p-5">
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
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
                                                        <span class="inline-flex items-center gap-1 font-mono font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700/60 border border-slate-300 dark:border-slate-600 px-2 py-0.5 rounded tabular-nums">
                                                            <x-ui.icon name="clock" size="sm" class="w-3.5 h-3.5 text-slate-600 dark:text-slate-400" />
                                                            {{ substr($item->jam_mulai, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }} WIB
                                                        </span>
                                                        @if($isMatchFilter)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-400 dark:border-emerald-500/50">
                                                                Sesuai filter
                                                            </span>
                                                        @endif
                                                    </div>

                                                    {{-- Mata Pelajaran --}}
                                                    <h4 class="font-bold text-base text-slate-900 dark:text-white leading-snug">
                                                        {{ $item->mapel->nama }}
                                                    </h4>

                                                    {{-- Guru Pengampu --}}
                                                    <div class="mt-2 pt-2 border-t-2 border-slate-200 dark:border-slate-700 flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                                                        <x-ui.icon name="user" size="sm" class="w-4 h-4 text-slate-600 dark:text-slate-400 shrink-0" />
                                                        <span class="truncate font-semibold text-slate-900 dark:text-slate-100" title="{{ $item->guru->user->name }}">
                                                            {{ $item->guru->user->name }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
