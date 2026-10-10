<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('siswa.riwayat', ['periode' => $filterPeriodeValue, 'tab' => 'per_mapel']) }}"
               class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
               title="Kembali ke Riwayat">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 6l-6 6l6 6" />
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $mapel->nama }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Tombol Navigasi Kembali & Pilihan Periode -->
            <div class="bg-white dark:bg-gray-800 shadow-xs sm:rounded-xl p-5 sm:p-6 border border-gray-100 dark:border-gray-700">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <a href="{{ route('siswa.riwayat', ['periode' => $filterPeriodeValue, 'tab' => 'per_mapel']) }}"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M15 6l-6 6l6 6" />
                                </svg>
                                Kembali ke Riwayat
                            </a>
                        </div>
                        <h3 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">
                            {{ $mapel->nama }}
                        </h3>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500 dark:text-gray-400 mt-1">
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg>
                                Guru: <strong class="text-gray-700 dark:text-gray-300">{{ $guru }}</strong>
                            </span>
                            <span>&bull;</span>
                            <span>
                                Periode: <strong class="text-blue-600 dark:text-blue-400">{{ $selectedPeriode['tahun_ajaran'] }} - Semester {{ $selectedPeriode['semester'] }}</strong>
                                @if($selectedPeriode['tahun_ajaran'] === ($activePeriode['tahun_ajaran'] ?? '') && $selectedPeriode['semester'] === ($activePeriode['semester'] ?? ''))
                                    <span class="ml-1 inline-flex items-center px-1.5 py-0.2 rounded text-[11px] font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                        (aktif)
                                    </span>
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- Dropdown Periode untuk Mapel Ini -->
                    @if(count($daftarPeriodeMapel) > 1)
                        <form method="GET" action="{{ route('siswa.riwayat.mapel', $mapel) }}" class="flex items-center gap-2 w-full sm:w-auto">
                            <label for="periode_select" class="text-xs font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">Ganti Periode:</label>
                            <select id="periode_select" name="periode" onchange="this.form.submit()" class="w-full sm:w-60 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                @foreach($daftarPeriodeMapel as $p)
                                    <option value="{{ $p['value'] }}" {{ $filterPeriodeValue === $p['value'] ? 'selected' : '' }}>
                                        {{ $p['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <noscript>
                                <button type="submit" class="bg-blue-600 text-white text-xs px-3 py-2 rounded-md font-semibold">Ubah</button>
                            </noscript>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Kartu Ringkasan Kehadiran Siswa -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-xs">
                    <span class="text-xs font-medium text-gray-400 dark:text-gray-500 block">Persentase</span>
                    <span class="text-2xl font-extrabold mt-1 block {{ $ringkasan['persentase'] >= 80 ? 'text-green-600 dark:text-green-400' : ($ringkasan['persentase'] >= 60 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                        {{ $ringkasan['persentase'] }}%
                    </span>
                    <span class="text-[11px] font-semibold {{ $ringkasan['persentase'] >= 80 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $ringkasan['persentase'] >= 80 ? 'Memenuhi' : 'Di Bawah Target' }}
                    </span>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-xs">
                    <span class="text-xs font-medium text-gray-400 dark:text-gray-500 block">Total Pertemuan</span>
                    <span class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1 block">
                        {{ $ringkasan['total_sesi'] }}
                    </span>
                    <span class="text-[11px] text-gray-400 dark:text-gray-500">Sesi tercatat</span>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-green-100 dark:border-green-900/40 shadow-xs">
                    <span class="text-xs font-medium text-green-600 dark:text-green-400 block">Hadir (H)</span>
                    <span class="text-2xl font-bold text-green-700 dark:text-green-300 mt-1 block">
                        {{ $ringkasan['total_hadir'] }}
                    </span>
                    <span class="text-[11px] text-gray-400 dark:text-gray-500">Sesi</span>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-blue-100 dark:border-blue-900/40 shadow-xs">
                    <span class="text-xs font-medium text-blue-600 dark:text-blue-400 block">Izin (I)</span>
                    <span class="text-2xl font-bold text-blue-700 dark:text-blue-300 mt-1 block">
                        {{ $ringkasan['total_izin'] }}
                    </span>
                    <span class="text-[11px] text-gray-400 dark:text-gray-500">Sesi</span>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-yellow-100 dark:border-yellow-900/40 shadow-xs">
                    <span class="text-xs font-medium text-yellow-600 dark:text-yellow-400 block">Sakit (S)</span>
                    <span class="text-2xl font-bold text-yellow-700 dark:text-yellow-300 mt-1 block">
                        {{ $ringkasan['total_sakit'] }}
                    </span>
                    <span class="text-[11px] text-gray-400 dark:text-gray-500">Sesi</span>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-red-100 dark:border-red-900/40 shadow-xs">
                    <span class="text-xs font-medium text-red-600 dark:text-red-400 block">Alpa (A)</span>
                    <span class="text-2xl font-bold {{ $ringkasan['total_alpa'] > 0 ? 'text-red-700 dark:text-red-300' : 'text-gray-600 dark:text-gray-300' }} mt-1 block">
                        {{ $ringkasan['total_alpa'] }}
                    </span>
                    <span class="text-[11px] text-gray-400 dark:text-gray-500">Sesi</span>
                </div>
            </div>

            <!-- Matriks Absensi Pertemuan Siswa (Horizontal Seperti Riwayat Guru) -->
            <div class="bg-white dark:bg-gray-800 shadow-xs sm:rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                        Matriks Presensi Pertemuan
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Rincian status kehadiran per pertemuan (P1 hingga P{{ $pertemuanList->count() }}).
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="border-collapse text-sm" style="min-width: max-content">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/60">
                                <th class="sticky left-0 z-10 bg-gray-50 dark:bg-gray-700/60 px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-600 w-8 text-center">No</th>
                                <th class="sticky left-8 z-10 bg-gray-50 dark:bg-gray-700/60 px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-600 w-32">NIS</th>
                                <th class="sticky left-[10rem] z-10 bg-gray-50 dark:bg-gray-700/60 px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-600 w-52">Nama Siswa</th>
                                @foreach($pertemuanList as $idx => $detail)
                                    <th class="px-2 py-2 border-b border-r border-gray-200 dark:border-gray-600 text-center font-semibold text-gray-600 dark:text-gray-300 w-16">
                                        <div class="text-xs font-bold text-gray-500 dark:text-gray-400">P{{ $idx + 1 }}</div>
                                        <div class="text-xs text-gray-400 dark:text-gray-500 font-normal">
                                            {{ \Carbon\Carbon::parse($detail->sesiAbsensi->tanggal)->locale('id')->isoFormat('D MMM') }}
                                        </div>
                                        <div class="text-xs text-gray-400 dark:text-gray-500 font-normal">
                                            {{ substr(ucfirst(\Carbon\Carbon::parse($detail->sesiAbsensi->tanggal)->locale('id')->isoFormat('dddd')), 0, 3) }}
                                        </div>
                                    </th>
                                @endforeach
                                {{-- Kolom Ringkasan --}}
                                <th class="px-2 py-2 border-b border-r border-gray-200 dark:border-gray-600 text-center font-semibold text-green-600 dark:text-green-400 w-10">H</th>
                                <th class="px-2 py-2 border-b border-r border-gray-200 dark:border-gray-600 text-center font-semibold text-blue-600 dark:text-blue-400 w-10">I</th>
                                <th class="px-2 py-2 border-b border-r border-gray-200 dark:border-gray-600 text-center font-semibold text-yellow-600 dark:text-yellow-400 w-10">S</th>
                                <th class="px-2 py-2 border-b border-r border-gray-200 dark:border-gray-600 text-center font-semibold text-red-600 dark:text-red-400 w-10">A</th>
                                <th class="px-2 py-2 border-b border-r border-gray-200 dark:border-gray-600 text-center font-semibold text-gray-600 dark:text-gray-300 w-16">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                <td class="sticky left-0 z-10 bg-white dark:bg-gray-800 px-3 py-2 text-center text-gray-500 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700 w-8">1</td>
                                <td class="sticky left-8 z-10 bg-white dark:bg-gray-800 px-3 py-2 text-gray-600 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700 font-mono text-xs w-32">{{ $siswa->nis }}</td>
                                <td class="sticky left-[10rem] z-10 bg-white dark:bg-gray-800 px-3 py-2 font-medium text-gray-800 dark:text-gray-200 border-r border-gray-200 dark:border-gray-700 w-52">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span>{{ $siswa->user->name ?? $siswa->nama }}</span>
                                    </div>
                                </td>
                                @foreach($pertemuanList as $idx => $detail)
                                    @php
                                        $statusStr = $detail->status instanceof \App\Enums\StatusKehadiran ? $detail->status->value : (string) $detail->status;
                                        [$kode, $bgKelas] = match($statusStr) {
                                            'hadir' => ['H', 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300'],
                                            'izin'  => ['I', 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300'],
                                            'sakit' => ['S', 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-300'],
                                            'alpa'  => ['A', 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300'],
                                            default => ['·', 'text-gray-300 dark:text-gray-600'],
                                        };
                                        $tooltipKeterangan = $detail->keterangan ? ' - Ket: '.$detail->keterangan : '';
                                        $titleText = 'P'.($idx+1).': '.$kode.' ('.ucfirst($statusStr).')'.$tooltipKeterangan;
                                    @endphp
                                    <td class="px-1 py-2 border-r border-gray-100 dark:border-gray-700 w-16 text-center" title="{{ $titleText }}">
                                        <span class="inline-block w-7 h-7 leading-7 rounded font-bold text-xs text-center {{ $bgKelas }}">
                                            {{ $kode }}
                                        </span>
                                    </td>
                                @endforeach
                                {{-- Kolom Ringkasan --}}
                                <td class="px-2 py-2 text-center font-bold text-green-600 dark:text-green-400 border-r border-gray-100 dark:border-gray-700 w-10">{{ $ringkasan['total_hadir'] }}</td>
                                <td class="px-2 py-2 text-center font-bold text-blue-600 dark:text-blue-400 border-r border-gray-100 dark:border-gray-700 w-10">{{ $ringkasan['total_izin'] }}</td>
                                <td class="px-2 py-2 text-center font-bold text-yellow-600 dark:text-yellow-400 border-r border-gray-100 dark:border-gray-700 w-10">{{ $ringkasan['total_sakit'] }}</td>
                                <td class="px-2 py-2 text-center font-bold text-red-600 dark:text-red-400 border-r border-gray-100 dark:border-gray-700 w-10">{{ $ringkasan['total_alpa'] }}</td>
                                <td class="px-2 py-2 text-center text-xs font-semibold w-16 {{ $ringkasan['persentase'] >= 80 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400 font-bold' }}">
                                    {{ $ringkasan['persentase'] }}%
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Keterangan Legenda Simbol --}}
                <div class="px-6 py-3 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-400 dark:text-gray-500 flex flex-wrap gap-4 items-center">
                    <span class="inline-flex items-center gap-1"><span class="w-4 h-4 rounded bg-green-100 dark:bg-green-900/50 inline-block"></span>H = Hadir</span>
                    <span class="inline-flex items-center gap-1"><span class="w-4 h-4 rounded bg-blue-100 dark:bg-blue-900/50 inline-block"></span>I = Izin</span>
                    <span class="inline-flex items-center gap-1"><span class="w-4 h-4 rounded bg-yellow-100 dark:bg-yellow-900/50 inline-block"></span>S = Sakit</span>
                    <span class="inline-flex items-center gap-1"><span class="w-4 h-4 rounded bg-red-100 dark:bg-red-900/50 inline-block"></span>A = Alpa</span>
                </div>
            </div>

            {{-- Catatan Keterangan Sesi (Jika ada keterangan izin/sakit/alpa) --}}
            @php
                $pertemuanDenganKeterangan = $pertemuanList->filter(function($d) {
                    return !empty(trim((string)$d->keterangan));
                });
            @endphp
            @if($pertemuanDenganKeterangan->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 shadow-xs sm:rounded-xl p-6 border border-gray-100 dark:border-gray-700">
                    <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9h.01"/><path d="M11 12h1v4h1"/><path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z"/></svg>
                        Catatan & Keterangan Pertemuan
                    </h4>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700 text-xs">
                        @foreach($pertemuanDenganKeterangan as $det)
                            @php
                                $idxPertemuan = $pertemuanList->search(function($item) use ($det) { return $item->id === $det->id; });
                                $stValue = $det->status instanceof \App\Enums\StatusKehadiran ? $det->status->value : (string) $det->status;
                            @endphp
                            <div class="py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-gray-700 dark:text-gray-300">
                                        P{{ $idxPertemuan !== false ? $idxPertemuan + 1 : '-' }} ({{ \Carbon\Carbon::parse($det->sesiAbsensi->tanggal)->locale('id')->isoFormat('D MMMM Y') }})
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold {{ match($stValue) { 'hadir' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300', 'izin' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300', 'sakit' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300', default => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' } }}">
                                        {{ ucfirst($stValue) }}
                                    </span>
                                </div>
                                <div class="text-gray-600 dark:text-gray-400 italic">
                                    "{{ $det->keterangan }}"
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
