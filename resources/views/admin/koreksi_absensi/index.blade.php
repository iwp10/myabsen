<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Koreksi Absensi') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status') || session('success'))
                <x-alert type="success" :autoDismiss="true">
                    {{ session('status') ?: session('success') }}
                </x-alert>
            @endif

            <x-ui.page-header
                title="Koreksi Absensi Siswa"
                description="Pilih tanggal sesi lampau dan kelas rombongan belajar untuk meninjau status serta melakukan koreksi data kehadiran."
            />

            <!-- Error Banner -->
            @if ($errorTanggal)
                <div class="bg-red-100 dark:bg-red-950/40 border-2 border-red-400 dark:border-red-600 text-red-900 dark:text-red-200 px-4 py-3 rounded-xl relative shadow-xs" role="alert">
                    <div class="flex items-center gap-2 font-semibold text-sm">
                        <x-ui.icon name="x" size="sm" class="shrink-0 text-red-700 dark:text-red-400" />
                        <span>{{ $errorTanggal }}</span>
                    </div>
                </div>
            @endif

            <!-- Form Filter Pencarian -->
            <x-ui.filter-bar :action="route('admin.koreksi-absensi.index')" formClass="grid grid-cols-1 sm:grid-cols-12 gap-3 sm:gap-4 items-end w-full">
                <!-- Input Tanggal: 5 kolom pada sm: ke atas -->
                <div class="sm:col-span-5 min-w-0">
                    <label for="tanggal" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        {{ __('Tanggal Sesi Absensi') }}
                    </label>
                    <input type="date"
                           id="tanggal"
                           name="tanggal"
                           value="{{ $tanggal }}"
                           max="{{ \Carbon\Carbon::now('Asia/Jakarta')->toDateString() }}"
                           required
                           class="w-full min-w-0 appearance-none px-3 h-[44px] min-h-[44px] text-base sm:text-xs rounded-lg font-medium border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 dark:[color-scheme:dark] focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 focus:outline-none transition shadow-2xs">
                </div>

                <!-- Pilihan Kelas: 4 kolom pada sm: ke atas -->
                <div class="sm:col-span-4 min-w-0">
                    <label for="kelas_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        {{ __('Pilih Kelas (Opsional)') }}
                    </label>
                    <x-ui.select id="kelas_id" name="kelas_id">
                        <option value="">Semua Kelas</option>
                        @foreach(\App\Support\Ui::sortKelas($kelasList) as $k)
                            <option value="{{ $k->id }}" title="{{ \App\Support\Ui::labelKelas($k) }}" {{ (string)$kelasId === (string)$k->id ? 'selected' : '' }}>
                                {{ \App\Support\Ui::labelKelas($k) }}
                            </option>
                        @endforeach
                    </x-ui.select>
                </div>

                <!-- Tombol Cari: 3 kolom pada sm: ke atas -->
                <div class="sm:col-span-3 min-w-0">
                    <button type="submit" class="{{ \App\Support\Ui::tombol('primary') }} w-full justify-center h-[44px]">
                        <x-ui.icon name="search" size="sm" class="mr-1.5" />
                        <span>Tampilkan Jadwal</span>
                    </button>
                </div>
            </x-ui.filter-bar>

            <!-- Daftar Jadwal & Status Absensi -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border-2 border-slate-300 dark:border-slate-600 overflow-hidden">
                <div class="p-5 border-b-2 border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <x-ui.icon name="calendar" size="md" class="text-emerald-700 dark:text-emerald-400" />
                            <span>Daftar Jadwal Pada Hari {{ ucfirst($hari ?? '-') }} ({{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('D MMMM YYYY') }})</span>
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                            Klik tombol absensi pada jadwal untuk membuka form dan melakukan koreksi data.
                        </p>
                    </div>
                    <div class="text-xs text-slate-600 dark:text-slate-300 font-medium">
                        Total: <span class="font-bold text-slate-900 dark:text-white">{{ $jadwals->count() }}</span> jadwal
                    </div>
                </div>

                <div class="p-0">
                    @if($jadwals->isEmpty())
                        <div class="p-6">
                            <x-ui.empty-state
                                title="Tidak Ada Jadwal Ditemukan"
                                description="Tidak ada jadwal mata pelajaran yang aktif pada hari {{ ucfirst($hari ?? '-') }} untuk filter yang dipilih. Silakan pilih tanggal atau kelas lain."
                                icon="calendar"
                            />
                        </div>
                    @else
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                                @foreach($jadwals as $jadwal)
                                    <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border-2 border-slate-300 dark:border-slate-600 shadow-xs hover:shadow-md hover:border-emerald-500 dark:hover:border-emerald-400 transition flex flex-col justify-between">
                                        <div>
                                            <!-- Status & Jam -->
                                            <div class="flex items-center justify-between gap-2 mb-3">
                                                <x-ui.status-badge :status="$jadwal->status_absensi === 'Sudah diabsen' ? 'sudah_diabsen' : 'belum_diabsen'" />

                                                <span class="font-mono tabular-nums text-xs font-bold text-teal-700 dark:text-teal-400">
                                                    {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                                                </span>
                                            </div>

                                            <!-- Mapel & Kelas -->
                                            <div class="mb-3">
                                                <h4 class="font-bold text-lg text-slate-900 dark:text-white">
                                                    {{ $jadwal->mapel->nama }}
                                                </h4>
                                                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200 mt-0.5">
                                                    Kelas: {{ $jadwal->kelas->nama }}
                                                    @if($jadwal->kelas->jurusan)
                                                        <span class="text-xs font-normal text-slate-600 dark:text-slate-300">({{ $jadwal->kelas->jurusan->nama }})</span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                                                    Guru: <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $jadwal->guru?->user?->name ?? 'Belum ditentukan' }}</span>
                                                </div>
                                            </div>

                                            <!-- Log Info jika sudah diabsen -->
                                            @if($jadwal->sesi_koreksi)
                                                <div class="text-xs text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700/50 p-2.5 rounded-lg border border-slate-300 dark:border-slate-600">
                                                    @if($jadwal->sesi_koreksi->diubah_oleh)
                                                        Terakhir dikoreksi oleh user #{{ $jadwal->sesi_koreksi->diubah_oleh }} pada {{ $jadwal->sesi_koreksi->updated_at?->format('H:i') }}
                                                    @else
                                                        Diabsen pertama kali oleh user #{{ $jadwal->sesi_koreksi->diabsen_oleh }} pada {{ $jadwal->sesi_koreksi->created_at?->format('H:i') }}
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Tombol Aksi Buka Form Absensi -->
                                        <div class="mt-4 pt-3 border-t-2 border-slate-200 dark:border-slate-700">
                                            <a href="{{ route('guru.absensi.show', ['jadwal' => $jadwal->id, 'tanggal' => $jadwal->tanggal_koreksi]) }}"
                                               class="{{ $jadwal->status_absensi === 'Sudah diabsen' ? \App\Support\Ui::tombol('primary') : \App\Support\Ui::tombol('secondary') }} w-full justify-center">
                                                <span>{{ $jadwal->status_absensi === 'Sudah diabsen' ? 'Koreksi Absensi' : 'Isi Absensi' }}</span>
                                                <x-ui.icon name="chevron-right" size="sm" class="ml-1" />
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
