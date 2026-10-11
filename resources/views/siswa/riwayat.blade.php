<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 dark:text-white leading-tight">
            {{ __('Riwayat & Rekap Kehadiran') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8" x-data="{ tab: '{{ $activeTab }}' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Banner Info Periode Dilihat & Filter Periode -->
            <div class="bg-white dark:bg-slate-900 overflow-hidden shadow-xs rounded-xl p-5 sm:p-6 border-2 border-slate-300 dark:border-slate-600">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                            Riwayat Kehadiran Siswa
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-0.5">
                            Periode Dilihat:
                            <span class="font-bold text-slate-900 dark:text-slate-100">
                                @if($filterPeriodeValue === 'semua')
                                    Semua Periode
                                @else
                                    {{ $selectedPeriode['tahun_ajaran'] }} - Semester {{ $selectedPeriode['semester'] }}
                                @endif
                            </span>
                            @if($filterPeriodeValue !== 'semua' && isset($daftarPeriode) && collect($daftarPeriode)->firstWhere('is_aktif', true)['value'] === ($selectedPeriode['tahun_ajaran'].'|'.$selectedPeriode['semester']))
                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-400 dark:border-emerald-500/50">
                                    (aktif)
                                </span>
                            @endif
                        </p>
                    </div>

                    <!-- Dropdown Periode untuk Tab Per Mapel -->
                    <div x-show="tab === 'per_mapel'">
                        <form method="GET" action="{{ route('siswa.riwayat') }}" class="flex items-center gap-2 w-full md:w-auto">
                            <input type="hidden" name="tab" value="per_mapel">
                            <label for="periode_top" class="sr-only">Pilih Periode</label>
                            <select id="periode_top" name="periode" onchange="this.form.submit()" class="w-full sm:w-60 border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 dark:[color-scheme:dark] focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 rounded-lg text-base sm:text-xs min-h-[44px] py-2 px-3 font-medium">
                                @foreach($daftarPeriode as $item)
                                    <option value="{{ $item['value'] }}" {{ $filterPeriodeValue === $item['value'] ? 'selected' : '' }}>
                                        {{ $item['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <noscript>
                                <button type="submit" class="bg-emerald-700 dark:bg-emerald-400 text-white dark:text-slate-950 text-xs px-3 py-2 rounded-lg font-bold min-h-[44px]">Ubah</button>
                            </noscript>
                        </form>
                    </div>
                </div>

                {{-- Tab Navigasi (Per Mata Pelajaran & Semua Riwayat) --}}
                <div class="mt-6 border-b-2 border-slate-200 dark:border-slate-700 flex gap-6">
                    <button type="button"
                            @click="tab = 'per_mapel'"
                            :class="tab === 'per_mapel' ? 'border-emerald-700 text-emerald-800 dark:text-emerald-300 dark:border-emerald-400 font-extrabold border-b-4' : 'border-transparent text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white font-semibold'"
                            class="pb-3 px-2 border-b-2 text-sm transition-all flex items-center gap-2 focus-visible:ring-4 focus-visible:ring-emerald-500/30 min-h-[44px]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M14 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M4 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M14 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/></svg>
                        <span>Per Mata Pelajaran</span>
                    </button>
                    <button type="button"
                            @click="tab = 'semua'"
                            :class="tab === 'semua' ? 'border-emerald-700 text-emerald-800 dark:text-emerald-300 dark:border-emerald-400 font-extrabold border-b-4' : 'border-transparent text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white font-semibold'"
                            class="pb-3 px-2 border-b-2 text-sm transition-all flex items-center gap-2 focus-visible:ring-4 focus-visible:ring-emerald-500/30 min-h-[44px]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 4m0 1a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1z"/><path d="M3 12m0 1a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1z"/><path d="M3 20m0 1a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1z"/></svg>
                        <span>Semua Riwayat</span>
                    </button>
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
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($persentasePerMapel as $rekap)
                            @php
                                $batasRendah = (int) config('absensi.batas_kehadiran_rendah', 75);
                                $isMemenuhi = ($rekap->persentase >= $batasRendah);
                            @endphp
                            <a href="{{ route('siswa.riwayat.mapel', ['mapel' => $rekap->id, 'periode' => $filterPeriodeValue]) }}"
                               class="group block bg-white dark:bg-slate-800 shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5 hover:shadow-md transition rounded-xl overflow-hidden border-2 border-slate-300 dark:border-slate-600 hover:border-emerald-500 dark:hover:border-emerald-400 p-5">
                                <div class="flex items-start justify-between gap-2 mb-3">
                                    <div class="min-w-0">
                                        <h4 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors truncate">
                                            {{ $rekap->mapel }}
                                        </h4>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5 flex items-center gap-1 truncate" title="{{ $rekap->guru ?? '-' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-600 dark:text-slate-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg>
                                            <span class="truncate font-semibold text-slate-900 dark:text-slate-100">{{ $rekap->guru ?? '-' }}</span>
                                        </p>
                                    </div>
                                    <span class="flex-shrink-0 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $isMemenuhi ? 'bg-emerald-100 text-emerald-900 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-400 dark:border-emerald-500/50' : 'bg-red-100 text-red-900 dark:bg-red-500/15 dark:text-red-300 border border-red-400 dark:border-red-500/50' }}">
                                        {{ $isMemenuhi ? 'Memenuhi' : 'Di Bawah Target' }}
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
                                        <span class="text-xs font-bold px-2.5 py-1 bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-200 rounded-lg border-2 border-slate-300 dark:border-slate-600">
                                            Total {{ $rekap->total_sesi }} Sesi
                                        </span>
                                    </div>
                                </div>

                                <div class="pt-3 border-t-2 border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs text-slate-600 dark:text-slate-300">
                                    <div class="flex items-center gap-2">
                                        <span title="Hadir">H: <strong class="text-emerald-900 dark:text-emerald-300 font-extrabold">{{ $rekap->total_hadir }}</strong></span>
                                        <span>&bull;</span>
                                        <span title="Izin">I: <strong class="text-blue-900 dark:text-blue-300 font-extrabold">{{ $rekap->total_izin }}</strong></span>
                                        <span>&bull;</span>
                                        <span title="Sakit">S: <strong class="text-amber-900 dark:text-amber-300 font-extrabold">{{ $rekap->total_sakit }}</strong></span>
                                        <span>&bull;</span>
                                        <span title="Alpa">A: <strong class="{{ $rekap->total_alpa > 0 ? 'text-red-900 dark:text-red-300 font-extrabold' : 'text-slate-700 dark:text-slate-300 font-bold' }}">{{ $rekap->total_alpa }}</strong></span>
                                    </div>
                                    <span class="text-emerald-700 dark:text-emerald-400 group-hover:translate-x-0.5 transition-transform flex items-center gap-0.5 font-bold text-xs">
                                        Lihat detail
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6l-6 6"/></svg>
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- KONTEN TAB 2: SEMUA RIWAYAT --}}
            <div x-show="tab === 'semua'" class="space-y-4">
                <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border-2 border-slate-300 dark:border-slate-600 overflow-hidden">
                    <div class="p-5 sm:p-6 text-slate-900 dark:text-white">
                        <h3 class="text-lg font-bold mb-4">Riwayat Kehadiran</h3>
                        
                        <form method="GET" action="{{ route('siswa.riwayat') }}" class="mb-6 flex flex-col md:flex-row flex-wrap gap-4 items-end">
                            <input type="hidden" name="tab" value="semua">
                            <div class="w-full sm:w-auto">
                                <x-input-label for="periode" value="Periode" class="text-slate-800 dark:text-slate-200 font-semibold" />
                                <select id="periode" name="periode" class="mt-1 block w-full sm:w-52 border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 dark:[color-scheme:dark] focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 rounded-lg text-base sm:text-xs min-h-[44px] py-2 px-3 font-medium">
                                    <option value="semua" {{ $filterPeriodeValue === 'semua' ? 'selected' : '' }}>Semua Periode</option>
                                    @foreach($daftarPeriode as $item)
                                        <option value="{{ $item['value'] }}" {{ $filterPeriodeValue === $item['value'] ? 'selected' : '' }}>
                                            {{ $item['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full sm:w-auto">
                                <x-input-label for="bulan" value="Bulan" class="text-slate-800 dark:text-slate-200 font-semibold" />
                                <input id="bulan" name="bulan" type="month" class="mt-1 block w-full sm:w-36 text-base sm:text-xs min-h-[44px] py-2 px-3 font-medium rounded-lg border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 dark:[color-scheme:dark] focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20" value="{{ request('bulan') }}" />
                            </div>
                            <div class="w-full sm:w-auto">
                                <x-input-label for="tanggal" value="Tanggal" class="text-slate-800 dark:text-slate-200 font-semibold" />
                                <input id="tanggal" name="tanggal" type="date" class="mt-1 block w-full sm:w-40 text-base sm:text-xs min-h-[44px] py-2 px-3 font-medium rounded-lg border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 dark:[color-scheme:dark] focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20" value="{{ request('tanggal') }}" />
                            </div>
                            <div class="w-full sm:w-auto">
                                <x-input-label for="mapel_id" value="Mata Pelajaran" class="text-slate-800 dark:text-slate-200 font-semibold" />
                                <select id="mapel_id" name="mapel_id" class="mt-1 block w-full sm:w-48 border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 dark:[color-scheme:dark] focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 rounded-lg text-base sm:text-xs min-h-[44px] py-2 px-3 font-medium">
                                    <option value="">Semua Mapel</option>
                                    @foreach($mapels as $mapel)
                                        <option value="{{ $mapel->id }}" {{ request('mapel_id') == $mapel->id ? 'selected' : '' }}>
                                            {{ $mapel->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full sm:w-auto">
                                <x-input-label for="status" value="Status" class="text-slate-800 dark:text-slate-200 font-semibold" />
                                <select id="status" name="status" class="mt-1 block w-full sm:w-36 border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 dark:[color-scheme:dark] focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 rounded-lg text-base sm:text-xs min-h-[44px] py-2 px-3 font-medium">
                                    <option value="">Semua Status</option>
                                    @foreach(['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'] as $sVal => $sLbl)
                                        <option value="{{ $sVal }}" {{ request('status') === $sVal ? 'selected' : '' }}>
                                            {{ $sLbl }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950 font-bold py-2.5 px-5 rounded-lg text-xs uppercase tracking-wider min-h-[44px] transition shadow-xs">
                                    Filter
                                </button>
                                @if(request('tanggal') || request('bulan') || request('mapel_id') || request('status') || request('periode') || request('tahun_ajaran') || request('semester'))
                                    <a href="{{ route('siswa.riwayat', ['tab' => 'semua']) }}" class="border-2 border-slate-400 dark:border-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold py-2.5 px-4 rounded-lg text-xs uppercase tracking-wider min-h-[44px] flex items-center justify-center transition shadow-xs">
                                        Reset
                                    </a>
                                @endif
                            </div>
                        </form>

                        {{-- Header Ringkas Jumlah Catatan --}}
                        <div class="mb-4 flex items-center justify-between text-sm text-slate-700 dark:text-slate-200">
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
                                                <td class="px-6 py-4 font-bold text-slate-900 dark:text-slate-100" data-label="Mata Pelajaran">
                                                    {{ $item->sesiAbsensi->jadwal->mapel->nama }}
                                                </td>
                                                <td class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200" data-label="Guru">
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
                            <div class="mt-6">
                                {{ $riwayat->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
