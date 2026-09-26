<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Guru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if (session('status'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Jadwal Mengajar Hari Ini ({{ $tanggal->isoFormat('dddd, D MMMM YYYY') }})</h3>
                    
                    @if($jadwalHariIni->isEmpty())
                        <div class="py-8 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-400 mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <p class="text-base font-medium text-gray-900 dark:text-gray-100">Tidak ada jadwal mengajar hari ini</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Anda tidak memiliki jadwal mata pelajaran yang aktif untuk hari ini.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($jadwalHariIni as $jadwal)
                                <div class="border dark:border-gray-700 rounded-lg p-4 shadow-sm flex flex-col">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h4 class="font-bold text-xl">{{ $jadwal->mapel->nama }}</h4>
                                            <p class="text-gray-600 dark:text-gray-400">Kelas: {{ $jadwal->kelas->nama }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="whitespace-nowrap text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 py-1 px-2 rounded">
                                                {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 flex-grow">
                                        @if($jadwal->sesi_hari_ini)
                                            <div class="bg-green-50 dark:bg-green-900/30 p-3 rounded-md mb-4 border border-green-200 dark:border-green-800">
                                                <p class="text-sm font-medium text-green-800 dark:text-green-400 mb-2">Sudah diabsen</p>
                                                
                                                @php
                                                    $hadir = 0; $izin = 0; $sakit = 0; $alpa = 0;
                                                    foreach($jadwal->sesi_hari_ini->detailAbsensi as $detail) {
                                                        if($detail->status->value === 'hadir') $hadir++;
                                                        elseif($detail->status->value === 'izin') $izin++;
                                                        elseif($detail->status->value === 'sakit') $sakit++;
                                                        elseif($detail->status->value === 'alpa') $alpa++;
                                                    }
                                                @endphp
                                                <div class="grid grid-cols-4 gap-1 text-center text-xs">
                                                    <div class="bg-white dark:bg-gray-800 p-1 rounded border dark:border-gray-700">
                                                        <span class="block text-gray-500">Hadir</span>
                                                        <span class="font-bold">{{ $hadir }}</span>
                                                    </div>
                                                    <div class="bg-white dark:bg-gray-800 p-1 rounded border dark:border-gray-700">
                                                        <span class="block text-gray-500">Izin</span>
                                                        <span class="font-bold">{{ $izin }}</span>
                                                    </div>
                                                    <div class="bg-white dark:bg-gray-800 p-1 rounded border dark:border-gray-700">
                                                        <span class="block text-gray-500">Sakit</span>
                                                        <span class="font-bold">{{ $sakit }}</span>
                                                    </div>
                                                    <div class="bg-white dark:bg-gray-800 p-1 rounded border dark:border-gray-700">
                                                        <span class="block text-gray-500">Alpa</span>
                                                        <span class="font-bold">{{ $alpa }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <a href="{{ route('guru.absensi.show', $jadwal->id) }}" class="inline-flex justify-center w-full items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                                                Edit Absensi
                                            </a>
                                        @else
                                            <div class="bg-yellow-50 dark:bg-yellow-900/30 p-3 rounded-md mb-4 border border-yellow-200 dark:border-yellow-800">
                                                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-400">Belum diabsen</p>
                                            </div>
                                            <a href="{{ route('guru.absensi.show', $jadwal->id) }}" class="inline-flex justify-center w-full items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                                Absen Sekarang
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
