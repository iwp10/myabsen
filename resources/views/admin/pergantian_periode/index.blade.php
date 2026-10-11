<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pergantian Periode') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8" x-data="{
        activeTab: '{{ in_array($tab, ['panduan', 'salin', 'pindah', 'lulus']) ? $tab : 'salin' }}',
        init() {
            // Penanda alamat URL hash: #salin, #pindah, #lulus, #panduan
            const allowedTabs = ['#salin', '#pindah', '#lulus', '#panduan'];
            const hash = window.location.hash.toLowerCase();
            if (allowedTabs.includes(hash)) {
                const target = hash.replace('#', '');
                this.activeTab = target;
                this.$nextTick(() => {
                    this.scrollToTab(target, false);
                });
            }
            window.addEventListener('hashchange', () => {
                const currentHash = window.location.hash.toLowerCase();
                if (allowedTabs.includes(currentHash)) {
                    const target = currentHash.replace('#', '');
                    this.activeTab = target;
                    this.scrollToTab(target, false);
                }
            });
        },
        switchTab(tab, shouldScroll = true) {
            this.activeTab = tab;
            if (window.location.hash !== '#' + tab) {
                history.pushState(null, '', '#' + tab);
            }
            if (shouldScroll) {
                this.$nextTick(() => {
                    this.scrollToTab(tab, true);
                });
            }
        },
        scrollToTab(tab, focusTitle = true) {
            const targetRef = this.$refs[tab + 'Panel'];
            if (targetRef) {
                const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                targetRef.scrollIntoView({
                    behavior: prefersReduced ? 'auto' : 'smooth',
                    block: 'start'
                });
                if (focusTitle) {
                    const heading = targetRef.querySelector('h3, h1') || targetRef;
                    if (heading && heading.focus) {
                        heading.focus();
                    }
                }
            }
        },
        scrollToTabs() {
            if (this.$refs.tabBar) {
                const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                this.$refs.tabBar.scrollIntoView({
                    behavior: prefersReduced ? 'auto' : 'smooth',
                    block: 'start'
                });
            }
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Banner Notifikasi / Flash Messages -->
            @if (session('success'))
                <x-alert type="success" :autoDismiss="true">
                    {{ session('success') }}
                </x-alert>
            @endif

            @if (session('error'))
                <x-alert type="error">
                    {{ session('error') }}
                </x-alert>
            @endif

            @if ($errors->any())
                <x-alert type="error">
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-alert>
            @endif

            <!-- 0. KARTU PANDUAN KERJA & STATUS PERIODE AKTIF -->
            <div x-ref="panduanPanel" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 scroll-mt-24">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="9" />
                                <line x1="12" y1="8" x2="12.01" y2="8" />
                                <polyline points="11 12 12 12 12 16 13 16" />
                            </svg>
                            <span>Panduan Alur Administrasi</span>
                        </div>
                        <h1 tabindex="-1" class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 outline-hidden">
                            Pusat Pergantian Periode & Tahun Ajaran
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Kelola transisi kenaikan kelas, mutasi siswa, dan penyiapan struktur kelas periode baru secara teratur.
                        </p>
                    </div>

                    <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-900/60 rounded-xl border border-gray-200 dark:border-gray-700 self-start md:self-auto shrink-0">
                        <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <rect x="4" y="5" width="16" height="16" rx="2" />
                                <line x1="16" y1="3" x2="16" y2="7" />
                                <line x1="8" y1="3" x2="8" y2="7" />
                                <line x1="4" y1="11" x2="20" y2="11" />
                            </svg>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 font-medium">Periode Aktif Saat Ini</span>
                            <span class="font-bold text-sm text-gray-900 dark:text-gray-100">
                                Semester {{ $activePeriode['semester'] ?? 'Ganjil' }} {{ $activePeriode['tahun_ajaran'] ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 5 Langkah Kerja Berurutan -->
                <div class="mt-5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">
                        Urutan Kerja yang Disarankan:
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                        <div class="p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 flex flex-col justify-between">
                            <div>
                                <span class="inline-flex items-center justify-center size-6 rounded-full bg-blue-600 text-white text-xs font-bold mb-2">1</span>
                                <h4 class="font-semibold text-sm text-gray-900 dark:text-gray-100">Salin Kelas</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Salin struktur kelas ke semester atau tahun ajaran baru.</p>
                            </div>
                            <button type="button" @click="switchTab('salin')" class="mt-3 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline text-left">
                                Buka Tab Salin &rarr;
                            </button>
                        </div>

                        <div class="p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 flex flex-col justify-between">
                            <div>
                                <span class="inline-flex items-center justify-center size-6 rounded-full bg-blue-600 text-white text-xs font-bold mb-2">2</span>
                                <h4 class="font-semibold text-sm text-gray-900 dark:text-gray-100">Buat Jadwal Baru</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Susun jadwal mata pelajaran pada kelas-kelas periode baru.</p>
                            </div>
                            <a href="{{ route('admin.jadwal.index') }}" class="mt-3 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1">
                                <span>Menu Jadwal</span>
                                <x-ui.icon name="arrow-right" size="sm" class="text-blue-600 dark:text-blue-400" />
                            </a>
                        </div>

                        <div class="p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 flex flex-col justify-between">
                            <div>
                                <span class="inline-flex items-center justify-center size-6 rounded-full bg-blue-600 text-white text-xs font-bold mb-2">3</span>
                                <h4 class="font-semibold text-sm text-gray-900 dark:text-gray-100">Pindahkan Siswa</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Naikkan atau mutasikan siswa dari kelas lama ke kelas baru.</p>
                            </div>
                            <button type="button" @click="switchTab('pindah')" class="mt-3 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline text-left">
                                Buka Tab Pindah &rarr;
                            </button>
                        </div>

                        <div class="p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 flex flex-col justify-between">
                            <div>
                                <span class="inline-flex items-center justify-center size-6 rounded-full bg-blue-600 text-white text-xs font-bold mb-2">4</span>
                                <h4 class="font-semibold text-sm text-gray-900 dark:text-gray-100">Luluskan Siswa</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Luluskan (nonaktifkan) siswa kelas akhir yang telah tamat.</p>
                            </div>
                            <button type="button" @click="switchTab('lulus')" class="mt-3 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline text-left">
                                Buka Tab Luluskan &rarr;
                            </button>
                        </div>

                        <div class="p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 flex flex-col justify-between">
                            <div>
                                <span class="inline-flex items-center justify-center size-6 rounded-full bg-blue-600 text-white text-xs font-bold mb-2">5</span>
                                <h4 class="font-semibold text-sm text-gray-900 dark:text-gray-100">Ganti Periode Aktif</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Aktifkan periode baru sebagai acuan absensi utama sekolah.</p>
                            </div>
                            <a href="{{ route('admin.pengaturan-periode.index') }}" class="mt-3 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1">
                                <span>Pengaturan Periode</span>
                                <x-ui.icon name="arrow-right" size="sm" class="text-blue-600 dark:text-blue-400" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NAVIGASI TAB UTAMA -->
            <div x-ref="tabBar" class="border-b border-gray-200 dark:border-gray-700 scroll-mt-20">
                <nav class="-mb-px flex space-x-2 sm:space-x-6 overflow-x-auto snap-x snap-mandatory py-1" aria-label="Tabs">
                    <button type="button"
                            @click="switchTab('salin')"
                            :class="activeTab === 'salin' ? 'border-blue-600 text-blue-600 bg-blue-50/60 dark:bg-blue-950/40 dark:border-blue-400 dark:text-blue-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 font-medium'"
                            class="whitespace-nowrap px-3 sm:px-4 py-3 border-b-4 text-sm flex items-center gap-2 min-h-[44px] min-w-[44px] rounded-t-lg transition snap-start flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="8" y="8" width="12" height="12" rx="2" />
                            <path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2" />
                        </svg>
                        <span>1. Salin Kelas</span>
                    </button>

                    <button type="button"
                            @click="switchTab('pindah')"
                            :class="activeTab === 'pindah' ? 'border-blue-600 text-blue-600 bg-blue-50/60 dark:bg-blue-950/40 dark:border-blue-400 dark:text-blue-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 font-medium'"
                            class="whitespace-nowrap px-3 sm:px-4 py-3 border-b-4 text-sm flex items-center gap-2 min-h-[44px] min-w-[44px] rounded-t-lg transition snap-start flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <circle cx="9" cy="7" r="4" />
                            <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                            <path d="M16 11l2 2l4 -4" />
                        </svg>
                        <span>2. Pindahkan Siswa</span>
                    </button>

                    <button type="button"
                            @click="switchTab('lulus')"
                            :class="activeTab === 'lulus' ? 'border-blue-600 text-blue-600 bg-blue-50/60 dark:bg-blue-950/40 dark:border-blue-400 dark:text-blue-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 font-medium'"
                            class="whitespace-nowrap px-3 sm:px-4 py-3 border-b-4 text-sm flex items-center gap-2 min-h-[44px] min-w-[44px] rounded-t-lg transition snap-start flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M22 9l-10 -4l-10 4l10 4l10 -4v6" />
                            <path d="M6 10.6v5.4a6 3 0 0 0 12 0v-5.4" />
                        </svg>
                        <span>3. Luluskan Siswa</span>
                    </button>
                </nav>
            </div>

            <!-- ========================================== -->
            <!-- TAB 1: SALIN KELAS                        -->
            <!-- ========================================== -->
            <div x-show="activeTab === 'salin'" x-ref="salinPanel" class="space-y-6 scroll-mt-24" x-cloak>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="mb-5">
                        <h3 tabindex="-1" class="text-lg font-bold text-gray-900 dark:text-gray-100 outline-hidden">Salin Struktur Kelas ke Periode Baru</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Salin nama, tingkat, dan jurusan kelas ke periode tujuan. Kelas yang sudah ada di periode tujuan akan otomatis dilewati. Jadwal dan siswa tidak ikut disalin.
                        </p>
                    </div>

                    <!-- Filter Pemilihan Periode Asal -->
                    <form method="GET" action="{{ route('admin.pergantian-periode.index') }}" class="mb-6 p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700">
                        <input type="hidden" name="tab" value="salin">
                        <div class="max-w-md">
                            <x-input-label for="filter_periode_asal" :value="__('Pilih Periode Asal (Sumber Kelas)')" />
                            <x-ui.select id="filter_periode_asal"
                                         name="periode_asal"
                                         onchange="this.form.submit()"
                                         wrapper-class="mt-1">
                                @forelse($daftarPeriodeAsal as $p)
                                    <option value="{{ $p['value'] }}" {{ $periodeAsal === $p['value'] ? 'selected' : '' }}>
                                        {{ $p['label'] }}
                                    </option>
                                @empty
                                    <option value="">Belum ada kelas aktif di database</option>
                                @endforelse
                            </x-ui.select>
                        </div>
                    </form>

                    <!-- Form Salin Kelas -->
                    @if($kelasPeriodeAsal->isNotEmpty())
                        <div x-data="pilihanMassal({{ json_encode($kelasPeriodeAsal->pluck('id')->map(fn($id) => (string)$id)->values()->all()) }}, {{ json_encode($kelasPeriodeAsal->pluck('id')->map(fn($id) => (string)$id)->values()->all()) }}, { confirmModal: false })">
                            <form id="formSalinKelas" method="POST" action="{{ route('admin.pergantian-periode.salin-kelas') }}" class="space-y-6">
                                @csrf
                                <input type="hidden" name="periode_asal" value="{{ $periodeAsal }}">

                                <!-- Pengaturan Periode Tujuan -->
                                <div class="p-4 rounded-xl bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-800">
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M13 18l6 -6"/><path d="M13 6l6 6"/></svg>
                                        <span>Tentukan Periode Tujuan</span>
                                    </h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-xl">
                                        <div>
                                            <x-input-label for="tahun_ajaran_tujuan" :value="__('Tahun Ajaran Tujuan')" />
                                            <x-ui.select id="tahun_ajaran_tujuan"
                                                         name="tahun_ajaran_tujuan"
                                                         required
                                                         wrapper-class="mt-1">
                                                @foreach($daftarTahunAjaran as $thn)
                                                    <option value="{{ $thn }}">{{ $thn }}</option>
                                                @endforeach
                                            </x-ui.select>
                                        </div>

                                        <div>
                                            <x-input-label for="semester_tujuan" :value="__('Semester Tujuan')" />
                                            <x-ui.select id="semester_tujuan"
                                                         name="semester_tujuan"
                                                         required
                                                         wrapper-class="mt-1">
                                                <option value="Ganjil">Ganjil</option>
                                                <option value="Genap">Genap</option>
                                            </x-ui.select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tabel Daftar Kelas Asal -->
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                            Daftar Kelas di Periode Asal ({{ $kelasPeriodeAsal->count() }} kelas)
                                        </h4>
                                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">
                                            <span x-text="terpilih.length"></span> dari {{ $kelasPeriodeAsal->count() }} kelas dipilih
                                        </span>
                                    </div>

                                    <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-xl">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                                <tr>
                                                    <th scope="col" class="px-4 py-3 text-left w-12">
                                                        <x-ui.pilih-semua aria-label="Pilih semua kelas di periode asal" />
                                                    </th>
                                                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Nama Kelas</th>
                                                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Tingkat</th>
                                                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Jurusan</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                                @foreach($kelasPeriodeAsal as $k)
                                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                                        <td class="px-4 py-3">
                                                            <x-ui.pilih-baris :id="$k->id" name="kelas_ids[]" :aria-label="'Pilih kelas ' . $k->nama" />
                                                        </td>
                                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $k->nama }}</td>
                                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">Kelas {{ $k->tingkat }}</td>
                                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $k->jurusan?->nama ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-3 pt-3">
                                    <button type="button"
                                            @click="if (terpilih.length > 0) { confirmModal = true } else { alert('Pilih minimal satu kelas untuk disalin.') }"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Salin Kelas Terpilih (<span x-text="terpilih.length"></span>)
                                    </button>
                                </div>

                                <!-- Modal Konfirmasi Salin Kelas -->
                                <div x-show="confirmModal"
                                     x-cloak
                                     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs">
                                    <div @click.away="confirmModal = false"
                                         class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-200 dark:border-gray-700">
                                        <div class="flex items-start gap-4">
                                            <div class="p-2.5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 8m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z"/><path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2"/></svg>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Konfirmasi Salin Kelas</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                                    Apakah Anda yakin ingin menyalin <span class="font-bold text-gray-900 dark:text-gray-100" x-text="terpilih.length"></span> kelas terpilih ke periode tujuan?
                                                </p>
                                                <p class="text-xs text-amber-600 dark:text-amber-400 mt-2">
                                                    Catatan: Kelas yang sudah ada di periode tujuan akan otomatis dilewati. Jadwal dan siswa tidak ikut disalin.
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mt-6 flex justify-end gap-3">
                                            <x-secondary-button type="button" @click="confirmModal = false">Batal</x-secondary-button>
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                                Ya, Salin Kelas
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                            Tidak ada kelas aktif pada periode asal yang dipilih.
                        </div>
                    @endif

                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                        <button type="button" @click="scrollToTabs()" class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 transition py-2 px-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 15l-6-6-6 6"/></svg>
                            <span>Kembali ke daftar langkah</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 2: PINDAHKAN SISWA                    -->
            <!-- ========================================== -->
            <div x-show="activeTab === 'pindah'" x-ref="pindahPanel" class="space-y-6 scroll-mt-24" x-cloak>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="mb-5">
                        <h3 tabindex="-1" class="text-lg font-bold text-gray-900 dark:text-gray-100 outline-hidden">Pindahkan Siswa ke Kelas Baru</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Pindahkan siswa aktif dari kelas asal ke kelas tujuan (misal kenaikan tingkat atau mutasi rombel). Riwayat absensi sesi lama tetap aman tersimpan di kelas asalnya.
                        </p>
                    </div>

                    <!-- Form Pemilihan Kelas Asal & Tujuan -->
                    <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 mb-6">
                        <form method="GET" action="{{ route('admin.pergantian-periode.index') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input type="hidden" name="tab" value="pindah">

                            <div>
                                <x-input-label for="select_kelas_asal" :value="__('Kelas Asal (Sumber Siswa)')" />
                                <x-ui.select id="select_kelas_asal"
                                             name="kelas_asal_id"
                                             onchange="this.form.submit()"
                                             wrapper-class="mt-1">
                                    <option value="">-- Pilih Kelas Asal --</option>
                                    @foreach($daftarKelasAktif as $ka)
                                        <option value="{{ $ka->id }}" {{ (string)$kelasAsalId === (string)$ka->id ? 'selected' : '' }}>
                                            {{ $ka->nama }} ({{ $ka->tahun_ajaran }} - {{ $ka->semester }})
                                        </option>
                                    @endforeach
                                </x-ui.select>
                            </div>

                            <div>
                                <x-input-label for="select_kelas_tujuan_filter" :value="__('Kelas Tujuan')" />
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                    Kelas tujuan dipilih pada form pemindahan di bawah setelah menentukan siswa yang akan dipindah.
                                </p>
                            </div>
                        </form>
                    </div>

                    <!-- Tabel Siswa Kelas Asal -->
                    @if($kelasAsalId && $siswaKelasAsal->isNotEmpty())
                        @php
                            $namaKelasAsal = $daftarKelasAktif->firstWhere('id', (int)$kelasAsalId)?->nama ?? 'Kelas Asal';
                        @endphp
                        <div x-data="pilihanMassal(
                            {{ json_encode($siswaKelasAsal->pluck('id')->map(fn($id) => (string)$id)->values()->all()) }},
                            {{ json_encode($siswaKelasAsal->pluck('id')->map(fn($id) => (string)$id)->values()->all()) }},
                            {
                                allSiswa: {{ json_encode($siswaKelasAsal->map(fn($s) => ['id' => (string)$s->id, 'nis' => $s->nis, 'name' => $s->user?->name ?? ''])->values()->all()) }},
                                search: '',
                                kelasTujuanNama: '',
                                confirmModal: false,
                                get filteredSiswa() {
                                    if (!this.search.trim()) return this.allSiswa;
                                    const q = this.search.toLowerCase();
                                    return this.allSiswa.filter(s => s.nis.toLowerCase().includes(q) || s.name.toLowerCase().includes(q));
                                },
                                updateTujuanName(e) {
                                    const sel = e.target;
                                    this.kelasTujuanNama = sel.options[sel.selectedIndex]?.text || '';
                                },
                                init() {
                                    this.$watch('search', () => {
                                        this.setTersedia(this.filteredSiswa.map(s => s.id));
                                    });
                                }
                            }
                        )">
                            <form id="formPindahkanSiswa" method="POST" action="{{ route('admin.pergantian-periode.pindahkan-siswa') }}" class="space-y-6">
                                @csrf
                                <input type="hidden" name="kelas_asal_id" value="{{ $kelasAsalId }}">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end p-4 bg-blue-50/40 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-800 rounded-xl">
                                    <div>
                                        <x-input-label for="kelas_tujuan_id" :value="__('Pilih Kelas Tujuan')" />
                                        <x-ui.select id="kelas_tujuan_id"
                                                     name="kelas_tujuan_id"
                                                     required
                                                     @change="updateTujuanName($event)"
                                                     wrapper-class="mt-1">
                                            <option value="">-- Pilih Kelas Tujuan --</option>
                                            @foreach($daftarKelasAktif as $kt)
                                                @if((int)$kt->id !== (int)$kelasAsalId)
                                                    <option value="{{ $kt->id }}">
                                                        {{ $kt->nama }} ({{ $kt->tahun_ajaran }} - {{ $kt->semester }})
                                                    </option>
                                                @endif
                                            @endforeach
                                        </x-ui.select>
                                    </div>

                                    <div>
                                        <x-input-label for="search_siswa" :value="__('Cari Siswa di Tampilan (Nama / NIS)')" />
                                        <div class="relative mt-1">
                                            <input type="text"
                                                   id="search_siswa"
                                                   x-model="search"
                                                   placeholder="Ketik nama atau NIS siswa..."
                                                   class="block w-full pl-9 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 rounded-md shadow-sm text-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="10" cy="10" r="7"/><line x1="21" y1="21" x2="15" y2="15"/></svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                                Siswa Aktif Kelas {{ $namaKelasAsal }} ({{ $siswaKelasAsal->count() }} siswa)
                                            </h4>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                Pilih semua yang terlihat (<span x-text="idTersedia.length"></span>)
                                            </span>
                                        </div>
                                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">
                                            <span x-text="terpilih.length"></span> siswa terpilih
                                        </span>
                                    </div>

                                    <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-xl">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                                <tr>
                                                    <th scope="col" class="px-4 py-3 text-left w-12">
                                                        <x-ui.pilih-semua aria-label="Pilih semua siswa di kelas asal" />
                                                    </th>
                                                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">NIS</th>
                                                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Nama Siswa</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                                <template x-for="item in filteredSiswa" :key="item.id">
                                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                                        <td class="px-4 py-3">
                                                            <x-ui.pilih-baris alpine-id="item.id" name="siswa_ids[]" :alpine-aria-label="'\'Pilih siswa \' + item.name'" />
                                                        </td>
                                                        <td class="px-4 py-3 font-mono text-gray-600 dark:text-gray-400" x-text="item.nis"></td>
                                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100" x-text="item.name"></td>
                                                    </tr>
                                                </template>
                                                <tr x-show="filteredSiswa.length === 0">
                                                    <td colspan="3" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                                        Tidak ada siswa yang cocok dengan pencarian.
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-3 pt-3">
                                    <button type="button"
                                            @click="
                                                const tujuanSel = document.getElementById('kelas_tujuan_id');
                                                if (!tujuanSel.value) {
                                                    alert('Silakan pilih kelas tujuan terlebih dahulu.');
                                                    return;
                                                }
                                                if (terpilih.length === 0) {
                                                    alert('Pilih minimal satu siswa untuk dipindahkan.');
                                                    return;
                                                }
                                                confirmModal = true;
                                            "
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Pindahkan Siswa Terpilih (<span x-text="terpilih.length"></span>)
                                    </button>
                                </div>

                                <!-- Modal Konfirmasi Pindahkan Siswa -->
                                <div x-show="confirmModal"
                                     x-cloak
                                     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs">
                                    <div @click.away="confirmModal = false"
                                         class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-200 dark:border-gray-700">
                                        <div class="flex items-start gap-4">
                                            <div class="p-2.5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9l-6 6"/><path d="M8 9l6 6"/></svg>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Konfirmasi Pemindahan Siswa</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                                    Pindahkan <span class="font-bold text-gray-900 dark:text-gray-100" x-text="terpilih.length"></span> siswa ke <span class="font-bold text-blue-600 dark:text-blue-400" x-text="kelasTujuanNama || 'kelas tujuan'"></span>?
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                                    Hanya kelas aktif siswa yang diperbarui. Detail kehadiran dan sesi absensi historis di kelas sebelumnya tetap aman.
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mt-6 flex justify-end gap-3">
                                            <x-secondary-button type="button" @click="confirmModal = false">Batal</x-secondary-button>
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                                Ya, Pindahkan Siswa
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @elseif($kelasAsalId)
                        <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                            Kelas ini tidak memiliki siswa aktif.
                        </div>
                    @else
                        <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                            Silakan pilih Kelas Asal pada dropdown di atas untuk memuat daftar siswa.
                        </div>
                    @endif

                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                        <button type="button" @click="scrollToTabs()" class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 transition py-2 px-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 15l-6-6-6 6"/></svg>
                            <span>Kembali ke daftar langkah</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 3: LULUSKAN SISWA                     -->
            <!-- ========================================== -->
            <div x-show="activeTab === 'lulus'" x-ref="lulusPanel" class="space-y-6 scroll-mt-24" x-cloak>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="mb-5">
                        <h3 tabindex="-1" class="text-lg font-bold text-gray-900 dark:text-gray-100 outline-hidden">Luluskan Siswa Kelas Akhir</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Luluskan siswa yang telah tamat belajar. Siswa akan di-nonaktifkan dengan aman (soft delete); data historis absensi tetap utuh dan siswa dapat dipulihkan sewaktu-waktu melalui menu Data Terhapus.
                        </p>
                    </div>

                    <!-- Filter Pemilihan Kelas -->
                    <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 mb-6">
                        <form method="GET" action="{{ route('admin.pergantian-periode.index') }}" class="max-w-md">
                            <input type="hidden" name="tab" value="lulus">

                            <x-input-label for="select_kelas_lulus" :value="__('Pilih Kelas Siswa yang Lulus')" />
                            <x-ui.select id="select_kelas_lulus"
                                         name="kelas_id"
                                         onchange="this.form.submit()"
                                         wrapper-class="mt-1">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($daftarKelasAktif as $kl)
                                    <option value="{{ $kl->id }}" {{ (string)$kelasLulusId === (string)$kl->id ? 'selected' : '' }}>
                                        {{ $kl->nama }} (Tingkat {{ $kl->tingkat }} &bull; {{ $kl->tahun_ajaran }})
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </form>
                    </div>

                    <!-- Tabel Siswa yang Akan Diluluskan -->
                    @if($kelasLulusId && $siswaLulus->isNotEmpty())
                        @php
                            $namaKelasLulus = $daftarKelasAktif->firstWhere('id', (int)$kelasLulusId)?->nama ?? 'Kelas';
                        @endphp
                        <div x-data="pilihanMassal(
                            {{ json_encode($siswaLulus->pluck('id')->map(fn($id) => (string)$id)->values()->all()) }},
                            {{ json_encode($siswaLulus->pluck('id')->map(fn($id) => (string)$id)->values()->all()) }},
                            {
                                allSiswa: {{ json_encode($siswaLulus->map(fn($s) => ['id' => (string)$s->id, 'nis' => $s->nis, 'name' => $s->user?->name ?? ''])->values()->all()) }},
                                search: '',
                                confirmModal: false,
                                get filteredSiswa() {
                                    if (!this.search.trim()) return this.allSiswa;
                                    const q = this.search.toLowerCase();
                                    return this.allSiswa.filter(s => s.nis.toLowerCase().includes(q) || s.name.toLowerCase().includes(q));
                                },
                                init() {
                                    this.$watch('search', () => {
                                        this.setTersedia(this.filteredSiswa.map(s => s.id));
                                    });
                                }
                            }
                        )">
                            <form id="formLuluskanSiswa" method="POST" action="{{ route('admin.pergantian-periode.luluskan-siswa') }}" class="space-y-6">
                                @csrf
                                <input type="hidden" name="kelas_id" value="{{ $kelasLulusId }}">

                                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                            Siswa Aktif Kelas {{ $namaKelasLulus }} ({{ $siswaLulus->count() }} siswa)
                                        </h4>
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                Pilih semua yang terlihat (<span x-text="idTersedia.length"></span>)
                                            </span>
                                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">
                                                <span x-text="terpilih.length"></span> siswa terpilih untuk diluluskan
                                            </span>
                                        </div>
                                    </div>

                                    <div class="w-full sm:w-72 relative">
                                        <input type="text"
                                               x-model="search"
                                               placeholder="Cari nama atau NIS siswa..."
                                               class="block w-full pl-9 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-red-500 dark:focus:border-red-600 focus:ring-red-500 dark:focus:ring-red-600 rounded-md shadow-sm text-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="10" cy="10" r="7"/><line x1="21" y1="21" x2="15" y2="15"/></svg>
                                        </div>
                                    </div>
                                </div>

                                <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-xl">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                                            <tr>
                                                <th scope="col" class="px-4 py-3 text-left w-12">
                                                    <x-ui.pilih-semua color="red" aria-label="Pilih semua siswa yang akan diluluskan" />
                                                </th>
                                                <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">NIS</th>
                                                <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Nama Siswa</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                            <template x-for="item in filteredSiswa" :key="item.id">
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                                    <td class="px-4 py-3">
                                                        <x-ui.pilih-baris alpine-id="item.id" name="siswa_ids[]" color="red" :alpine-aria-label="'\'Pilih siswa \' + item.name'" />
                                                    </td>
                                                    <td class="px-4 py-3 font-mono text-gray-600 dark:text-gray-400" x-text="item.nis"></td>
                                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100" x-text="item.name"></td>
                                                </tr>
                                            </template>
                                            <tr x-show="filteredSiswa.length === 0">
                                                <td colspan="3" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                                    Tidak ada siswa yang cocok dengan pencarian.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="flex items-center justify-end gap-3 pt-3">
                                    <button type="button"
                                            @click="if (terpilih.length > 0) { confirmModal = true } else { alert('Pilih minimal satu siswa untuk diluluskan.') }"
                                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Luluskan Siswa Terpilih (<span x-text="terpilih.length"></span>)
                                    </button>
                                </div>

                                <!-- Modal Konfirmasi Luluskan Siswa -->
                                <div x-show="confirmModal"
                                     x-cloak
                                     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs">
                                    <div @click.away="confirmModal = false"
                                         class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-200 dark:border-gray-700">
                                        <div class="flex items-start gap-4">
                                            <div class="p-2.5 rounded-full bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400 shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4v.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Konfirmasi Kelulusan Siswa</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                                    Apakah Anda yakin ingin meluluskan <span class="font-bold text-red-600 dark:text-red-400" x-text="terpilih.length"></span> siswa terpilih dari kelas {{ $namaKelasLulus }}?
                                                </p>
                                                <div class="mt-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400 space-y-1">
                                                    <p>&bull; Akun mereka tidak bisa login lagi.</p>
                                                    <p>&bull; Namanya tetap ada di riwayat dengan tanda <strong>(nonaktif)</strong>.</p>
                                                    <p>&bull; Dapat dipulihkan sewaktu-waktu lewat menu <strong>Data Terhapus</strong>.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-6 flex justify-end gap-3">
                                            <x-secondary-button type="button" @click="confirmModal = false">Batal</x-secondary-button>
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                                Ya, Luluskan Siswa
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @elseif($kelasLulusId)
                        <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                            Kelas ini tidak memiliki siswa aktif untuk diluluskan.
                        </div>
                    @else
                        <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                            Silakan pilih Kelas pada dropdown di atas untuk memuat daftar siswa yang akan diluluskan.
                        </div>
                    @endif

                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                        <button type="button" @click="scrollToTabs()" class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 transition py-2 px-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 15l-6-6-6 6"/></svg>
                            <span>Kembali ke daftar langkah</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
