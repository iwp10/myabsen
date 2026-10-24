<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 dark:text-white leading-tight">
            {{ __('Riwayat & Rekap Kehadiran') }}
        </h2>
    </x-slot>

    @php
        // Siapkan chip filter aktif untuk tab Semua Riwayat
        $activeFilters = [];
        if (request()->filled('periode') && request('periode') !== 'semua') {
            $activeFilters['periode'] = [
                'label' => 'Periode: ' . ($selectedPeriode['tahun_ajaran'] ?? '') . ' Sem ' . ($selectedPeriode['semester'] ?? ''),
                'clear_url' => route('siswa.riwayat', array_filter(request()->except('periode'))),
            ];
        }
        if (request()->filled('bulan')) {
            $namaBulan = \App\Support\Ui::namaBulanIndo((int) request('bulan'));
            $activeFilters['bulan'] = [
                'label' => 'Bulan: ' . $namaBulan,
                'clear_url' => route('siswa.riwayat', array_filter(request()->except('bulan'))),
            ];
        }
        if (request()->filled('tanggal')) {
            $activeFilters['tanggal'] = [
                'label' => 'Tanggal: ' . \Carbon\Carbon::parse(request('tanggal'))->format('d/m/Y'),
                'clear_url' => route('siswa.riwayat', array_filter(request()->except('tanggal'))),
            ];
        }
        if (request()->filled('mapel_id')) {
            $mObj = $mapels->firstWhere('id', request('mapel_id'));
            $activeFilters['mapel_id'] = [
                'label' => 'Mapel: ' . ($mObj->nama ?? request('mapel_id')),
                'clear_url' => route('siswa.riwayat', array_filter(request()->except('mapel_id'))),
            ];
        }
        if (request()->filled('status')) {
            $activeFilters['status'] = [
                'label' => 'Status: ' . ucfirst(request('status')),
                'clear_url' => route('siswa.riwayat', array_filter(request()->except('status'))),
            ];
        }
    @endphp

    <div class="py-4 sm:py-8" x-data="{ tab: '{{ $activeTab }}' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            <!-- Banner Info Periode Dilihat & Filter Periode -->
            <div class="bg-white dark:bg-slate-900 overflow-hidden shadow-xs rounded-xl p-4 sm:p-5 border-2 border-slate-300 dark:border-slate-600">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                            Riwayat Kehadiran Siswa
                        </h3>
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-0.5">
                            <span>Periode Dilihat:</span>
                            <strong class="font-bold text-slate-900 dark:text-white">
                                @if($filterPeriodeValue === 'semua')
                                    Semua Periode
                                @else
                                    {{ $selectedPeriode['tahun_ajaran'] }} - Semester {{ $selectedPeriode['semester'] }}
                                @endif
                            </strong>
                            @if($filterPeriodeValue !== 'semua' && isset($daftarPeriode) && collect($daftarPeriode)->firstWhere('is_aktif', true)['value'] === ($selectedPeriode['tahun_ajaran'].'|'.$selectedPeriode['semester']))
                                <span class="shrink-0 whitespace-nowrap inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-900 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-400 dark:border-emerald-500/50">
                                    (aktif)
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Dropdown Periode untuk Tab Per Mapel -->
                    <div x-show="tab === 'per_mapel'" class="w-full md:w-auto">
                        <form method="GET" action="{{ route('siswa.riwayat') }}" class="flex items-center gap-2 w-full md:w-auto">
                            <input type="hidden" name="tab" value="per_mapel">
                            <label for="periode_top" class="sr-only">Pilih Periode</label>
                            <x-ui.select id="periode_top" name="periode" onchange="this.form.submit()" wrapper-class="w-full sm:w-auto sm:min-w-[16rem]">
                                @foreach($daftarPeriode as $item)
                                    <option value="{{ $item['value'] }}" {{ $filterPeriodeValue === $item['value'] ? 'selected' : '' }}>
                                        {{ $item['label'] }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </form>
                    </div>
                </div>

                {{-- Tab Navigasi Segmen Ramah Sentuh (Mudah diketuk & tidak terpotong di 320px) --}}
                <div class="mt-4 pt-3 border-t-2 border-slate-200 dark:border-slate-700">
                    <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border-2 border-slate-300 dark:border-slate-600 max-w-md">
                        <button type="button"
                                @click="tab = 'per_mapel'"
                                :class="tab === 'per_mapel'
                                    ? 'bg-white dark:bg-slate-900 text-emerald-800 dark:text-emerald-300 shadow-xs border-2 border-emerald-500 dark:border-emerald-400 font-bold'
                                    : 'text-slate-700 dark:text-slate-300 font-semibold border-2 border-transparent hover:text-slate-900 dark:hover:text-white'"
                                class="py-2 px-2.5 rounded-lg text-xs sm:text-sm transition flex items-center justify-center gap-1.5 min-h-[44px]">
                            <x-ui.icon name="book" size="sm" class="shrink-0" />
                            <span class="truncate">Per Mata Pelajaran</span>
                        </button>
                        <button type="button"
                                @click="tab = 'semua'"
                                :class="tab === 'semua'
                                    ? 'bg-white dark:bg-slate-900 text-emerald-800 dark:text-emerald-300 shadow-xs border-2 border-emerald-500 dark:border-emerald-400 font-bold'
                                    : 'text-slate-700 dark:text-slate-300 font-semibold border-2 border-transparent hover:text-slate-900 dark:hover:text-white'"
                                class="py-2 px-2.5 rounded-lg text-xs sm:text-sm transition flex items-center justify-center gap-1.5 min-h-[44px]">
                            <x-ui.icon name="history" size="sm" class="shrink-0" />
                            <span class="truncate">Semua Riwayat</span>
                        </button>
                    </div>
                </div>
            </div>

            @if($errors->any())
                <x-alert type="danger" :dismissible="true">
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-alert>
            @endif

            {{-- KONTEN TAB 1: PER MATA PELAJARAN --}}
            <div x-show="tab === 'per_mapel'" class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                    <h4 class="text-base font-bold text-slate-900 dark:text-white">
                        Persentase Kehadiran per Mata Pelajaran
                    </h4>
                    <span class="text-xs text-slate-600 dark:text-slate-300">
                        Klik kartu untuk melihat rincian presensi pertemuan.
                    </span>
                </div>

                @if($persentasePerMapel->isEmpty())
                    <x-ui.empty-state
                        title="Belum ada data kehadiran pada periode ini"
                        description="Persentase kehadiran per mata pelajaran akan muncul setelah absensi tercatat."
                    />
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                        @foreach($persentasePerMapel as $rekap)
                            @php
                                $batasRendah = (int) config('absensi.batas_kehadiran_rendah', 75);
                                $isMemenuhi = ($rekap->persentase >= $batasRendah);
                            @endphp
                            <a href="{{ route('siswa.riwayat.mapel', ['mapel' => $rekap->id, 'periode' => $filterPeriodeValue]) }}"
                               class="group block bg-white dark:bg-slate-800 shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5 hover:shadow-md transition rounded-xl overflow-hidden border-2 border-slate-300 dark:border-slate-600 hover:border-emerald-500 dark:hover:border-emerald-400 p-4 sm:p-5">
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors leading-snug truncate">
                                            {{ $rekap->mapel }}
                                        </h4>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5 flex items-center gap-1 truncate" title="{{ $rekap->guru ?? '-' }}">
                                            <x-ui.icon name="user" size="sm" class="w-3.5 h-3.5 text-slate-600 dark:text-slate-400 shrink-0" />
                                            <span class="truncate font-semibold text-slate-800 dark:text-slate-200">{{ $rekap->guru ?? '-' }}</span>
                                        </p>
                                    </div>
                                    <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $isMemenuhi ? 'bg-emerald-100 text-emerald-900 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-400 dark:border-emerald-500/50' : 'bg-red-100 text-red-900 dark:bg-red-500/15 dark:text-red-300 border border-red-400 dark:border-red-500/50' }}">
                                        {{ $isMemenuhi ? 'Memenuhi' : 'Di Bawah' }}
                                    </span>
                                </div>

                                <div class="my-3 flex items-baseline justify-between">
                                    <div>
                                        <div class="text-3xl font-extrabold tracking-tight {{ \App\Support\Ui::persenWarna($rekap->persentase) }}">
                                            {{ $rekap->persentase }}%
                                        </div>
                                        <div class="text-[11px] text-slate-600 dark:text-slate-400 font-semibold mt-0.5">
                                            Persentase Kehadiran
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-bold px-2.5 py-1 bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-200 rounded-lg border border-slate-300 dark:border-slate-600">
                                            Total {{ $rekap->total_sesi }} Sesi
                                        </span>
                                    </div>
                                </div>

                                <div class="pt-3 border-t-2 border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs text-slate-600 dark:text-slate-300">
                                    <div class="flex items-center gap-1.5 font-semibold">
                                        <span title="Hadir">H: <strong class="text-emerald-900 dark:text-emerald-300 font-extrabold">{{ $rekap->total_hadir }}</strong></span>
                                        <span>&bull;</span>
                                        <span title="Izin">I: <strong class="text-blue-900 dark:text-blue-300 font-extrabold">{{ $rekap->total_izin }}</strong></span>
                                        <span>&bull;</span>
                                        <span title="Sakit">S: <strong class="text-amber-900 dark:text-amber-300 font-extrabold">{{ $rekap->total_sakit }}</strong></span>
                                        <span>&bull;</span>
                                        <span title="Alpa">A: <strong class="{{ $rekap->total_alpa > 0 ? 'text-red-900 dark:text-red-300 font-extrabold' : 'text-slate-700 dark:text-slate-300' }}">{{ $rekap->total_alpa }}</strong></span>
                                    </div>
                                    <span class="text-emerald-700 dark:text-emerald-400 group-hover:translate-x-0.5 transition-transform flex items-center gap-0.5 font-bold text-xs">
                                        Detail &rarr;
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- KONTEN TAB 2: SEMUA RIWAYAT --}}
            <div x-show="tab === 'semua'" class="space-y-4">
                {{-- Filter Panel Semua Riwayat (Lipat di Mobile, Inline di Desktop) --}}
                <x-ui.filter-panel :action="route('siswa.riwayat')" :active-filters="$activeFilters" :reset-url="route('siswa.riwayat', ['tab' => 'semua'])">
                    <input type="hidden" name="tab" value="semua">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                        {{-- Periode --}}
                        <div>
                            <x-input-label for="periode" value="Periode" class="text-slate-800 dark:text-slate-200 font-semibold mb-1" />
                            <x-ui.select id="periode" name="periode">
                                <option value="semua" {{ $filterPeriodeValue === 'semua' ? 'selected' : '' }}>Semua Periode</option>
                                @foreach($daftarPeriode as $item)
                                    <option value="{{ $item['value'] }}" {{ $filterPeriodeValue === $item['value'] ? 'selected' : '' }}>
                                        {{ $item['label'] }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        {{-- Bulan --}}
                        <div>
                            <x-input-label for="bulan" value="Bulan" class="text-slate-800 dark:text-slate-200 font-semibold mb-1" />
                            <x-ui.bulan-select
                                id="bulan"
                                name="bulan"
                                :tahun-ajaran="$selectedPeriode['tahun_ajaran'] ?? ($activePeriode['tahun_ajaran'] ?? null)"
                                :semester="$selectedPeriode['semester'] ?? ($activePeriode['semester'] ?? null)"
                                :selected="request('bulan')"
                            />
                        </div>

                        {{-- Tanggal --}}
                        <div>
                            <x-input-label for="tanggal" value="Tanggal" class="text-slate-800 dark:text-slate-200 font-semibold mb-1" />
                            <input id="tanggal"
                                   name="tanggal"
                                   type="date"
                                   class="block w-full text-base sm:text-xs min-h-[44px] py-2 px-3 font-medium rounded-xl border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 dark:[color-scheme:dark] focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 shadow-xs"
                                   value="{{ request('tanggal') }}" />
                        </div>

                        {{-- Mata Pelajaran --}}
                        <div>
                            <x-input-label for="mapel_id" value="Mata Pelajaran" class="text-slate-800 dark:text-slate-200 font-semibold mb-1" />
                            <x-ui.select id="mapel_id" name="mapel_id">
                                <option value="">Semua Mapel</option>
                                @foreach($mapels as $mapel)
                                    <option value="{{ $mapel->id }}" {{ (string) request('mapel_id') === (string) $mapel->id ? 'selected' : '' }}>
                                        {{ $mapel->nama }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        {{-- Status Kehadiran --}}
                        <div>
                            <x-input-label for="status" value="Status Kehadiran" class="text-slate-800 dark:text-slate-200 font-semibold mb-1" />
                            <x-ui.select id="status" name="status">
                                <option value="">Semua Status</option>
                                @foreach(['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'] as $sVal => $sLbl)
                                    <option value="{{ $sVal }}" {{ request('status') === $sVal ? 'selected' : '' }}>
                                        {{ $sLbl }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>
                    </div>
                </x-ui.filter-panel>

                {{-- Header Ringkas Jumlah Catatan --}}
                <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border-2 border-slate-300 dark:border-slate-600 p-4">
                    <div class="flex items-center justify-between text-sm text-slate-700 dark:text-slate-200 mb-3">
                        <span class="font-bold">
                            Menampilkan {{ $riwayat->total() }} catatan {{ request('status') ? strtolower(request('status')) : 'kehadiran' }}
                        </span>
                    </div>

                    @if($riwayat->isEmpty())
                        <x-ui.empty-state
                            title="Tidak ada riwayat kehadiran"
                            description="Belum ada catatan kehadiran yang sesuai dengan filter pencarian pada periode ini."
                        />
                    @else
                        <div class="overflow-x-auto rounded-xl border-2 border-slate-300 dark:border-slate-600">
                            <table class="w-full responsive-stack-table border-collapse">
                                <thead class="bg-slate-100 dark:bg-slate-800 border-b-2 border-slate-400 dark:border-slate-500 text-slate-900 dark:text-slate-100">
                                    <tr>
                                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider">Tanggal</th>
                                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider">Mata Pelajaran</th>
                                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider">Guru</th>
                                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y-2 divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                                    @foreach($riwayat as $item)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-slate-100 tabular-nums" data-label="Tanggal">
                                                {{ \Illuminate\Support\Carbon::parse($item->sesiAbsensi->tanggal)->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white" data-label="Mata Pelajaran">
                                                {{ $item->sesiAbsensi->jadwal->mapel->nama }}
                                            </td>
                                            <td class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-300" data-label="Guru">
                                                {{ $item->sesiAbsensi->jadwal->guru->user->name }}
                                            </td>
                                            <td class="px-6 py-4" data-label="Status">
                                                <x-ui.status-badge :status="$item->status" />
                                            </td>
                                            <td class="px-6 py-4 text-sm font-medium text-slate-600 dark:text-slate-300" data-label="Keterangan">
                                                {{ $item->keterangan ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $riwayat->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
