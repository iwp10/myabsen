<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Jadwal Mengajar') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Banner Header / Ringkasan Jadwal -->
            <div class="bg-blue-600 dark:bg-blue-700 rounded-2xl p-6 text-white shadow-md relative overflow-hidden" style="background-color: #2563eb;">
                <div class="absolute -right-8 -bottom-10 opacity-10 pointer-events-none">
                    <svg class="w-56 h-56 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20a2 2 0 002 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/>
                    </svg>
                </div>
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white mb-2">
                            Tahun Ajaran Aktif &bull; SMK Mandiri 02 Balaraja
                        </span>
                        <h1 class="text-2xl font-bold tracking-tight text-white">Jadwal Mengajar Guru</h1>
                        <p class="text-white text-sm mt-1">
                            Guru: <span class="font-semibold text-white">{{ Auth::user()->name }}</span> &bull; NIP: <span class="font-semibold text-white">{{ $guru?->nip ?? '-' }}</span>
                        </p>
                    </div>
                    <div class="sm:text-right flex-shrink-0">
                        <span class="text-xs text-white block uppercase font-medium tracking-wider">Total Jadwal</span>
                        <span class="text-2xl font-bold text-white">
                            {{ $jadwals->count() }} <span class="text-sm font-normal">Sesi / Minggu</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Tabel Jadwal Mengajar -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Seluruh Jadwal Mengajar Seminggu
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Daftar terurut berdasarkan hari pelaksanaan dan jam mulai pelajaran
                        </p>
                    </div>
                    <a href="{{ route('guru.dashboard') }}" class="inline-flex items-center text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 gap-1 self-start sm:self-auto">
                        &larr; Kembali ke Dashboard
                    </a>
                </div>

                <div class="p-0">
                    @if($jadwals->isEmpty())
                        <div class="py-12 text-center px-4">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-500 dark:text-blue-400 mb-3">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <p class="text-base font-semibold text-gray-900 dark:text-gray-100">Belum Ada Jadwal Mengajar</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-md mx-auto">
                                Anda belum memiliki jadwal mengajar yang terdaftar dalam sistem. Silakan hubungi bagian kurikulum atau operator sekolah jika terdapat kekeliruan.
                            </p>
                            <div class="mt-5">
                                <a href="{{ route('guru.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold uppercase tracking-widest rounded-lg shadow-xs transition gap-2">
                                    Kembali ke Dashboard
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                            Hari
                                        </th>
                                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                            Jam
                                        </th>
                                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                            Kelas
                                        </th>
                                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                            Mata Pelajaran
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($jadwals as $jadwal)
                                        @php
                                            $hariLower = strtolower($jadwal->hari);
                                            $badgeClasses = match($hariLower) {
                                                'senin' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                                'selasa' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 border-purple-200 dark:border-purple-800',
                                                'rabu' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                                                'kamis' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                                'jumat' => 'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300 border-teal-200 dark:border-teal-800',
                                                'sabtu' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                                                default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600',
                                            };
                                        @endphp
                                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition">
                                            <!-- Hari -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $badgeClasses }}">
                                                    {{ ucfirst($jadwal->hari) }}
                                                </span>
                                            </td>

                                            <!-- Jam -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span>{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}</span>
                                                </div>
                                            </td>

                                            <!-- Kelas -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                                    {{ $jadwal->kelas->nama }}
                                                </div>
                                                @if($jadwal->kelas->jurusan)
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $jadwal->kelas->jurusan->nama }}
                                                    </div>
                                                @endif
                                            </td>

                                            <!-- Mata Pelajaran -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                                    {{ $jadwal->mapel->nama }}
                                                </div>
                                                @if($jadwal->mapel->kode)
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">
                                                        Kode: {{ $jadwal->mapel->kode }}
                                                    </div>
                                                @endif
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
