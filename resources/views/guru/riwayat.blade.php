<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Riwayat Absensi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold">Riwayat Sesi Absensi</h3>
                        
                        <form action="{{ route('guru.laporan.export') }}" method="GET" class="flex items-center space-x-2">
                            <input type="month" name="bulan" value="{{ date('Y-m') }}" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm text-sm" />
                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Excel
                            </button>
                            <button type="submit" formaction="{{ route('guru.laporan.exportPdf') }}" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                PDF
                            </button>
                        </form>
                    </div>

                    @if($riwayatSesi->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400">Belum ada riwayat absensi yang tersimpan.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full whitespace-no-wrap">
                                <thead>
                                    <tr class="text-left font-bold border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                        <th class="px-4 py-3">Tanggal</th>
                                        <th class="px-4 py-3">Kelas</th>
                                        <th class="px-4 py-3">Mata Pelajaran</th>
                                        <th class="px-4 py-3">Jam</th>
                                        <th class="px-4 py-3 text-center">Rekap (H/I/S/A)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y dark:divide-gray-700">
                                    @foreach($riwayatSesi as $sesi)
                                        @php
                                            $hadir = 0; $izin = 0; $sakit = 0; $alpa = 0;
                                            foreach($sesi->detailAbsensi as $detail) {
                                                if($detail->status->value === 'hadir') $hadir++;
                                                elseif($detail->status->value === 'izin') $izin++;
                                                elseif($detail->status->value === 'sakit') $sakit++;
                                                elseif($detail->status->value === 'alpa') $alpa++;
                                            }
                                        @endphp
                                        <tr class="text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-4 py-3">{{ \Carbon\Carbon::parse($sesi->tanggal)->isoFormat('D MMM YYYY') }}</td>
                                            <td class="px-4 py-3">{{ $sesi->jadwal->kelas->nama }}</td>
                                            <td class="px-4 py-3">{{ $sesi->jadwal->mapel->nama }}</td>
                                            <td class="px-4 py-3 text-sm">{{ substr($sesi->jadwal->jam_mulai, 0, 5) }} - {{ substr($sesi->jadwal->jam_selesai, 0, 5) }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <div class="inline-flex space-x-2 text-sm">
                                                    <span class="text-green-600 font-bold" title="Hadir">{{ $hadir }}</span>
                                                    <span class="text-gray-400">/</span>
                                                    <span class="text-blue-600 font-bold" title="Izin">{{ $izin }}</span>
                                                    <span class="text-gray-400">/</span>
                                                    <span class="text-yellow-600 font-bold" title="Sakit">{{ $sakit }}</span>
                                                    <span class="text-gray-400">/</span>
                                                    <span class="text-red-600 font-bold" title="Alpa">{{ $alpa }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
