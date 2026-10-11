<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
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
            <div class="bg-emerald-700 dark:bg-emerald-800 rounded-2xl p-6 text-white shadow-md relative overflow-hidden border-2 border-emerald-800 dark:border-emerald-700">
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
                        <span class="text-3xl font-extrabold text-white">
                            {{ $jadwals->count() }} <span class="text-sm font-normal">Sesi / Minggu</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Tabel / Grid Jadwal Mengajar -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border-2 border-slate-300 dark:border-slate-600 overflow-hidden">
                <div class="p-5 border-b-2 border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-700 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Seluruh Jadwal Mengajar Seminggu
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                            Daftar terurut berdasarkan hari pelaksanaan dan jam mulai pelajaran
                        </p>
                    </div>
                    
                    {{-- Filter Hari, Kelas, Mapel & Tombol Navigasi --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto">
                        <form method="GET" action="{{ route('guru.jadwal') }}" class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2">
                            {{-- Dropdown Hari --}}
                            <select name="hari" onchange="this.form.submit()" class="border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 dark:[color-scheme:dark] focus:outline-none focus:ring-4 focus:ring-emerald-500 rounded-xl text-base sm:text-xs min-h-[44px] py-2 sm:py-1.5 px-3">
                                <option value="">Semua Hari</option>
                                @foreach(['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu'] as $v => $l)
                                    <option value="{{ $v }}" {{ strtolower((string)$filterHari) === $v ? 'selected' : '' }}>
                                        {{ $l }} {{ strtolower($hariIni) === $v ? '(Hari Ini)' : '' }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- Dropdown Kelas --}}
                            <select name="kelas_id" onchange="this.form.submit()" class="border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 dark:[color-scheme:dark] focus:outline-none focus:ring-4 focus:ring-emerald-500 rounded-xl text-base sm:text-xs min-h-[44px] py-2 sm:py-1.5 px-3">
                                <option value="">Semua Kelas</option>
                                @foreach($daftarKelas as $k)
                                    <option value="{{ $k['id'] }}" {{ $filterKelasId == $k['id'] ? 'selected' : '' }}>
                                        {{ $k['nama'] }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- Dropdown Mata Pelajaran --}}
                            <select name="mapel_id" onchange="this.form.submit()" class="border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 dark:[color-scheme:dark] focus:outline-none focus:ring-4 focus:ring-emerald-500 rounded-xl text-base sm:text-xs min-h-[44px] py-2 sm:py-1.5 px-3">
                                <option value="">Semua Mapel</option>
                                @foreach($daftarMapel as $m)
                                    <option value="{{ $m['id'] }}" {{ $filterMapelId == $m['id'] ? 'selected' : '' }}>
                                        {{ $m['nama'] }}
                                    </option>
                                @endforeach
                            </select>

                            @if(!empty($filterHari) || !empty($filterKelasId) || !empty($filterMapelId))
                                <a href="{{ route('guru.jadwal') }}" class="inline-flex items-center justify-center text-xs font-semibold text-slate-700 hover:text-slate-900 dark:text-slate-200 dark:hover:text-white px-3 py-2 sm:py-1.5 border-2 border-slate-400 dark:border-slate-500 rounded-xl min-h-[44px] hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                                    Reset
                                </a>
                            @endif
                        </form>
                        <a href="{{ route('guru.dashboard') }}" class="inline-flex items-center justify-center text-xs font-bold text-emerald-700 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300 gap-1 min-h-[44px] px-2">
                            &larr; Dashboard
                        </a>
                    </div>
                </div>

                {{-- Banner Filter Aktif --}}
                @if($filterHari || $filterKelasId || $filterMapelId)
                    <div class="px-5 py-3 bg-emerald-50 dark:bg-emerald-950/20 border-b-2 border-emerald-300 dark:border-emerald-700 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2 flex-wrap text-xs">
                            <span class="font-bold text-emerald-800 dark:text-emerald-300">Filter aktif:</span>
                            @if($filterHari)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-900 border border-emerald-400 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/50 font-semibold">
                                    Hari: {{ ucfirst($filterHari) }}
                                    <a href="{{ route('guru.jadwal', array_filter(['kelas_id' => $filterKelasId, 'mapel_id' => $filterMapelId])) }}" class="hover:text-emerald-950 dark:hover:text-white font-bold ml-1" aria-label="Hapus filter hari">&times;</a>
                                </span>
                            @endif
                            @if($activeKelas)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-900 border border-emerald-400 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/50 font-semibold">
                                    Kelas: {{ $activeKelas['nama'] }}
                                    <a href="{{ route('guru.jadwal', array_filter(['hari' => $filterHari, 'mapel_id' => $filterMapelId])) }}" class="hover:text-emerald-950 dark:hover:text-white font-bold ml-1" aria-label="Hapus filter kelas">&times;</a>
                                </span>
                            @endif
                            @if($activeMapel)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-900 border border-emerald-400 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/50 font-semibold">
                                    Mata pelajaran: {{ $activeMapel['nama'] }}
                                    <a href="{{ route('guru.jadwal', array_filter(['hari' => $filterHari, 'kelas_id' => $filterKelasId])) }}" class="hover:text-emerald-950 dark:hover:text-white font-bold ml-1" aria-label="Hapus filter mata pelajaran">&times;</a>
                                </span>
                            @endif
                        </div>
                        <a href="{{ route('guru.jadwal') }}" class="text-xs font-bold text-emerald-700 dark:text-emerald-400 hover:text-emerald-900 dark:hover:text-emerald-200 underline">
                            Reset Semua Filter
                        </a>
                    </div>
                @endif

                <div class="p-0">
                    @if($jadwals->isEmpty())
                        <div class="py-12 text-center px-4">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 mb-3 border-2 border-slate-300 dark:border-slate-700">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <p class="text-base font-bold text-slate-900 dark:text-white">
                                @if(!empty($filterHari) || !empty($filterKelasId) || !empty($filterMapelId))
                                    Tidak Ada Jadwal Mengajar yang Cocok
                                @else
                                    Belum Ada Jadwal Mengajar
                                @endif
                            </p>
                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1 max-w-md mx-auto">
                                @if(!empty($filterHari) || !empty($filterKelasId) || !empty($filterMapelId))
                                    Tidak ditemukan jadwal mengajar yang sesuai dengan filter pencarian. Coba ubah atau reset filter untuk menampilkan semua jadwal.
                                @else
                                    Anda belum memiliki jadwal mengajar yang terdaftar dalam sistem. Silakan hubungi bagian kurikulum atau operator sekolah jika terdapat kekeliruan.
                                @endif
                            </p>
                            <div class="mt-5 flex justify-center gap-3 flex-wrap">
                                @if(!empty($filterHari) || !empty($filterKelasId) || !empty($filterMapelId))
                                    <a href="{{ route('guru.jadwal') }}" class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-slate-800 border-2 border-slate-400 dark:border-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-900 dark:text-slate-100 text-xs font-bold uppercase tracking-widest rounded-xl shadow-xs transition min-h-[44px]">
                                        Reset Semua Filter
                                    </a>
                                @endif
                                <a href="{{ route('guru.dashboard') }}" class="inline-flex items-center px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950 text-xs font-bold uppercase tracking-widest rounded-xl shadow-xs transition gap-2 min-h-[44px]">
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

                                        $badgeClasses = \App\Support\Ui::badgeHari($jadwal->hari);
                                        $linkTanggal = $jadwal->target_tanggal ?: \Carbon\Carbon::now('Asia/Jakarta')->toDateString();

                                        if ($isMatchFilter) {
                                            $cardBgClasses = 'bg-emerald-50 dark:bg-emerald-500/10 border-2 border-l-8 border-l-emerald-600 dark:border-l-emerald-400 border-emerald-400 dark:border-emerald-500/40 shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5';
                                        } elseif ($isHariIni) {
                                            $cardBgClasses = 'bg-white dark:bg-slate-800 border-2 border-l-8 border-l-emerald-600 dark:border-l-emerald-400 border-slate-300 dark:border-slate-600 shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5';
                                        } else {
                                            $cardBgClasses = 'bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 hover:border-slate-400 dark:hover:border-slate-500 shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5';
                                        }
                                    @endphp
                                    <a href="{{ route('guru.absensi.show', ['jadwal' => $jadwal->id, 'tanggal' => $linkTanggal]) }}"
                                       class="group block rounded-xl p-5 transition duration-150 flex flex-col justify-between {{ $cardBgClasses }}">
                                        <div>
                                            <!-- Header Kartu: Hari, Sorotan Hari Ini, & Status Badge -->
                                            <div class="flex items-center justify-between gap-2 mb-3">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="{{ $badgeClasses }}">
                                                        {{ ucfirst($jadwal->hari) }}
                                                    </span>
                                                    @if($isHariIni)
                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-700 text-white dark:bg-emerald-400 dark:text-slate-950">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                            Hari ini
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                @if($jadwal->status_absensi === 'Sudah diabsen')
                                                    <x-ui.status-badge status="sudah_diabsen" label="Sudah diabsen" />
                                                @elseif($jadwal->status_absensi === 'Hari ini')
                                                    <x-ui.status-badge status="belum_diabsen" label="Hari ini" />
                                                @else
                                                    <x-ui.status-badge status="belum_diabsen" label="Belum diabsen" />
                                                @endif
                                            </div>

                                            <!-- Mapel & Kelas -->
                                            <div class="mb-4">
                                                <h4 class="font-bold text-lg text-slate-900 dark:text-white group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">
                                                    {{ $jadwal->mapel->nama }}
                                                </h4>
                                                <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 mt-1 flex items-center justify-between">
                                                    <span>Kelas: {{ $jadwal->kelas->nama }}</span>
                                                    @if($jadwal->kelas->jurusan)
                                                        <span class="text-xs font-normal text-slate-600 dark:text-slate-400">({{ $jadwal->kelas->jurusan->nama }})</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Jam & Tanggal Sesi -->
                                            <div class="space-y-1.5 text-xs text-slate-800 dark:text-slate-200 bg-slate-100 dark:bg-slate-700/60 p-3 rounded-xl border-2 border-slate-200 dark:border-slate-700">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-slate-600 dark:text-slate-300">Jam:</span>
                                                    <span class="font-bold text-slate-900 dark:text-white font-mono">
                                                        {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-slate-600 dark:text-slate-300">Tanggal:</span>
                                                    <span class="font-bold text-slate-900 dark:text-white">
                                                        {{ $jadwal->target_tanggal ? \Carbon\Carbon::parse($jadwal->target_tanggal, 'Asia/Jakarta')->isoFormat('dddd, D MMMM YYYY') : '-' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Footer Aksi -->
                                        <div class="mt-4 pt-3 border-t-2 border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
                                            <span class="text-slate-600 dark:text-slate-400">Buka absensi</span>
                                            <span class="font-bold text-emerald-700 dark:text-emerald-400 group-hover:translate-x-0.5 transition-transform flex items-center gap-1">
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
