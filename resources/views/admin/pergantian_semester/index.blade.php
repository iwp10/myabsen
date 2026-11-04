<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Pergantian Semester Otomatis') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8" x-data="{ modalKonfirmasi: false, submitting: false, modalAktifkan: false, submittingAktifkan: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Tombol Navigasi Kembali --}}
            <div>
                <x-ui.back-button :href="route('admin.pergantian-periode.index')" label="Kembali ke Pergantian Periode" />
            </div>

            <x-ui.page-header
                title="Pergantian Semester Otomatis"
                description="Alur terpadu untuk menyalin kelas, memindahkan siswa aktif, dan menyalin jadwal pelajaran dari semester Ganjil ke Genap dalam tahun ajaran yang sama." />

            {{-- Banner Notifikasi --}}
            @if (session('success'))
                <x-alert type="success" :autoDismiss="false">
                    {{ session('success') }}
                </x-alert>
            @endif

            @if (session('warning'))
                <x-alert type="warning">
                    {{ session('warning') }}
                </x-alert>
            @endif

            @if (session('error'))
                <x-alert type="error">
                    {{ session('error') }}
                </x-alert>
            @endif

            @if ($errorPesan)
                <x-alert type="error">
                    {{ $errorPesan }}
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

            {{-- 1. HASIL EKSEKUSI (Bila Baru Saja Dijalankan) --}}
            @if (session('hasil_eksekusi'))
                @php $hasil = session('hasil_eksekusi'); @endphp
                <x-ui.card class="border-2 border-emerald-400 dark:border-emerald-700 bg-emerald-50/40 dark:bg-emerald-950/20">
                    <div class="flex items-start gap-3">
                        <div class="p-2 rounded-xl bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300 shrink-0">
                            <x-ui.icon name="check" size="md" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                                Hasil Eksekusi Pergantian Semester (Genap {{ $hasil['tahun_ajaran'] }})
                            </h3>
                            <p class="text-sm text-slate-700 dark:text-slate-300 mt-1">
                                Proses pergantian semester telah selesai dijalankan. Berikut adalah ringkasan hasil pemrosesan:
                            </p>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4">
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <span class="block text-xs font-semibold text-slate-600 dark:text-slate-400">Kelas Berhasil</span>
                                    <span class="text-xl font-bold text-emerald-700 dark:text-emerald-400">{{ $hasil['kelas_berhasil'] }}</span>
                                </div>
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <span class="block text-xs font-semibold text-slate-600 dark:text-slate-400">Siswa Dipindahkan</span>
                                    <span class="text-xl font-bold text-blue-700 dark:text-blue-400">{{ $hasil['siswa_dipindah'] }}</span>
                                </div>
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <span class="block text-xs font-semibold text-slate-600 dark:text-slate-400">Jadwal Disalin</span>
                                    <span class="text-xl font-bold text-teal-700 dark:text-teal-400">{{ $hasil['jadwal_disalin'] }}</span>
                                </div>
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <span class="block text-xs font-semibold text-slate-600 dark:text-slate-400">Jadwal Dilewati</span>
                                    <span class="text-xl font-bold text-amber-700 dark:text-amber-400">{{ $hasil['jadwal_dilewati'] }}</span>
                                </div>
                            </div>

                            @if ($hasil['kelas_gagal'] > 0)
                                <div class="mt-4 p-3 bg-red-50 dark:bg-red-950/40 border border-red-300 dark:border-red-700 rounded-xl text-xs text-red-800 dark:text-red-300">
                                    <span class="font-bold">Perhatian:</span> Terdapat {{ $hasil['kelas_gagal'] }} kelas yang mengalami kendala saat eksekusi. Periksa rincian di bawah atau jalankan ulang setelah memperbaiki penyebab kendala.
                                </div>
                            @endif

                            {{-- Aksi Lanjutan: Aktifkan Periode --}}
                            <div class="mt-5 pt-4 border-t border-emerald-200 dark:border-emerald-800/60 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs text-slate-700 dark:text-slate-300 font-medium">
                                        Langkah berikutnya: Aktifkan semester Genap {{ $hasil['tahun_ajaran'] }} agar sistem mulai menggunakan jadwal baru.
                                    </p>
                                </div>
                                <button type="button" @click="modalAktifkan = true" class="{{ \App\Support\Ui::tombol('primary') }} shrink-0">
                                    <x-ui.icon name="calendar-check" size="sm" class="mr-1.5" />
                                    <span>Aktifkan Semester Genap {{ $hasil['tahun_ajaran'] }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            @endif

            {{-- 2. KARTU PANDUAN BAHASA AWAM --}}
            <x-ui.card>
                <div class="flex items-start gap-3">
                    <div class="p-2.5 rounded-xl bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-300 shrink-0">
                        <x-ui.icon name="info" size="md" />
                    </div>
                    <div class="space-y-1.5 text-sm text-slate-700 dark:text-slate-300">
                        <h3 class="font-bold text-base text-slate-900 dark:text-white">
                            Panduan Pergantian Semester Ganjil &rarr; Genap
                        </h3>
                        <p>
                            Fitur ini khusus dirancang untuk mempercepat pergantian semester dari <strong>Semester Ganjil</strong> ke <strong>Semester Genap</strong> dalam tahun ajaran yang sama.
                        </p>
                        <p class="text-xs text-slate-600 dark:text-slate-400">
                            Untuk kenaikan tingkat siswa ke kelas yang lebih tinggi atau kelulusan siswa kelas XII, silakan gunakan tab reguler (Salin Kelas, Pindahkan Siswa, dan Luluskan Siswa) pada menu <strong>Pergantian Periode</strong>.
                        </p>
                    </div>
                </div>
            </x-ui.card>

            {{-- 3. FORMULIR PEMILIHAN PERIODE & OPSI (GET) --}}
            <x-ui.card>
                <h3 class="font-bold text-base text-slate-900 dark:text-white mb-4">
                    1. Tentukan Periode Asal & Opsi Pergantian
                </h3>

                <form action="{{ route('admin.pergantian-semester.index') }}" method="GET" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Periode Asal --}}
                        <div>
                            <label for="tahun_ajaran" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Tahun Ajaran (Semester Asal: Ganjil)
                            </label>
                            <x-ui.select id="tahun_ajaran" name="tahun_ajaran" required>
                                @foreach($daftarTahunAjaran as $ta)
                                    <option value="{{ $ta }}" {{ $tahunAjaranTerpilih === $ta ? 'selected' : '' }}>
                                        {{ $ta }} · Semester Ganjil
                                    </option>
                                @endforeach
                            </x-ui.select>
                            <input type="hidden" name="semester_asal" value="Ganjil">
                        </div>

                        {{-- Periode Tujuan (Otomatis) --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Semester Tujuan (Dihitung Otomatis)
                            </label>
                            <div class="min-h-[44px] px-3.5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center justify-between">
                                <span>{{ $tahunAjaranTerpilih }} · Semester Genap</span>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-900 dark:bg-blue-500/20 dark:text-blue-300">
                                    Otomatis
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Pilihan Opsi Tindakan --}}
                    <div>
                        <span class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Pilih Langkah yang Ingin Dijalankan:
                        </span>

                        <div class="space-y-3">
                            {{-- Opsi 1: Salin Kelas --}}
                            <label class="flex items-start gap-3 p-3.5 rounded-xl border-2 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer transition min-h-[44px]">
                                <input type="checkbox" name="salin_kelas" value="1" {{ $opsi['salin_kelas'] ? 'checked' : '' }}
                                       class="size-5 rounded border-2 border-slate-400 text-emerald-600 focus:ring-emerald-500 mt-0.5">
                                <div class="min-w-0">
                                    <span class="block text-sm font-bold text-slate-900 dark:text-white">
                                        Salin Struktur Kelas
                                    </span>
                                    <span class="block text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                                        Membuat kelas baru pada semester Genap dengan nama, tingkat, dan jurusan yang persis sama. Jika kelas tujuan sudah ada, sistem akan langsung menggunakannya.
                                    </span>
                                </div>
                            </label>

                            {{-- Opsi 2: Pindahkan Siswa --}}
                            <label class="flex items-start gap-3 p-3.5 rounded-xl border-2 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer transition min-h-[44px]">
                                <input type="checkbox" name="pindah_siswa" value="1" {{ $opsi['pindah_siswa'] ? 'checked' : '' }}
                                       class="size-5 rounded border-2 border-slate-400 text-emerald-600 focus:ring-emerald-500 mt-0.5">
                                <div class="min-w-0">
                                    <span class="block text-sm font-bold text-slate-900 dark:text-white">
                                        Pindahkan Siswa Aktif
                                    </span>
                                    <span class="block text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                                        Memindahkan siswa aktif dari kelas Ganjil ke kelas Genap padanannya. Seluruh riwayat absensi lama tetap utuh dan tersimpan aman.
                                    </span>
                                </div>
                            </label>

                            {{-- Opsi 3: Salin Jadwal --}}
                            <label class="flex items-start gap-3 p-3.5 rounded-xl border-2 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer transition min-h-[44px]">
                                <input type="checkbox" name="salin_jadwal" value="1" {{ $opsi['salin_jadwal'] ? 'checked' : '' }}
                                       class="size-5 rounded border-2 border-slate-400 text-emerald-600 focus:ring-emerald-500 mt-0.5">
                                <div class="min-w-0">
                                    <span class="block text-sm font-bold text-slate-900 dark:text-white">
                                        Salin Jadwal Pembelajaran
                                    </span>
                                    <span class="block text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                                        Menyalin seluruh jadwal mengajar (mata pelajaran, guru, hari, jam) ke semester Genap dengan pemeriksaan pencegahan jadwal bentrok (AB-06).
                                    </span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-2">
                        <button type="submit" class="{{ \App\Support\Ui::tombol('primary') }} w-full sm:w-auto inline-flex items-center justify-center gap-2">
                            <x-ui.icon name="search" size="sm" />
                            <span>Lihat Pratinjau Rencana</span>
                        </button>
                    </div>
                </form>
            </x-ui.card>

            {{-- 4. PERINGATAN KOREKSI ABSENSI SEBELUM PINDAHKAN SISWA --}}
            <div class="p-4 bg-amber-50 dark:bg-amber-950/40 border-2 border-amber-300 dark:border-amber-700 rounded-2xl flex items-start gap-3 shadow-xs">
                <div class="p-1.5 rounded-lg bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300 shrink-0">
                    <x-ui.icon name="clock" size="md" />
                </div>
                <div class="text-xs sm:text-sm text-amber-900 dark:text-amber-200 space-y-1">
                    <p class="font-bold">
                        Peringatan Penting Mengenai Absensi:
                    </p>
                    <p>
                        Selesaikan seluruh pengisian dan koreksi absensi semester Ganjil <strong>SEBELUM</strong> memindahkan siswa ke semester Genap. Siswa yang sudah berpindah kelas tidak akan lagi tampil di form absensi kelas lama, meskipun catatan kehadiran historisnya tetap tercatat di laporan.
                    </p>
                </div>
            </div>

            {{-- 5. BAGIAN PRATINJAU RENCANA --}}
            @if ($pratinjau)
                @php $r = $pratinjau['ringkasan']; @endphp

                <x-ui.card>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b-2 border-slate-200 dark:border-slate-700">
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-900 dark:bg-blue-500/20 dark:text-blue-300 mb-1">
                                <x-ui.icon name="file-text" size="sm" />
                                <span>Pratinjau Aman (Read-Only)</span>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                                2. Ringkasan Rencana Pergantian Semester
                            </h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                                Pratinjau ini hanya menampilkan simulasi kalkulasi tanpa mengubah data apa pun di database.
                            </p>
                        </div>

                        {{-- Tombol Eksekusi --}}
                        @if (!$pratinjau['melebihi_batas'] && $r['total_kelas'] > 0)
                            <button type="button" @click="modalKonfirmasi = true" class="{{ \App\Support\Ui::tombol('primary') }} w-full sm:w-auto shrink-0 inline-flex items-center justify-center gap-2">
                                <x-ui.icon name="check" size="sm" />
                                <span>Jalankan Pergantian Semester</span>
                            </button>
                        @endif
                    </div>

                    {{-- Pesan Bila Melebihi Batas --}}
                    @if ($pratinjau['melebihi_batas'])
                        <div class="mt-4 p-4 bg-red-50 dark:bg-red-950/40 border-2 border-red-300 dark:border-red-700 rounded-xl text-xs sm:text-sm text-red-800 dark:text-red-300 font-semibold flex items-center gap-2">
                            <x-ui.icon name="x" size="md" class="shrink-0 text-red-600" />
                            <span>{{ $pratinjau['pesan_batas'] }}</span>
                        </div>
                    @endif

                    {{-- Kartu Ringkasan Tiga Kolom --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        {{-- Ringkasan Kelas --}}
                        <div class="p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 space-y-2">
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                Rombongan Belajar (Kelas)
                            </span>
                            <div class="flex items-baseline justify-between">
                                <span class="text-2xl font-black text-slate-900 dark:text-white">{{ $r['total_kelas'] }}</span>
                                <span class="text-xs text-slate-600 dark:text-slate-400">Total kelas asal</span>
                            </div>
                            <div class="pt-2 border-t border-slate-200 dark:border-slate-700 text-xs space-y-1">
                                <div class="flex justify-between">
                                    <span class="text-emerald-700 dark:text-emerald-400 font-semibold">&bull; Akan disalin:</span>
                                    <span class="font-bold">{{ $r['kelas_disalin'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-blue-700 dark:text-blue-400 font-semibold">&bull; Sudah ada:</span>
                                    <span class="font-bold">{{ $r['kelas_sudah_ada'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-amber-700 dark:text-amber-400 font-semibold">&bull; Dilewati:</span>
                                    <span class="font-bold">{{ $r['kelas_dilewati'] }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Ringkasan Siswa --}}
                        <div class="p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 space-y-2">
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                Siswa Aktif
                            </span>
                            <div class="flex items-baseline justify-between">
                                <span class="text-2xl font-black text-slate-900 dark:text-white">{{ $r['total_siswa'] }}</span>
                                <span class="text-xs text-slate-600 dark:text-slate-400">Total siswa aktif</span>
                            </div>
                            <div class="pt-2 border-t border-slate-200 dark:border-slate-700 text-xs space-y-1">
                                <div class="flex justify-between">
                                    <span class="text-emerald-700 dark:text-emerald-400 font-semibold">&bull; Akan dipindahkan:</span>
                                    <span class="font-bold">{{ $r['siswa_dipindah'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-amber-700 dark:text-amber-400 font-semibold">&bull; Dilewati:</span>
                                    <span class="font-bold">{{ $r['siswa_dilewati'] }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Ringkasan Jadwal --}}
                        <div class="p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 space-y-2">
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                Jadwal Pelajaran
                            </span>
                            <div class="flex items-baseline justify-between">
                                <span class="text-2xl font-black text-slate-900 dark:text-white">{{ $r['total_jadwal'] }}</span>
                                <span class="text-xs text-slate-600 dark:text-slate-400">Total jadwal asal</span>
                            </div>
                            <div class="pt-2 border-t border-slate-200 dark:border-slate-700 text-xs space-y-1">
                                <div class="flex justify-between">
                                    <span class="text-emerald-700 dark:text-emerald-400 font-semibold">&bull; Akan disalin:</span>
                                    <span class="font-bold">{{ $r['jadwal_disalin'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-amber-700 dark:text-amber-400 font-semibold">&bull; Dilewati:</span>
                                    <span class="font-bold">{{ $r['jadwal_dilewati'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Rincian per Kelas --}}
                    <div class="mt-6 space-y-3">
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white">
                            Rincian Rencana per Kelas ({{ count($pratinjau['rencana_kelas']) }} Kelas)
                        </h4>

                        @if (empty($pratinjau['rencana_kelas']))
                            <x-ui.empty-state
                                title="Tidak Ada Kelas Semester Ganjil"
                                description="Tidak ditemukan kelas aktif pada semester Ganjil tahun ajaran {{ $tahunAjaranTerpilih }}." />
                        @else
                            <x-ui.table class="responsive-stack-table">
                                <thead class="{{ \App\Support\Ui::tableHeader() }}">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 whitespace-nowrap">Kelas Asal (Ganjil)</th>
                                        <th scope="col" class="px-4 py-3 whitespace-nowrap">Rencana Kelas Tujuan (Genap)</th>
                                        <th scope="col" class="px-4 py-3 whitespace-nowrap">Siswa Aktif</th>
                                        <th scope="col" class="px-4 py-3 whitespace-nowrap">Jadwal Pelajaran</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y-2 divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900 text-sm">
                                    @foreach($pratinjau['rencana_kelas'] as $rk)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                                            {{-- Kelas Asal --}}
                                            <td class="stack-title px-4 py-3 font-bold text-slate-900 dark:text-white" data-label="Kelas Asal">
                                                <div class="flex items-center gap-2">
                                                    <span>{{ $rk['kelas_asal']->nama }}</span>
                                                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300">
                                                        Tingkat {{ $rk['kelas_asal']->tingkat }}
                                                    </span>
                                                </div>
                                            </td>

                                            {{-- Kelas Tujuan --}}
                                            <td class="px-4 py-3" data-label="Kelas Tujuan">
                                                @if ($rk['status_kelas'] === 'akan_disalin')
                                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-800 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-500/15 border border-emerald-400 dark:border-emerald-500/50 px-2 py-0.5 rounded-full">
                                                        <x-ui.icon name="plus" size="sm" />
                                                        Akan Disalin
                                                    </span>
                                                @elseif ($rk['status_kelas'] === 'sudah_ada')
                                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-blue-800 dark:text-blue-300 bg-blue-100 dark:bg-blue-500/15 border border-blue-400 dark:border-blue-500/50 px-2 py-0.5 rounded-full">
                                                        <x-ui.icon name="check" size="sm" />
                                                        Sudah Ada (Dipakai)
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-800 dark:text-amber-300 bg-amber-100 dark:bg-amber-500/15 border border-amber-400 dark:border-amber-500/50 px-2 py-0.5 rounded-full">
                                                        <x-ui.icon name="x" size="sm" />
                                                        Dilewati
                                                    </span>
                                                    @if ($rk['alasan_kelas'])
                                                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $rk['alasan_kelas'] }}</p>
                                                    @endif
                                                @endif
                                            </td>

                                            {{-- Siswa --}}
                                            <td class="px-4 py-3" data-label="Siswa">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $rk['jumlah_siswa'] }} siswa</span>
                                                    @if ($rk['status_siswa'] === 'akan_dipindah')
                                                        <span class="text-xs text-emerald-700 dark:text-emerald-400 font-semibold">(dipindah)</span>
                                                    @else
                                                        <span class="text-xs text-amber-700 dark:text-amber-400 font-semibold">(dilewati)</span>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- Jadwal --}}
                                            <td class="px-4 py-3" data-label="Jadwal">
                                                @php
                                                    $disalinJadwalCount = collect($rk['daftar_jadwal'])->where('status', 'akan_disalin')->count();
                                                    $dilewatiJadwalCount = collect($rk['daftar_jadwal'])->where('status', 'dilewati')->count();
                                                @endphp
                                                <div class="space-y-1">
                                                    <div class="flex items-center gap-2 text-xs">
                                                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ count($rk['daftar_jadwal']) }} jadwal:</span>
                                                        <span class="text-emerald-700 dark:text-emerald-400 font-semibold">{{ $disalinJadwalCount }} disalin</span>
                                                        @if ($dilewatiJadwalCount > 0)
                                                            <span class="text-amber-700 dark:text-amber-400 font-semibold">&bull; {{ $dilewatiJadwalCount }} dilewati</span>
                                                        @endif
                                                    </div>

                                                    {{-- Detail alasan dilewati jika ada --}}
                                                    @foreach($rk['daftar_jadwal'] as $dj)
                                                        @if ($dj['status'] === 'dilewati')
                                                            <div class="text-[11px] text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded border border-amber-300 dark:border-amber-700/50 mt-1">
                                                                <span class="font-bold">{{ $dj['hari'] }} {{ $dj['jam'] }} ({{ $dj['mapel_nama'] }}):</span> {{ $dj['alasan'] }}
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </x-ui.table>
                        @endif
                    </div>
                </x-ui.card>
            @endif

        </div>

        {{-- MODAL KONFIRMASI EKSEKUSI --}}
        @if ($pratinjau && !$pratinjau['melebihi_batas'] && $r['total_kelas'] > 0)
            <div x-show="modalKonfirmasi" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="modalKonfirmasi" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="if (!submitting) modalKonfirmasi = false"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="modalKonfirmasi" x-transition class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-2 border-slate-300 dark:border-slate-600 p-6">
                        <div class="flex items-start gap-4">
                            <div class="p-3 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300 shrink-0">
                                <x-ui.icon name="calendar-check" size="md" />
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white" id="modal-title">
                                    Konfirmasi Pergantian Semester
                                </h3>
                                <p class="text-sm text-slate-700 dark:text-slate-300 mt-2">
                                    Anda akan menjalankan pergantian semester otomatis untuk tahun ajaran <strong>{{ $tahunAjaranTerpilih }}</strong> menuju <strong>Semester Genap</strong>:
                                </p>
                                <ul class="list-disc list-inside text-xs text-slate-600 dark:text-slate-400 mt-2 space-y-1 font-medium">
                                    <li>Menyalin / memproses <strong>{{ $r['total_kelas'] }} kelas</strong></li>
                                    <li>Memindahkan <strong>{{ $r['siswa_dipindah'] }} siswa aktif</strong></li>
                                    <li>Menyalin <strong>{{ $r['jadwal_disalin'] }} jadwal pembelajaran</strong></li>
                                </ul>
                                <p class="text-xs text-amber-700 dark:text-amber-400 mt-3 font-semibold">
                                    Pastikan pengisian dan koreksi absensi semester Ganjil telah selesai.
                                </p>
                            </div>
                        </div>

                        <form action="{{ route('admin.pergantian-semester.jalankan') }}" method="POST" class="mt-6 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3" @submit="submitting = true">
                            @csrf
                            <input type="hidden" name="tahun_ajaran" value="{{ $pratinjau['tahun_ajaran'] }}">
                            <input type="hidden" name="semester_asal" value="Ganjil">
                            <input type="hidden" name="salin_kelas" value="{{ $pratinjau['opsi']['salin_kelas'] ? '1' : '0' }}">
                            <input type="hidden" name="pindah_siswa" value="{{ $pratinjau['opsi']['pindah_siswa'] ? '1' : '0' }}">
                            <input type="hidden" name="salin_jadwal" value="{{ $pratinjau['opsi']['salin_jadwal'] ? '1' : '0' }}">

                            <button type="button" @click="modalKonfirmasi = false" :disabled="submitting" class="{{ \App\Support\Ui::tombol('secondary') }} w-full sm:w-auto">
                                Batal
                            </button>
                            <button type="submit" :disabled="submitting" class="{{ \App\Support\Ui::tombol('primary') }} w-full sm:w-auto inline-flex items-center justify-center gap-2">
                                <span x-show="!submitting">Ya, Jalankan Sekarang</span>
                                <span x-show="submitting" x-cloak>Sedang Memproses...</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- MODAL KONFIRMASI AKTIFKAN PERIODE --}}
        <div x-show="modalAktifkan" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="modalAktifkan" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="if (!submittingAktifkan) modalAktifkan = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="modalAktifkan" x-transition class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border-2 border-slate-300 dark:border-slate-600 p-6">
                    <div class="flex items-start gap-4">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-300 shrink-0">
                            <x-ui.icon name="calendar" size="md" />
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                                Konfirmasi Aktivasi Periode
                            </h3>
                            <p class="text-sm text-slate-700 dark:text-slate-300 mt-2">
                                Apakah Anda yakin ingin mengaktifkan periode <strong>Semester Genap {{ $tahunAjaranTerpilih }}</strong> sebagai periode berjalan sekolah saat ini?
                            </p>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-2">
                                Seluruh dashboard guru dan siswa akan langsung menampilkan jadwal dan lembar absensi semester Genap.
                            </p>
                        </div>
                    </div>

                    <form action="{{ route('admin.pergantian-semester.aktifkan') }}" method="POST" class="mt-6 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3" @submit="submittingAktifkan = true">
                        @csrf
                        <input type="hidden" name="tahun_ajaran" value="{{ $tahunAjaranTerpilih }}">

                        <button type="button" @click="modalAktifkan = false" :disabled="submittingAktifkan" class="{{ \App\Support\Ui::tombol('secondary') }} w-full sm:w-auto">
                            Batal
                        </button>
                        <button type="submit" :disabled="submittingAktifkan" class="{{ \App\Support\Ui::tombol('primary') }} w-full sm:w-auto inline-flex items-center justify-center gap-2">
                            <span x-show="!submittingAktifkan">Aktifkan Sekarang</span>
                            <span x-show="submittingAktifkan" x-cloak>Menyimpan...</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>

