<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 dark:text-white leading-tight">
            {{ __('Jadwal Mengajar') }}
        </h2>
    </x-slot>

    @php
        $daftarHariSekolah = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu'];
        $jadwalPerHari = $jadwals->groupBy(fn($j) => strtolower($j->hari));
        $hariAktif = strtolower($hariIni);
        $adaFilter = !empty($filterHari) || !empty($filterKelasId) || !empty($filterMapelId);
        $hariTampil = $jadwalPerHari->keys()->all();
        $hanyaSatuHari = count($hariTampil) === 1;

        // Data filter aktif untuk chip di x-ui.filter-panel
        $activeFilters = [];
        if (!empty($filterHari)) {
            $activeFilters['hari'] = [
                'label' => 'Hari: ' . ucfirst($filterHari),
                'clear_url' => route('guru.jadwal', array_filter(['kelas_id' => $filterKelasId, 'mapel_id' => $filterMapelId])),
            ];
        }
        if (!empty($filterKelasId) && $activeKelas) {
            $activeFilters['kelas_id'] = [
                'label' => 'Kelas: ' . $activeKelas['nama'],
                'clear_url' => route('guru.jadwal', array_filter(['hari' => $filterHari, 'mapel_id' => $filterMapelId])),
            ];
        }
        if (!empty($filterMapelId) && $activeMapel) {
            $activeFilters['mapel_id'] = [
                'label' => 'Mapel: ' . $activeMapel['nama'],
                'clear_url' => route('guru.jadwal', array_filter(['hari' => $filterHari, 'kelas_id' => $filterKelasId])),
            ];
        }
    @endphp

    <div class="py-4 sm:py-8"
         x-data="{
             openDays: {
                 @foreach($daftarHariSekolah as $kodeHari => $namaHari)
                     '{{ $kodeHari }}': {{ ($adaFilter || $hanyaSatuHari || $kodeHari === $hariAktif) ? 'true' : 'false' }},
                 @endforeach
             },
             toggleDay(day) {
                 this.openDays[day] = !this.openDays[day];
             }
         }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            @if($errors->any())
                <x-alert type="danger">
                    {{ $errors->first() }}
                </x-alert>
            @endif

            <!-- Banner Header Kompak -->
            <div class="bg-emerald-700 dark:bg-emerald-800 rounded-2xl p-4 sm:p-6 text-white shadow-md relative overflow-hidden border-2 border-emerald-800 dark:border-emerald-700">
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white mb-1.5">
                            Tahun Ajaran Aktif &bull; SMK Mandiri 02 Balaraja
                        </span>
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Jadwal Mengajar Guru</h1>
                        <p class="text-white/90 text-xs sm:text-sm mt-0.5">
                            Seluruh Jadwal Mengajar Seminggu &bull; {{ Auth::user()->name }} &bull; NIP: {{ $guru?->nip ?? '-' }}
                        </p>
                    </div>
                    <div class="flex items-center justify-between sm:flex-col sm:items-end pt-2 sm:pt-0 border-t border-white/20 sm:border-0">
                        <span class="text-xs text-white/90 uppercase font-medium tracking-wider">Total Jadwal</span>
                        <span class="text-2xl sm:text-3xl font-extrabold text-white">
                            {{ $jadwals->count() }} <span class="text-xs font-normal">Sesi/Minggu</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Panel Filter Mengajar (Mobile Lipat + Chip Aktif, Desktop Inline) -->
            <x-ui.filter-panel :action="route('guru.jadwal')" :active-filters="$activeFilters" :reset-url="route('guru.jadwal')">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    {{-- Dropdown Hari --}}
                    <div>
                        <label for="filter-guru-hari" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Hari</label>
                        <x-ui.select id="filter-guru-hari" name="hari" onchange="this.form.submit()">
                            <option value="">Semua Hari</option>
                            @foreach($daftarHariSekolah as $v => $l)
                                <option value="{{ $v }}" {{ strtolower((string)$filterHari) === $v ? 'selected' : '' }}>
                                    {{ $l }} {{ $hariAktif === $v ? '(Hari Ini)' : '' }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    {{-- Dropdown Kelas --}}
                    <div>
                        <label for="filter-guru-kelas" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kelas</label>
                        <x-ui.select id="filter-guru-kelas" name="kelas_id" onchange="this.form.submit()">
                            <option value="">Semua Kelas</option>
                            @foreach($daftarKelas as $k)
                                <option value="{{ $k['id'] }}" {{ $filterKelasId == $k['id'] ? 'selected' : '' }}>
                                    {{ $k['nama'] }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    {{-- Dropdown Mata Pelajaran --}}
                    <div>
                        <label for="filter-guru-mapel" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Mata Pelajaran</label>
                        <x-ui.select id="filter-guru-mapel" name="mapel_id" onchange="this.form.submit()">
                            <option value="">Semua Mapel</option>
                            @foreach($daftarMapel as $m)
                                <option value="{{ $m['id'] }}" {{ $filterMapelId == $m['id'] ? 'selected' : '' }}>
                                    {{ $m['nama'] }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>
                </div>
            </x-ui.filter-panel>

            <!-- Daftar Jadwal Dikelompokkan Per Hari dengan Accordion -->
            @if($jadwals->isEmpty())
                <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border-2 border-slate-300 dark:border-slate-600 p-8 text-center">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 mb-3 border-2 border-slate-300 dark:border-slate-700">
                        <x-ui.icon name="calendar" size="lg" />
                    </div>
                    <p class="text-base font-bold text-slate-900 dark:text-white">
                        @if($adaFilter)
                            Tidak Ada Jadwal Mengajar yang Cocok
                        @else
                            Belum Ada Jadwal Mengajar
                        @endif
                    </p>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1 max-w-md mx-auto">
                        @if($adaFilter)
                            Tidak ditemukan jadwal mengajar yang sesuai dengan filter pencarian. Coba ubah atau reset filter.
                        @else
                            Anda belum memiliki jadwal mengajar yang terdaftar dalam sistem.
                        @endif
                    </p>
                    <div class="mt-5 flex justify-center gap-3 flex-wrap">
                        @if($adaFilter)
                            <a href="{{ route('guru.jadwal') }}" class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-slate-800 border-2 border-slate-400 dark:border-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-900 dark:text-slate-100 text-xs font-bold uppercase tracking-widest rounded-xl shadow-xs transition min-h-[44px]">
                                Reset Semua Filter
                            </a>
                        @endif
                        <a href="{{ route('guru.dashboard') }}" class="inline-flex items-center px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950 text-xs font-bold uppercase tracking-widest rounded-xl shadow-xs transition gap-2 min-h-[44px]">
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($daftarHariSekolah as $kodeHari => $namaHari)
                        @php
                            $listJadwalHari = $jadwalPerHari->get($kodeHari, collect());
                            $isHariIni = ($kodeHari === $hariAktif);
                        @endphp

                        @if($listJadwalHari->isNotEmpty())
                            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border-2 border-slate-300 dark:border-slate-600 overflow-hidden">
                                {{-- Header Accordion Hari --}}
                                <button type="button"
                                        @click="toggleDay('{{ $kodeHari }}')"
                                        class="w-full p-4 border-b-2 border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 flex items-center justify-between gap-3 text-left transition hover:bg-slate-200/70 dark:hover:bg-slate-700/70 min-h-[44px]"
                                        :aria-expanded="openDays['{{ $kodeHari }}'] ? 'true' : 'false'">
                                    <div class="flex items-center gap-2.5 flex-wrap">
                                        <h3 class="text-base font-bold text-slate-900 dark:text-white">
                                            {{ $namaHari }}
                                        </h3>
                                        @if($isHariIni)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-700 text-white dark:bg-emerald-400 dark:text-slate-950 shadow-xs">
                                                <x-ui.icon name="clock" size="sm" class="w-3.5 h-3.5" />
                                                <span>Hari ini</span>
                                            </span>
                                        @endif
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-white text-slate-700 dark:bg-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600">
                                            {{ $listJadwalHari->count() }} Sesi
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-400 hidden sm:inline"
                                              x-text="openDays['{{ $kodeHari }}'] ? 'Tutup' : 'Buka'"></span>
                                        <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 transition-transform duration-200"
                                             :class="openDays['{{ $kodeHari }}'] ? 'rotate-180' : ''"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </button>

                                {{-- Isi Kartu Jadwal pada Hari ini --}}
                                <div x-show="openDays['{{ $kodeHari }}']"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 -translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     class="p-4 sm:p-5">
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                                        @foreach($listJadwalHari as $jadwal)
                                            @php
                                                $isMatchFilter = ($filterKelasId && $jadwal->kelas_id == $filterKelasId) || ($filterMapelId && $jadwal->mapel_id == $filterMapelId);
                                                $linkTanggal = $jadwal->target_tanggal ?: \Carbon\Carbon::now('Asia/Jakarta')->toDateString();

                                                if ($isMatchFilter) {
                                                    $cardBorderClasses = 'border-2 border-l-8 border-l-emerald-600 dark:border-l-emerald-400 border-emerald-400 dark:border-emerald-500/50 bg-emerald-50/50 dark:bg-emerald-500/10';
                                                } elseif ($isHariIni) {
                                                    $cardBorderClasses = 'border-2 border-l-8 border-l-emerald-600 dark:border-l-emerald-400 border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800';
                                                } else {
                                                    $cardBorderClasses = 'border-2 border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 hover:border-slate-400 dark:hover:border-slate-500';
                                                }
                                            @endphp
                                            <div class="rounded-xl p-4 sm:p-5 transition shadow-xs flex flex-col justify-between {{ $cardBorderClasses }}">
                                                <div>
                                                    <!-- Jam & Status Badge -->
                                                    <div class="flex items-center justify-between gap-2 mb-2">
                                                        <span class="whitespace-nowrap text-xs font-mono font-bold bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 py-1 px-2.5 rounded-md">
                                                            {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB
                                                        </span>
                                                        @if($jadwal->status_absensi === 'Sudah diabsen')
                                                            <x-ui.status-badge status="sudah_diabsen" label="Sudah diabsen" />
                                                        @elseif($jadwal->status_absensi === 'Hari ini')
                                                            <x-ui.status-badge status="belum_diabsen" label="Hari ini" />
                                                        @else
                                                            <x-ui.status-badge status="belum_diabsen" label="Belum diabsen" />
                                                        @endif
                                                    </div>

                                                    <!-- Mata Pelajaran & Kelas -->
                                                    <div class="mb-3">
                                                        <h4 class="font-bold text-base sm:text-lg text-slate-900 dark:text-white leading-snug">
                                                            {{ $jadwal->mapel->nama }}
                                                        </h4>
                                                        <p class="text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 mt-1">
                                                            Kelas: <strong class="text-slate-900 dark:text-white">{{ $jadwal->kelas->nama }}</strong>
                                                            @if($jadwal->kelas->jurusan)
                                                                <span class="text-xs text-slate-600 dark:text-slate-400 font-normal">({{ $jadwal->kelas->jurusan->nama }})</span>
                                                            @endif
                                                        </p>
                                                    </div>

                                                    <!-- Tanggal Sesi -->
                                                    <div class="text-xs text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-700/50 p-2 rounded-lg border border-slate-200 dark:border-slate-700 flex items-center justify-between mb-3">
                                                        <span>Tanggal Sesi:</span>
                                                        <span class="font-bold text-slate-900 dark:text-white">
                                                            {{ $jadwal->target_tanggal ? \Carbon\Carbon::parse($jadwal->target_tanggal, 'Asia/Jakarta')->isoFormat('D MMM YYYY') : '-' }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- Tombol Aksi Lebar Penuh min 44px -->
                                                <a href="{{ route('guru.absensi.show', ['jadwal' => $jadwal->id, 'tanggal' => $linkTanggal]) }}"
                                                   class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 min-h-[44px] rounded-xl font-bold text-xs uppercase tracking-wider transition shadow-xs {{ $jadwal->status_absensi === 'Sudah diabsen' ? 'bg-white hover:bg-slate-100 text-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-white border-2 border-slate-300 dark:border-slate-500' : 'bg-emerald-700 hover:bg-emerald-800 text-white dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950' }}">
                                                    <span>
                                                        @if($jadwal->status_absensi === 'Sudah diabsen')
                                                            Koreksi Absensi &rarr;
                                                        @elseif($jadwal->status_absensi === 'Hari ini')
                                                            Absen Sekarang &rarr;
                                                        @else
                                                            Isi Susulan &rarr;
                                                        @endif
                                                    </span>
                                                </a>
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
