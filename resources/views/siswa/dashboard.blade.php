<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Siswa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Jadwal & Status Hari Ini ({{ \Illuminate\Support\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }})</h3>
                    
                    @if($statusHariIni->isEmpty())
                        <p class="text-gray-500">Tidak ada jadwal mapel hari ini.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Jam</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Mata Pelajaran</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Guru</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status Kehadiran</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($statusHariIni as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ substr($item['jadwal']->jam_mulai, 0, 5) }} - {{ substr($item['jadwal']->jam_selesai, 0, 5) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $item['jadwal']->mapel->nama }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $item['jadwal']->guru->user->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @php
                                                    $statusValue = $item['status_value'] ?? '';
                                                    $badgeClass = match($statusValue) {
                                                        'hadir' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                        'izin' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                                        'sakit' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                                        'alpa' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                                    };
                                                @endphp
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeClass }}">
                                                    {{ $item['status_label'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <a href="{{ route('siswa.riwayat') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Lihat Riwayat & Persentase
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
