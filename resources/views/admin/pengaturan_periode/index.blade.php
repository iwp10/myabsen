<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pengaturan Periode Aktif') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8" x-data="{
        tahunAjaran: '{{ old('tahun_ajaran', $activePeriode['tahun_ajaran']) }}',
        semester: '{{ old('semester', $activePeriode['semester']) }}',
        saranTahunAjaran: '{{ $saran['tahun_ajaran'] }}',
        saranSemester: '{{ $saran['semester'] }}',
        showConfirmModal: false,
        terapkanSaran() {
            this.tahunAjaran = this.saranTahunAjaran;
            this.semester = this.saranSemester;
        }
    }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Status Message -->
            @if (session('status'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative shadow-xs flex items-center gap-2 dark:bg-green-900/40 dark:border-green-800 dark:text-green-300" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M5 12l5 5l10 -10" />
                    </svg>
                    <span class="block sm:inline text-sm font-medium">{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative shadow-xs dark:bg-red-900/40 dark:border-red-800 dark:text-red-300" role="alert">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- (a) Kotak Penjelasan Sederhana untuk Orang Awam -->
            <div class="bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900 rounded-2xl p-5 flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                        <path d="M12 9h.01" />
                        <path d="M11 12h1v4h1" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-blue-900 dark:text-blue-200">
                        Panduan Pengaturan Periode Akademik
                    </h3>
                    <p class="text-xs text-blue-800/90 dark:text-blue-300 mt-1 leading-relaxed">
                        Periode aktif menentukan jadwal pelajaran yang tampil secara otomatis di dashboard guru dan dashboard siswa saat ini. Data riwayat dan absensi pada periode lama tidak hilang dan tetap tersimpan utuh di basis data sekolah.
                    </p>
                </div>
            </div>

            <!-- (b) Kartu Periode Aktif Saat Ini -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-gray-100 dark:border-gray-700/60">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Status Saat Ini
                        </span>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-0.5">
                            Periode Akademik Aktif
                        </h3>
                    </div>
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-base font-bold">
                            Semester {{ $activePeriode['semester'] }} {{ $activePeriode['tahun_ajaran'] }}
                        </span>
                    </div>
                </div>

                <!-- (d) Kotak Saran Sistem -->
                <div class="mt-5 p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M12 3c1.657 0 3 1.343 3 3a3 3 0 0 1 -3 3a3 3 0 0 1 -3 -3c0 -1.657 1.343 -3 3 -3z" />
                                <path d="M12 12v9" />
                                <path d="M8 17l4 4l4 -4" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 block">
                                Saran Sistem Berdasarkan Tanggal ({{ $tanggalHariIni->locale('id')->isoFormat('D MMMM YYYY') }})
                            </span>
                            <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                Semester {{ $saran['semester'] }} {{ $saran['tahun_ajaran'] }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 block mt-0.5">
                                (Juli–Desember = Ganjil, Januari–Juni = Genap)
                            </span>
                        </div>
                    </div>

                    <button type="button"
                            @click="terapkanSaran()"
                            class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-700 hover:bg-blue-50 dark:hover:bg-blue-950/40 transition shadow-2xs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" />
                            <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" />
                        </svg>
                        Gunakan Saran
                    </button>
                </div>

                <!-- Form Ubah Periode -->
                <form id="form-update-periode" action="{{ route('admin.pengaturan-periode.update') }}" method="POST" class="mt-6 space-y-6">
                    @csrf

                    <!-- (c) Dua Dropdown: Tahun Ajaran dan Semester -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Dropdown Tahun Ajaran -->
                        <div>
                            <x-input-label for="tahun_ajaran" :value="__('Tahun Ajaran')" />
                            <div class="mt-1.5 relative">
                                <select id="tahun_ajaran"
                                        name="tahun_ajaran"
                                        x-model="tahunAjaran"
                                        required
                                        class="block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 shadow-xs text-sm py-2.5">
                                    @foreach($daftarTahunAjaran as $ta)
                                        <option value="{{ $ta }}">{{ $ta }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Format: YYYY/YYYY (contoh: 2026/2027)
                            </p>
                        </div>

                        <!-- Dropdown Semester -->
                        <div>
                            <x-input-label for="semester" :value="__('Semester')" />
                            <div class="mt-1.5 relative">
                                <select id="semester"
                                        name="semester"
                                        x-model="semester"
                                        required
                                        class="block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 shadow-xs text-sm py-2.5">
                                    <option value="Ganjil">Semester Ganjil</option>
                                    <option value="Genap">Semester Genap</option>
                                </select>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Pilihan: Ganjil atau Genap
                            </p>
                        </div>
                    </div>

                    <!-- (e) Tombol Simpan yang Membuka Jendela Konfirmasi Alpine.js -->
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-end gap-3">
                        <button type="button"
                                @click="showConfirmModal = true"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 dark:bg-blue-700 hover:bg-blue-700 dark:hover:bg-blue-600 text-white font-semibold rounded-xl text-sm transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                <path d="M14 4l0 4l-6 0l0 -4" />
                            </svg>
                            Simpan Perubahan Periode
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- (e) Jendela Modal Konfirmasi (Alpine.js) -->
        <div x-show="showConfirmModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title"
             role="dialog"
             aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <!-- Backdrop -->
                <div x-show="showConfirmModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="showConfirmModal = false"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Panel -->
                <div x-show="showConfirmModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl px-6 pt-5 pb-6 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700">
                    
                    <div class="sm:flex sm:items-start gap-4">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 sm:mx-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M12 9v4" />
                                <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.875h16.214a1.914 1.914 0 0 0 1.636 -2.875l-8.106 -13.534a1.914 1.914 0 0 0 -3.274 0z" />
                                <path d="M12 16h.01" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:text-left">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100" id="modal-title">
                                Konfirmasi Pergantian Periode
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                    Ganti periode aktif ke <strong class="text-gray-900 dark:text-white">Semester <span x-text="semester"></span> <span x-text="tahunAjaran"></span></strong>?
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700">
                                    Perubahan ini akan langsung mempengaruhi jadwal pelajaran harian yang ditampilkan pada dashboard seluruh guru dan siswa.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 sm:flex sm:flex-row-reverse gap-3">
                        <button type="button"
                                @click="document.getElementById('form-update-periode').submit()"
                                class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-xs px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto transition">
                            Ya, Ganti Periode
                        </button>
                        <button type="button"
                                @click="showConfirmModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 dark:border-gray-600 shadow-2xs px-4 py-2.5 bg-white dark:bg-gray-700 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto transition">
                            Batal
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
