<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Riwayat & Rekap Kehadiran') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ tab: '{{ $activeTab }}' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Banner Info Periode Dilihat & Filter Periode -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xs sm:rounded-xl p-6 border border-gray-100 dark:border-gray-700">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                            Riwayat Kehadiran Siswa
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            Periode Dilihat:
                            <span class="font-semibold text-blue-600 dark:text-blue-400">
                                @if($filterPeriodeValue === 'semua')
                                    Semua Periode
                                @else
                                    {{ $selectedPeriode['tahun_ajaran'] }} - Semester {{ $selectedPeriode['semester'] }}
                                @endif
                            </span>
                            @if($filterPeriodeValue !== 'semua' && isset($daftarPeriode) && collect($daftarPeriode)->firstWhere('is_aktif', true)['value'] === ($selectedPeriode['tahun_ajaran'].'|'.$selectedPeriode['semester']))
                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
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
                            <select id="periode_top" name="periode" onchange="this.form.submit()" class="w-full sm:w-60 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                @foreach($daftarPeriode as $item)
                                    <option value="{{ $item['value'] }}" {{ $filterPeriodeValue === $item['value'] ? 'selected' : '' }}>
                                        {{ $item['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <noscript>
                                <button type="submit" class="bg-blue-600 text-white text-xs px-3 py-2 rounded-md font-semibold">Ubah</button>
                            </noscript>
                        </form>
                    </div>
                </div>

                {{-- Tab Navigasi (Per Mata Pelajaran & Semua Riwayat) --}}
                <div class="mt-6 border-b border-gray-200 dark:border-gray-700 flex gap-6">
                    <button type="button"
                            @click="tab = 'per_mapel'"
                            :class="tab === 'per_mapel' ? 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="pb-3 px-1 border-b-2 font-medium text-sm transition-colors flex items-center gap-2 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M14 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M4 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M14 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/></svg>
                        <span>Per Mata Pelajaran</span>
                    </button>
                    <button type="button"
                            @click="tab = 'semua'"
                            :class="tab === 'semua' ? 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="pb-3 px-1 border-b-2 font-medium text-sm transition-colors flex items-center gap-2 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 4m0 1a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1z"/><path d="M3 12m0 1a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1z"/><path d="M3 20m0 1a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1z"/></svg>
                        <span>Semua Riwayat</span>
                    </button>
                </div>
            </div>

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md" role="alert">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- KONTEN TAB 1: PER MATA PELAJARAN --}}
            <div x-show="tab === 'per_mapel'" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-base font-bold text-gray-900 dark:text-gray-100">
                        Persentase Kehadiran per Mata Pelajaran
                    </h4>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Klik kartu untuk melihat rincian presensi pertemuan.
                    </span>
                </div>

                @if($persentasePerMapel->isEmpty())
                    <div class="bg-white dark:bg-gray-800 shadow-xs sm:rounded-xl p-10 text-center text-gray-500 dark:text-gray-400 border border-gray-100 dark:border-gray-700">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-400 mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100">Belum ada data kehadiran pada periode ini</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-md mx-auto">
                            Persentase kehadiran per mata pelajaran akan muncul setelah absensi tercatat.
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($persentasePerMapel as $rekap)
                            <a href="{{ route('siswa.riwayat.mapel', ['mapel' => $rekap->id, 'periode' => $filterPeriodeValue]) }}"
                               class="group block bg-white dark:bg-gray-800 shadow-xs hover:shadow-md transition sm:rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600 p-5">
                                <div class="flex items-start justify-between gap-2 mb-3">
                                    <div class="min-w-0">
                                        <h4 class="text-lg font-bold text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors truncate">
                                            {{ $rekap->mapel }}
                                        </h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-1 truncate" title="{{ $rekap->guru ?? '-' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg>
                                            <span class="truncate">{{ $rekap->guru ?? '-' }}</span>
                                        </p>
                                    </div>
                                    <span class="flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $rekap->persentase >= 80 ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                                        {{ $rekap->persentase >= 80 ? 'Memenuhi' : 'Di Bawah Target' }}
                                    </span>
                                </div>

                                <div class="my-3 flex items-baseline justify-between">
                                    <div>
                                        <div class="text-3xl font-extrabold tracking-tight {{ $rekap->persentase >= 80 ? 'text-green-600 dark:text-green-400' : ($rekap->persentase >= 60 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                                            {{ $rekap->persentase }}%
                                        </div>
                                        <div class="text-[11px] text-gray-400 dark:text-gray-500 font-medium mt-0.5">
                                            Persentase Kehadiran
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-semibold px-2 py-1 bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 rounded-md">
                                            Total {{ $rekap->total_sesi }} Sesi
                                        </span>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                    <div class="flex items-center gap-2">
                                        <span title="Hadir">H: <strong class="text-green-600 dark:text-green-400">{{ $rekap->total_hadir }}</strong></span>
                                        <span>&bull;</span>
                                        <span title="Izin">I: <strong class="text-blue-600 dark:text-blue-400">{{ $rekap->total_izin }}</strong></span>
                                        <span>&bull;</span>
                                        <span title="Sakit">S: <strong class="text-yellow-600 dark:text-yellow-400">{{ $rekap->total_sakit }}</strong></span>
                                        <span>&bull;</span>
                                        <span title="Alpa">A: <strong class="{{ $rekap->total_alpa > 0 ? 'text-red-600 dark:text-red-400 font-bold' : 'text-gray-700 dark:text-gray-300' }}">{{ $rekap->total_alpa }}</strong></span>
                                    </div>
                                    <span class="text-blue-600 dark:text-blue-400 group-hover:translate-x-0.5 transition-transform flex items-center gap-0.5 font-medium text-[11px]">
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
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xs sm:rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-bold mb-4">Riwayat Kehadiran</h3>
                        
                        <form method="GET" action="{{ route('siswa.riwayat') }}" class="mb-6 flex flex-col md:flex-row flex-wrap gap-4 items-end">
                            <input type="hidden" name="tab" value="semua">
                            <div class="w-full sm:w-auto">
                                <x-input-label for="periode" value="Periode" />
                                <select id="periode" name="periode" class="mt-1 block w-full sm:w-52 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                    <option value="semua" {{ $filterPeriodeValue === 'semua' ? 'selected' : '' }}>Semua Periode</option>
                                    @foreach($daftarPeriode as $item)
                                        <option value="{{ $item['value'] }}" {{ $filterPeriodeValue === $item['value'] ? 'selected' : '' }}>
                                            {{ $item['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full sm:w-auto">
                                <x-input-label for="bulan" value="Bulan" />
                                <x-text-input id="bulan" name="bulan" type="month" class="mt-1 block w-full sm:w-36 text-sm dark:[color-scheme:dark]" value="{{ request('bulan') }}" />
                            </div>
                            <div class="w-full sm:w-auto">
                                <x-input-label for="tanggal" value="Tanggal" />
                                <x-text-input id="tanggal" name="tanggal" type="date" class="mt-1 block w-full sm:w-40 text-sm dark:[color-scheme:dark]" value="{{ request('tanggal') }}" />
                            </div>
                            <div class="w-full sm:w-auto">
                                <x-input-label for="mapel_id" value="Mata Pelajaran" />
                                <select id="mapel_id" name="mapel_id" class="mt-1 block w-full sm:w-48 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                    <option value="">Semua Mapel</option>
                                    @foreach($mapels as $mapel)
                                        <option value="{{ $mapel->id }}" {{ request('mapel_id') == $mapel->id ? 'selected' : '' }}>
                                            {{ $mapel->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full sm:w-auto">
                                <x-input-label for="status" value="Status" />
                                <select id="status" name="status" class="mt-1 block w-full sm:w-36 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                    <option value="">Semua Status</option>
                                    @foreach(['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'] as $sVal => $sLbl)
                                        <option value="{{ $sVal }}" {{ request('status') === $sVal ? 'selected' : '' }}>
                                            {{ $sLbl }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md text-sm transition">
                                    Filter
                                </button>
                                @if(request('tanggal') || request('bulan') || request('mapel_id') || request('status') || request('periode') || request('tahun_ajaran') || request('semester'))
                                    <a href="{{ route('siswa.riwayat', ['tab' => 'semua']) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-3 rounded-md text-sm transition">
                                        Reset
                                    </a>
                                @endif
                            </div>
                        </form>

                        {{-- Header Ringkas Jumlah Catatan --}}
                        <div class="mb-4 flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                            <span class="font-medium">
                                Menampilkan {{ $riwayat->total() }} catatan {{ request('status') ? strtolower(request('status')) : 'kehadiran' }}
                            </span>
                        </div>

                        @if($riwayat->isEmpty())
                            <div class="py-8 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-400 mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                </div>
                                <p class="text-base font-medium text-gray-900 dark:text-gray-100">Tidak ada riwayat kehadiran</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Belum ada catatan kehadiran yang sesuai dengan filter pencarian pada periode ini.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Mata Pelajaran</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Guru</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($riwayat as $item)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    {{ \Illuminate\Support\Carbon::parse($item->sesiAbsensi->tanggal)->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $item->sesiAbsensi->jadwal->mapel->nama }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $item->sesiAbsensi->jadwal->guru->user->name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    @php
                                                        $badgeClass = match($item->status->value) {
                                                            'hadir' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                            'izin' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                                            'sakit' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                                            'alpa' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                                        };
                                                    @endphp
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeClass }}">
                                                        {{ $item->status->label() }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
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
    </div>
</x-app-layout>
