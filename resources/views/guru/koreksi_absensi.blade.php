<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Jadwal & Koreksi Absensi') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Banner Header -->
            <div class="bg-blue-600 dark:bg-blue-700 rounded-2xl p-6 text-white shadow-md relative overflow-hidden border-2 border-blue-700 dark:border-blue-600" style="background-color: #2563eb;">
                <div class="absolute -right-8 -bottom-10 opacity-10 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-56 h-56 text-white">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M11.5 21h-5.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v6" />
                        <path d="M16 3v4" />
                        <path d="M8 3v4" />
                        <path d="M4 11h16" />
                        <path d="M15 19l2 2l4 -4" />
                    </svg>
                </div>
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white mb-2">
                            Pusat Koreksi & Susulan &bull; Portal Guru
                        </span>
                        <h1 class="text-2xl font-bold tracking-tight text-white">Jadwal & Koreksi Absensi</h1>
                        <p class="text-white/90 text-sm mt-1">
                            Daftar jadwal mengajar 7 hari terakhir (hari ini sampai H-6) untuk melakukan pengisian susulan atau koreksi data absensi.
                        </p>
                    </div>
                    <div class="sm:text-right flex-shrink-0">
                        <span class="text-xs text-white/80 block uppercase font-medium tracking-wider">Guru Pengajar</span>
                        <span class="text-sm font-semibold text-white block">
                            {{ Auth::user()->name }}
                        </span>
                        <span class="text-xs text-white/80 block">
                            NIP: {{ $guru?->nip ?? '-' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Daftar 7 Hari Terakhir -->
            <div class="space-y-6">
                @foreach($daftarHari as $hariItem)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border-2 border-gray-300 dark:border-gray-600 overflow-hidden">
                        <!-- Header Tanggal -->
                        <div class="px-5 py-4 border-b-2 border-gray-300 dark:border-gray-600 flex flex-wrap items-center justify-between gap-3 bg-gray-50/50 dark:bg-gray-850">
                            <div class="flex items-center gap-3 flex-wrap">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-blue-600 dark:text-blue-400">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                        <path d="M16 3v4" />
                                        <path d="M8 3v4" />
                                        <path d="M4 11h16" />
                                    </svg>
                                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">
                                        {{ $hariItem['tanggal_label'] }}
                                    </h3>
                                </div>

                                @if($hariItem['is_hari_ini'])
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-600 text-white shadow-xs">
                                        Hari ini
                                    </span>
                                @endif
                            </div>

                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 px-3 py-1 rounded-lg border-2 border-gray-300 dark:border-gray-600">
                                {{ $hariItem['jadwals']->count() }} Jadwal
                            </span>
                        </div>

                        <!-- Daftar Jadwal pada Tanggal Ini -->
                        <div class="p-5">
                            @if($hariItem['jadwals']->isEmpty())
                                <div class="py-4 px-4 text-sm text-gray-600 dark:text-gray-300 flex items-center gap-2 bg-gray-50 dark:bg-gray-700/30 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-gray-400 flex-shrink-0">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                        <path d="M12 9h.01" />
                                        <path d="M11 12h1v4h1" />
                                    </svg>
                                    <span>Tidak ada jadwal</span>
                                </div>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($hariItem['jadwals'] as $jadwal)
                                        <a href="{{ route('guru.absensi.show', ['jadwal' => $jadwal->id, 'tanggal' => $jadwal->tanggal_target]) }}"
                                           class="group block bg-white dark:bg-gray-800 rounded-xl p-5 border-2 border-gray-300 dark:border-gray-600 shadow-xs hover:shadow-md hover:border-blue-500 dark:hover:border-blue-400 transition duration-150 flex flex-col justify-between">
                                            <div>
                                                <!-- Status Badge & Jam -->
                                                <div class="flex items-center justify-between gap-2 mb-3">
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <x-ui.status-badge :status="$jadwal->status_absensi === 'Sudah diabsen' ? 'sudah_diabsen' : 'belum_diabsen'" :label="$jadwal->status_absensi" />

                                                        @if($jadwal->is_hari_ini)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                                Hari ini
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                                        {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB
                                                    </span>
                                                </div>

                                                <!-- Mapel & Kelas -->
                                                <div class="mb-3">
                                                    <h4 class="font-bold text-lg text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">
                                                        {{ $jadwal->mapel->nama }}
                                                    </h4>
                                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-0.5">
                                                        Kelas: <span class="text-gray-900 dark:text-gray-100 font-bold">{{ $jadwal->kelas->nama }}</span>
                                                        @if($jadwal->kelas->jurusan)
                                                            <span class="text-xs font-normal text-gray-500 dark:text-gray-400">({{ $jadwal->kelas->jurusan->nama }})</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Tombol Aksi Buka Halaman Absensi -->
                                            <div class="mt-4 pt-3 border-t-2 border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                                <span class="text-xs font-bold {{ $jadwal->status_absensi === 'Sudah diabsen' ? 'text-emerald-700 dark:text-emerald-400' : 'text-blue-600 dark:text-blue-400' }}">
                                                    {{ $jadwal->status_absensi === 'Sudah diabsen' ? 'Koreksi Absensi' : 'Isi Absensi' }}
                                                </span>
                                                <span class="inline-flex items-center text-xs font-bold {{ $jadwal->status_absensi === 'Sudah diabsen' ? 'text-emerald-700 dark:text-emerald-400' : 'text-blue-600 dark:text-blue-400' }} group-hover:translate-x-0.5 transition-transform">
                                                    Buka Form &rarr;
                                                </span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
