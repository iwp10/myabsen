<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Koreksi Absensi') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Banner Header -->
            <div class="bg-blue-600 dark:bg-blue-700 rounded-2xl p-6 text-white shadow-md relative overflow-hidden" style="background-color: #2563eb;">
                <div class="absolute -right-8 -bottom-10 opacity-10 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-56 h-56 text-white">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M9 5h10l2 2l-2 2h-10a2 2 0 0 1 -2 -2a2 2 0 0 1 2 -2" />
                        <path d="M13 13h6l2 2l-2 2h-6a2 2 0 0 1 -2 -2a2 2 0 0 1 2 -2" />
                        <path d="M7 21h8l2 2l-2 2h-8a2 2 0 0 1 -2 -2a2 2 0 0 1 2 -2" />
                    </svg>
                </div>
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white mb-2">
                            Pusat Kendali Koreksi &bull; Administrator
                        </span>
                        <h1 class="text-2xl font-bold tracking-tight text-white">Koreksi Absensi Siswa</h1>
                        <p class="text-white/90 text-sm mt-1">
                            Pilih tanggal historis dan kelas untuk meninjau status sesi serta mengoreksi data absensi.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Error Banner -->
            @if ($errorTanggal)
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative shadow-xs" role="alert">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M12 9v2m0 4v.01" />
                            <path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" />
                        </svg>
                        <span class="text-sm font-medium">{{ $errorTanggal }}</span>
                    </div>
                </div>
            @endif

            <!-- Form Filter Pencarian -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-200 dark:border-gray-700">
                <form method="GET" action="{{ route('admin.koreksi-absensi.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                    <!-- Input Tanggal -->
                    <div>
                        <x-input-label for="tanggal" :value="__('Tanggal Sesi Absensi')" />
                        <input type="date"
                               id="tanggal"
                               name="tanggal"
                               value="{{ $tanggal }}"
                               max="{{ \Carbon\Carbon::now('Asia/Jakarta')->toDateString() }}"
                               required
                               class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 rounded-md shadow-sm text-sm">
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Bebas memilih tanggal lampau (tidak boleh masa depan).</p>
                    </div>

                    <!-- Pilihan Kelas -->
                    <div>
                        <x-input-label for="kelas_id" :value="__('Pilih Kelas (Opsional)')" />
                        <select id="kelas_id"
                                name="kelas_id"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 rounded-md shadow-sm text-sm">
                            <option value="">Semua Kelas</option>
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" {{ $kelasId == $kelas->id ? 'selected' : '' }}>
                                    {{ $kelas->nama }} @if($kelas->jurusan) ({{ $kelas->jurusan->nama }}) @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Saring daftar jadwal berdasarkan kelas tertentu.</p>
                    </div>

                    <!-- Tombol Cari -->
                    <div>
                        <button type="submit"
                                class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold uppercase tracking-widest rounded-lg shadow-xs transition duration-150 gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                                <path d="M21 21l-6 -6" />
                            </svg>
                            <span>Tampilkan Jadwal</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Daftar Jadwal & Status Absensi -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-blue-600 dark:text-blue-400">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" />
                                <path d="M16 3v4" />
                                <path d="M8 3v4" />
                                <path d="M4 11h16" />
                            </svg>
                            Daftar Jadwal Pada Hari {{ ucfirst($hari ?? '-') }} ({{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('D MMMM YYYY') }})
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Klik tombol absensi pada jadwal untuk membuka form dan melakukan koreksi data.
                        </p>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                        Total: <span class="font-bold text-gray-900 dark:text-gray-100">{{ $jadwals->count() }}</span> jadwal
                    </div>
                </div>

                <div class="p-0">
                    @if($jadwals->isEmpty())
                        <div class="py-12 text-center px-4">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-500 dark:text-blue-400 mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19.823 19.824a2 2 0 0 1 -1.823 1.176h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 1.175 -1.823m3.825 -.177h9a2 2 0 0 1 2 2v9" />
                                    <path d="M16 3v4" />
                                    <path d="M8 3v1" />
                                    <path d="M4 11h7m4 0h5" />
                                    <path d="M3 3l18 18" />
                                </svg>
                            </div>
                            <p class="text-base font-semibold text-gray-900 dark:text-gray-100">Tidak Ada Jadwal Ditemukan</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-md mx-auto">
                                Tidak ada jadwal mata pelajaran yang aktif pada hari {{ ucfirst($hari ?? '-') }} untuk filter yang dipilih. Silakan pilih tanggal atau kelas lain.
                            </p>
                        </div>
                    @else
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                                @foreach($jadwals as $jadwal)
                                    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-xs hover:shadow-md hover:border-blue-400 dark:hover:border-blue-500 transition duration-150 flex flex-col justify-between">
                                        <div>
                                            <!-- Status & Jam -->
                                            <div class="flex items-center justify-between gap-2 mb-3">
                                                @if($jadwal->status_absensi === 'Sudah diabsen')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 border border-green-200 dark:border-green-800">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                        Sudah diabsen
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                        Belum diabsen
                                                    </span>
                                                @endif

                                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                                                    {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                                                </span>
                                            </div>

                                            <!-- Mapel & Kelas -->
                                            <div class="mb-3">
                                                <h4 class="font-bold text-lg text-gray-900 dark:text-gray-100">
                                                    {{ $jadwal->mapel->nama }}
                                                </h4>
                                                <div class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-0.5">
                                                    Kelas: {{ $jadwal->kelas->nama }}
                                                    @if($jadwal->kelas->jurusan)
                                                        <span class="text-xs font-normal text-gray-500 dark:text-gray-400">({{ $jadwal->kelas->jurusan->nama }})</span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    Guru: <span class="font-medium text-gray-800 dark:text-gray-200">{{ $jadwal->guru?->user?->name ?? 'Belum ditentukan' }}</span>
                                                </div>
                                            </div>

                                            <!-- Log Info jika sudah diabsen -->
                                            @if($jadwal->sesi_koreksi)
                                                <div class="text-[11px] text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/40 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700">
                                                    @if($jadwal->sesi_koreksi->diubah_oleh)
                                                        Terakhir dikoreksi oleh user #{{ $jadwal->sesi_koreksi->diubah_oleh }} pada {{ $jadwal->sesi_koreksi->updated_at?->format('H:i') }}
                                                    @else
                                                        Diabsen pertama kali oleh user #{{ $jadwal->sesi_koreksi->diabsen_oleh }} pada {{ $jadwal->sesi_koreksi->created_at?->format('H:i') }}
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Tombol Aksi Buka Form Absensi -->
                                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                                            <a href="{{ route('guru.absensi.show', ['jadwal' => $jadwal->id, 'tanggal' => $jadwal->tanggal_koreksi]) }}"
                                               class="w-full inline-flex justify-center items-center px-4 py-2 {{ $jadwal->status_absensi === 'Sudah diabsen' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white text-xs font-semibold uppercase tracking-wider rounded-lg shadow-xs transition gap-1.5">
                                                <span>{{ $jadwal->status_absensi === 'Sudah diabsen' ? 'Koreksi Absensi' : 'Isi Absensi' }}</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M9 6l6 6l-6 6" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
