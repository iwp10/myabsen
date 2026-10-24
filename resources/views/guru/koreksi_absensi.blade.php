<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 dark:text-white leading-tight">
            {{ __('Jadwal & Koreksi Absensi') }}
        </h2>
    </x-slot>

    <div class="py-4 sm:py-8"
         x-data="{
             openDates: {
                 @foreach($daftarHari as $item)
                     '{{ $item['tanggal'] }}': {{ $item['is_hari_ini'] ? 'true' : 'false' }},
                 @endforeach
             },
             toggleDate(dateStr) {
                 this.openDates[dateStr] = !this.openDates[dateStr];
             }
         }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            <!-- Banner Header Kompak -->
            <div class="bg-emerald-700 dark:bg-emerald-800 rounded-2xl p-4 sm:p-6 text-white shadow-md relative overflow-hidden border-2 border-emerald-800 dark:border-emerald-700">
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white mb-1.5">
                            Pusat Koreksi & Susulan • Portal Guru
                        </span>
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Jadwal & Koreksi Absensi</h1>
                        <p class="text-white/90 text-xs sm:text-sm mt-0.5">
                            Daftar jadwal mengajar 7 hari terakhir (hari ini s.d. H-6) untuk pengisian susulan atau koreksi.
                        </p>
                    </div>
                    <div class="flex items-center justify-between sm:flex-col sm:items-end pt-2 sm:pt-0 border-t border-white/20 sm:border-0">
                        <span class="text-xs text-white/90 uppercase font-medium tracking-wider">Guru Pengajar</span>
                        <span class="text-sm font-bold text-white block">
                            {{ Auth::user()->name }}
                        </span>
                        <span class="text-xs text-white/80 block">
                            NIP: {{ $guru?->nip ?? '-' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Daftar 7 Hari Terakhir dengan Accordion (Hari Ini Terbuka Bawaan) -->
            <div class="space-y-3 sm:space-y-4">
                @foreach($daftarHari as $hariItem)
                    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border-2 border-slate-300 dark:border-slate-600 overflow-hidden">
                        <!-- Header Bar Accordion Tanggal/Hari -->
                        <button type="button"
                                @click="toggleDate('{{ $hariItem['tanggal'] }}')"
                                class="w-full px-4 py-3 sm:px-5 sm:py-4 border-b-2 border-slate-300 dark:border-slate-600 flex items-center justify-between gap-3 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white text-left transition hover:bg-slate-200/70 dark:hover:bg-slate-700/70 min-h-[44px]"
                                :aria-expanded="openDates['{{ $hariItem['tanggal'] }}'] ? 'true' : 'false'">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <x-ui.icon name="calendar" size="sm" class="text-emerald-700 dark:text-emerald-400 shrink-0" />
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                                    {{ $hariItem['tanggal_label'] }}
                                </h3>
                                @if($hariItem['is_hari_ini'])
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-700 text-white dark:bg-emerald-400 dark:text-slate-950 shadow-xs">
                                        Hari ini
                                    </span>
                                @endif
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-white text-slate-700 dark:bg-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600">
                                    {{ $hariItem['jadwals']->count() }} Jadwal
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold text-slate-600 dark:text-slate-400 hidden sm:inline"
                                      x-text="openDates['{{ $hariItem['tanggal'] }}'] ? 'Tutup' : 'Buka'"></span>
                                <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 transition-transform duration-200"
                                     :class="openDates['{{ $hariItem['tanggal'] }}'] ? 'rotate-180' : ''"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>

                        <!-- Daftar Jadwal pada Tanggal Ini -->
                        <div x-show="openDates['{{ $hariItem['tanggal'] }}']"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="p-4 sm:p-5">
                            @if($hariItem['jadwals']->isEmpty())
                                <div class="py-4 px-4 text-xs sm:text-sm text-slate-600 dark:text-slate-300 flex items-center gap-2 bg-slate-100 dark:bg-slate-800/60 rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-600">
                                    <x-ui.icon name="info" size="sm" class="text-slate-600 dark:text-slate-400 shrink-0" />
                                    <span>Tidak ada jadwal mengajar pada tanggal ini.</span>
                                </div>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                                    @foreach($hariItem['jadwals'] as $jadwal)
                                        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 sm:p-5 border-2 border-slate-300 dark:border-slate-600 shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5 hover:border-slate-400 dark:hover:border-slate-500 transition duration-150 flex flex-col justify-between">
                                            <div>
                                                <!-- Status Badge & Jam -->
                                                <div class="flex items-center justify-between gap-2 mb-2.5">
                                                    <x-ui.status-badge :status="$jadwal->status_absensi === 'Sudah diabsen' ? 'sudah_diabsen' : 'belum_diabsen'" :label="$jadwal->status_absensi" />
                                                    <span class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700/60 px-2 py-1 rounded-md border border-slate-300 dark:border-slate-600">
                                                        {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB
                                                    </span>
                                                </div>

                                                <!-- Mapel & Kelas -->
                                                <div class="mb-3">
                                                    <h4 class="font-bold text-base sm:text-lg text-slate-900 dark:text-white leading-snug">
                                                        {{ $jadwal->mapel->nama }}
                                                    </h4>
                                                    <p class="text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 mt-0.5">
                                                        Kelas: <strong class="text-slate-900 dark:text-white">{{ $jadwal->kelas->nama }}</strong>
                                                        @if($jadwal->kelas->jurusan)
                                                            <span class="text-xs text-slate-600 dark:text-slate-400 font-normal">({{ $jadwal->kelas->jurusan->nama }})</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Tombol Aksi Lebar Penuh min 44px -->
                                            <a href="{{ route('guru.absensi.show', ['jadwal' => $jadwal->id, 'tanggal' => $jadwal->tanggal_target]) }}"
                                               class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 min-h-[44px] rounded-xl font-bold text-xs uppercase tracking-wider transition shadow-xs {{ $jadwal->status_absensi === 'Sudah diabsen' ? 'bg-white hover:bg-slate-100 text-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-white border-2 border-slate-300 dark:border-slate-500' : 'bg-emerald-700 hover:bg-emerald-800 text-white dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950' }}">
                                                <span>{{ $jadwal->status_absensi === 'Sudah diabsen' ? 'Koreksi Absensi' : 'Isi Susulan' }}</span>
                                                <x-ui.icon name="arrow-right" size="sm" aria-hidden="true" />
                                            </a>
                                        </div>
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
