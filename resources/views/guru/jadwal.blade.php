<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Jadwal Mengajar') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if($errors->any())
                <div class="mb-4">
                    <x-alert type="danger">
                        {{ $errors->first() }}
                    </x-alert>
                </div>
            @endif

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
                <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
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
                    
                    {{-- Filter Hari, Kelas, Mapel & Tombol Navigasi --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                        <form method="GET" action="{{ route('guru.jadwal') }}" class="flex flex-wrap items-center gap-2">
                            {{-- Dropdown Hari --}}
                            <select name="hari" onchange="this.form.submit()" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-xs text-xs py-1.5 px-3">
                                <option value="">Semua Hari</option>
                                @foreach(['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu'] as $v => $l)
                                    <option value="{{ $v }}" {{ strtolower((string)$filterHari) === $v ? 'selected' : '' }}>
                                        {{ $l }} {{ strtolower($hariIni) === $v ? '(Hari Ini)' : '' }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- Dropdown Kelas --}}
                            <select name="kelas_id" onchange="this.form.submit()" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-xs text-xs py-1.5 px-3">
                                <option value="">Semua Kelas</option>
                                @foreach($daftarKelas as $k)
                                    <option value="{{ $k['id'] }}" {{ $filterKelasId == $k['id'] ? 'selected' : '' }}>
                                        {{ $k['nama'] }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- Dropdown Mata Pelajaran --}}
                            <select name="mapel_id" onchange="this.form.submit()" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-xs text-xs py-1.5 px-3">
                                <option value="">Semua Mapel</option>
                                @foreach($daftarMapel as $m)
                                    <option value="{{ $m['id'] }}" {{ $filterMapelId == $m['id'] ? 'selected' : '' }}>
                                        {{ $m['nama'] }}
                                    </option>
                                @endforeach
                            </select>

                            @if(!empty($filterHari) || !empty($filterKelasId) || !empty($filterMapelId))
                                <a href="{{ route('guru.jadwal') }}" class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 px-2 py-1.5 border border-gray-300 dark:border-gray-700 rounded-md">
                                    Reset
                                </a>
                            @endif
                        </form>
                        <a href="{{ route('guru.dashboard') }}" class="inline-flex items-center text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 gap-1 self-start sm:self-auto ml-2">
                            &larr; Dashboard
                        </a>
                    </div>
                </div>

                {{-- Banner Filter Aktif --}}
                @if($filterHari || $filterKelasId || $filterMapelId)
                    <div class="px-5 py-3 bg-emerald-50/70 dark:bg-emerald-950/20 border-b border-emerald-100 dark:border-emerald-900/40 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2 flex-wrap text-xs">
                            <span class="font-semibold text-emerald-800 dark:text-emerald-300">Filter aktif:</span>
                            @if($filterHari)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200 font-medium">
                                    Hari: {{ ucfirst($filterHari) }}
                                    <a href="{{ route('guru.jadwal', array_filter(['kelas_id' => $filterKelasId, 'mapel_id' => $filterMapelId])) }}" class="hover:text-emerald-950 dark:hover:text-white font-bold" aria-label="Hapus filter hari">&times;</a>
                                </span>
                            @endif
                            @if($activeKelas)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200 font-medium">
                                    Kelas: {{ $activeKelas['nama'] }}
                                    <a href="{{ route('guru.jadwal', array_filter(['hari' => $filterHari, 'mapel_id' => $filterMapelId])) }}" class="hover:text-emerald-950 dark:hover:text-white font-bold" aria-label="Hapus filter kelas">&times;</a>
                                </span>
                            @endif
                            @if($activeMapel)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200 font-medium">
                                    Mata pelajaran: {{ $activeMapel['nama'] }}
                                    <a href="{{ route('guru.jadwal', array_filter(['hari' => $filterHari, 'kelas_id' => $filterKelasId])) }}" class="hover:text-emerald-950 dark:hover:text-white font-bold" aria-label="Hapus filter mata pelajaran">&times;</a>
                                </span>
                            @endif
                        </div>
                        <a href="{{ route('guru.jadwal') }}" class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 hover:text-emerald-900 dark:hover:text-emerald-200 hover:underline">
                            Reset Semua Filter
                        </a>
                    </div>
                @endif

                <div class="p-0">
                    @if($jadwals->isEmpty())
                        <div class="py-12 text-center px-4">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-500 dark:text-blue-400 mb-3">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                @if(!empty($filterHari) || !empty($filterKelasId) || !empty($filterMapelId))
                                    Tidak Ada Jadwal Mengajar yang Cocok
                                @else
                                    Belum Ada Jadwal Mengajar
                                @endif
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-md mx-auto">
                                @if(!empty($filterHari) || !empty($filterKelasId) || !empty($filterMapelId))
                                    Tidak ditemukan jadwal mengajar yang sesuai dengan filter pencarian. Coba ubah atau reset filter untuk menampilkan semua jadwal.
                                @else
                                    Anda belum memiliki jadwal mengajar yang terdaftar dalam sistem. Silakan hubungi bagian kurikulum atau operator sekolah jika terdapat kekeliruan.
                                @endif
                            </p>
                            <div class="mt-5 flex justify-center gap-2">
                                @if(!empty($filterHari) || !empty($filterKelasId) || !empty($filterMapelId))
                                    <a href="{{ route('guru.jadwal') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-xs font-semibold uppercase tracking-widest rounded-lg shadow-xs transition">
                                        Reset Semua Filter
                                    </a>
                                @endif
                                <a href="{{ route('guru.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold uppercase tracking-widest rounded-lg shadow-xs transition gap-2">
                                    Kembali ke Dashboard
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                                @foreach($jadwals as $jadwal)
                                    @php
                                        $hariLower = strtolower($jadwal->hari);
                                        $isHariIni = ($hariLower === strtolower($hariIni));
                                        $isMatchFilter = ($filterKelasId && $jadwal->kelas_id == $filterKelasId) || ($filterMapelId && $jadwal->mapel_id == $filterMapelId);

                                        $badgeClasses = match($hariLower) {
                                            'senin' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                            'selasa' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 border-purple-200 dark:border-purple-800',
                                            'rabu' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                                            'kamis' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                            'jumat' => 'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300 border-teal-200 dark:border-teal-800',
                                            'sabtu' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600',
                                        };
                                        $linkTanggal = $jadwal->target_tanggal ?: \Carbon\Carbon::now('Asia/Jakarta')->toDateString();

                                        if ($isMatchFilter) {
                                            $cardBgClasses = 'bg-emerald-50/70 dark:bg-emerald-950/30 border-l-4 border-l-emerald-600 border-emerald-300 dark:border-emerald-700 shadow-xs ring-1 ring-emerald-500/30';
                                        } elseif ($isHariIni) {
                                            $cardBgClasses = 'bg-blue-50/50 dark:bg-blue-950/20 border-blue-300 dark:border-blue-700 ring-1 ring-blue-400/30';
                                        } else {
                                            $cardBgClasses = 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700';
                                        }
                                    @endphp
                                    <a href="{{ route('guru.absensi.show', ['jadwal' => $jadwal->id, 'tanggal' => $linkTanggal]) }}"
                                       class="group block rounded-xl p-5 border shadow-xs hover:shadow-md hover:border-blue-500 dark:hover:border-blue-400 transition duration-150 flex flex-col justify-between {{ $cardBgClasses }}">
                                        <div>
                                            <!-- Header Kartu: Hari, Sorotan Hari Ini, & Status Badge -->
                                            <div class="flex items-center justify-between gap-2 mb-3">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $badgeClasses }}">
                                                        {{ ucfirst($jadwal->hari) }}
                                                    </span>
                                                    @if($isHariIni)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 border border-blue-200 dark:border-blue-700">
                                                            <svg class="w-3 h-3 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                            Hari ini
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                @if($jadwal->status_absensi === 'Sudah diabsen')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 border border-green-200 dark:border-green-800">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                        Sudah diabsen
                                                    </span>
                                                @elseif($jadwal->status_absensi === 'Hari ini')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        Hari ini
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                        Belum diabsen
                                                    </span>
                                                @endif
                                            </div>

                                            <!-- Mapel & Kelas -->
                                            <div class="mb-4">
                                                <h4 class="font-bold text-lg text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                                    {{ $jadwal->mapel->nama }}
                                                </h4>
                                                <div class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1 flex items-center justify-between">
                                                    <span>Kelas: {{ $jadwal->kelas->nama }}</span>
                                                    @if($jadwal->kelas->jurusan)
                                                        <span class="text-xs font-normal text-gray-500 dark:text-gray-400">({{ $jadwal->kelas->jurusan->nama }})</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Jam & Tanggal Sesi -->
                                            <div class="space-y-1.5 text-xs text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-gray-500 dark:text-gray-400">Jam:</span>
                                                    <span class="font-semibold text-gray-900 dark:text-gray-100">
                                                        {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-gray-500 dark:text-gray-400">Tanggal:</span>
                                                    <span class="font-bold text-gray-900 dark:text-gray-100">
                                                        {{ $jadwal->target_tanggal ? \Carbon\Carbon::parse($jadwal->target_tanggal, 'Asia/Jakarta')->isoFormat('dddd, D MMMM YYYY') : '-' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Footer Aksi -->
                                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs">
                                            <span class="text-gray-400 dark:text-gray-500">Buka absensi</span>
                                            <span class="font-bold text-blue-600 dark:text-blue-400 group-hover:translate-x-0.5 transition-transform flex items-center gap-1">
                                                @if($jadwal->status_absensi === 'Sudah diabsen')
                                                    Koreksi Absensi &rarr;
                                                @elseif($jadwal->status_absensi === 'Hari ini')
                                                    Absen Sekarang &rarr;
                                                @else
                                                    Isi Susulan &rarr;
                                                @endif
                                            </span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
