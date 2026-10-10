<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Riwayat & Rekap Kehadiran') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Banner Info Periode Dilihat -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                            Riwayat & Rekap Kehadiran
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            Periode Dilihat:
                            <span class="font-semibold text-blue-600 dark:text-blue-400">
                                {{ $selectedPeriode['tahun_ajaran'] }} - Semester {{ $selectedPeriode['semester'] }}
                            </span>
                            @if(isset($daftarPeriode) && collect($daftarPeriode)->firstWhere('is_aktif', true)['value'] === ($selectedPeriode['tahun_ajaran'].'|'.$selectedPeriode['semester']))
                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                    (aktif)
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Rekap Persentase -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Persentase Kehadiran per Mata Pelajaran</h3>
                    
                    @if($persentasePerMapel->isEmpty())
                        <div class="py-6 text-center">
                            <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-400 mb-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Belum ada data kehadiran pada periode ini</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Persentase kehadiran per mata pelajaran akan muncul setelah absensi tercatat.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($persentasePerMapel as $rekap)
                                <div class="border dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-800">
                                    <div class="flex items-center justify-between">
                                        <div class="font-medium text-lg text-gray-900 dark:text-gray-100">{{ $rekap->mapel }}</div>
                                        <span class="text-xs px-2 py-0.5 rounded-full {{ $rekap->persentase >= 80 ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                                            {{ $rekap->persentase >= 80 ? 'Memenuhi' : 'Di Bawah Target' }}
                                        </span>
                                    </div>
                                    <div class="mt-2 text-3xl font-bold {{ $rekap->persentase >= 80 ? 'text-green-600 dark:text-green-400' : ($rekap->persentase >= 60 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                                        {{ $rekap->persentase }}%
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-2 pt-2 border-t border-gray-100 dark:border-gray-700 flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <span>H: <strong class="text-gray-700 dark:text-gray-300">{{ $rekap->total_hadir }}</strong></span>
                                        <span>&bull;</span>
                                        <span>I: <strong class="text-gray-700 dark:text-gray-300">{{ $rekap->total_izin }}</strong></span>
                                        <span>&bull;</span>
                                        <span>S: <strong class="text-gray-700 dark:text-gray-300">{{ $rekap->total_sakit }}</strong></span>
                                        <span>&bull;</span>
                                        <span>A: <strong class="{{ $rekap->total_alpa > 0 ? 'text-red-600 dark:text-red-400 font-bold' : 'text-gray-700 dark:text-gray-300' }}">{{ $rekap->total_alpa }}</strong></span>
                                        <span class="ml-auto text-[11px] text-gray-400 dark:text-gray-500">Total {{ $rekap->total_sesi }} Sesi</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Filter dan Tabel Riwayat -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Riwayat Kehadiran</h3>

                    @if($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <ul class="list-disc list-inside text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form method="GET" action="{{ route('siswa.riwayat') }}" class="mb-6 flex flex-col md:flex-row flex-wrap gap-4 items-end">
                        <div class="w-full sm:w-auto">
                            <x-input-label for="periode" value="Periode" />
                            <select id="periode" name="periode" class="mt-1 block w-full sm:w-56 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                @foreach($daftarPeriode as $item)
                                    <option value="{{ $item['value'] }}" {{ $filterPeriodeValue === $item['value'] ? 'selected' : '' }}>
                                        {{ $item['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full sm:w-auto">
                            <x-input-label for="tanggal" value="Tanggal" />
                            <x-text-input id="tanggal" name="tanggal" type="date" class="mt-1 block w-full sm:w-44 text-sm" value="{{ request('tanggal') }}" />
                        </div>
                        <div class="w-full sm:w-auto">
                            <x-input-label for="mapel_id" value="Mata Pelajaran" />
                            <select id="mapel_id" name="mapel_id" class="mt-1 block w-full sm:w-52 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
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
                            <select id="status" name="status" class="mt-1 block w-full sm:w-40 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
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
                            @if(request('tanggal') || request('mapel_id') || request('periode') || request('tahun_ajaran') || request('semester') || request('status'))
                                <a href="{{ route('siswa.riwayat') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-3 rounded-md text-sm transition">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>

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
</x-app-layout>
