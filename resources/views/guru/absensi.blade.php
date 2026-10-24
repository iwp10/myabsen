<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
            <x-ui.back-button :href="route('guru.dashboard')" />
            <div class="min-w-0 flex-1">
                <h2 class="font-bold text-base sm:text-xl text-slate-900 dark:text-white leading-tight truncate">
                    Absensi: {{ $jadwal->mapel->nama }} - {{ $jadwal->kelas->nama }}
                </h2>
                <p class="text-xs text-slate-600 dark:text-slate-400 truncate">
                    {{ $tanggal->isoFormat('dddd, D MMMM YYYY') }} • {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8 pb-32 sm:pb-16" x-data="absensiForm()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            <!-- Tombol Kembali & Header Kompak -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <x-ui.back-button :href="route('guru.dashboard')" />
                    <div>
                        <h3 class="font-extrabold text-lg sm:text-2xl text-slate-900 dark:text-white leading-tight">
                            {{ $jadwal->mapel->nama }}
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 font-medium">
                            Kelas {{ $jadwal->kelas->nama }} • {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100 border border-slate-300 dark:border-slate-600">
                        {{ $tanggal->isoFormat('dddd, D MMMM YYYY') }}
                    </span>
                    @if($sesi)
                        <x-ui.status-badge status="sudah_diabsen" label="Mode Koreksi" />
                    @else
                        <x-ui.status-badge status="belum_diabsen" label="Pengisian Baru" />
                    @endif
                </div>
            </div>

            <!-- Pemilih Tanggal Sesi / Batas Koreksi -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border-2 border-slate-300 dark:border-slate-600 p-4 sm:p-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    @if(Auth::user()->role === 'admin')
                        <div>
                            <x-input-label for="tanggal_admin" :value="__('Tanggal Sesi (Koreksi Admin)')" />
                            <input type="date" id="tanggal_admin" value="{{ $tanggal->toDateString() }}" max="{{ \Carbon\Carbon::now('Asia/Jakarta')->toDateString() }}" onchange="window.location.href='{{ route('guru.absensi.show', $jadwal->id) }}?tanggal=' + this.value" class="mt-1 block border-2 border-slate-400 dark:border-slate-500 dark:bg-slate-950 dark:text-slate-50 dark:[color-scheme:dark] rounded-xl shadow-xs text-base sm:text-sm min-h-[44px] py-2 px-3">
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">Admin bebas memilih tanggal historis (tidak boleh masa depan).</p>
                        </div>
                    @else
                        <div class="flex items-center justify-between sm:justify-start gap-3 flex-wrap w-full sm:w-auto">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 block">Sesi Tanggal</span>
                                <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $tanggal->isoFormat('dddd, D MMMM YYYY') }}</span>
                            </div>
                            <a href="{{ route('guru.koreksi-absensi') }}" class="inline-flex items-center text-xs font-bold text-emerald-700 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300 gap-1 bg-emerald-50 dark:bg-emerald-500/15 px-3 py-2 rounded-xl border-2 border-emerald-400 dark:border-emerald-500/50 min-h-[44px] transition">
                                <x-ui.icon name="calendar" size="sm" class="w-4 h-4" />
                                <span>Pilih Tanggal Lain →</span>
                            </a>
                        </div>
                    @endif

                    @if($sesi)
                        <div class="text-xs sm:text-sm text-slate-600 dark:text-slate-300">
                            <div class="flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400 font-bold">
                                <x-ui.icon name="check" size="sm" class="w-4 h-4" />
                                Sesi sudah pernah disimpan
                            </div>
                            <div class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                                @if($sesi->diubah_oleh)
                                    Terakhir dikoreksi oleh user #{{ $sesi->diubah_oleh }} pada {{ $sesi->updated_at?->format('H:i') }}
                                @else
                                    Diabsen pertama kali oleh user #{{ $sesi->diabsen_oleh }} pada {{ $sesi->created_at?->format('H:i') }}
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-xs sm:text-sm text-amber-700 dark:text-amber-400 flex items-center gap-1.5 font-bold">
                            <x-ui.icon name="info" size="sm" class="w-4 h-4" />
                            Belum ada data absensi untuk tanggal ini
                        </div>
                    @endif
                </div>
            </div>

            @if ($errors->any())
                <x-alert type="danger">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-alert>
            @endif

            <!-- Ringkasan Sticky Melekat dengan Tombol Semua Hadir -->
            <x-ui.ringkasan-sticky :total-siswa="$jadwal->kelas->siswa->count()" />

            <form method="POST" action="{{ route('guru.absensi.store', $jadwal->id) }}" id="form-absensi">
                @csrf
                <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">

                <!-- Catatan Sesi -->
                <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border-2 border-slate-300 dark:border-slate-600 overflow-hidden mb-4 sm:mb-6">
                    <div class="p-4 sm:p-5">
                        <x-input-label for="catatan" :value="__('Catatan Sesi (Opsional)')" />
                        <textarea id="catatan"
                                  name="catatan"
                                  rows="2"
                                  class="mt-1 block w-full border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 placeholder:text-slate-500 dark:placeholder:text-slate-400 dark:[color-scheme:dark] focus:outline-none focus:ring-4 focus:ring-emerald-500 rounded-xl shadow-xs text-base sm:text-sm py-2 px-3"
                                  placeholder="Contoh: Materi BAB 3 tuntas, 2 siswa izin dispensasi lomba...">{{ old('catatan', $sesi->catatan ?? '') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('catatan')" />
                    </div>
                </div>

                <!-- Daftar Siswa -->
                <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border-2 border-slate-300 dark:border-slate-600 overflow-hidden">
                    <div class="p-4 border-b-2 border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h4 class="font-bold text-base text-slate-900 dark:text-white">Daftar Siswa Kelas</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-300">Total {{ $jadwal->kelas->siswa->count() }} siswa terdaftar</p>
                        </div>

                        <!-- Kolom Pencarian Siswa Sisi Klien -->
                        <div class="w-full sm:w-72">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-600 dark:text-slate-400">
                                    <x-ui.icon name="search" size="sm" class="w-4 h-4" />
                                </div>
                                <input type="text"
                                       x-model="searchQuery"
                                       placeholder="Cari nama atau NIS siswa..."
                                       aria-label="Cari nama atau NIS siswa"
                                       class="block w-full pl-9 pr-3 py-2 text-base sm:text-xs border-2 border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-emerald-500 min-h-[44px]">
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full responsive-stack-table border-collapse">
                            <thead class="bg-slate-100 dark:bg-slate-800 border-b-2 border-slate-400 dark:border-slate-500 text-slate-900 dark:text-white font-bold">
                                <tr>
                                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider w-16">No</th>
                                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider w-64">Nama Siswa</th>
                                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider min-w-[340px]">Status & Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                                @foreach($jadwal->kelas->siswa as $index => $siswa)
                                    @php
                                        $defaultStatus = 'hadir';
                                        $defaultKeterangan = '';
                                        if(old('siswa.'.$siswa->id.'.status')) {
                                            $defaultStatus = old('siswa.'.$siswa->id.'.status');
                                            $defaultKeterangan = old('siswa.'.$siswa->id.'.keterangan', '');
                                        } elseif(isset($detailExisting[$siswa->id])) {
                                            $defaultStatus = $detailExisting[$siswa->id]['status'];
                                            $defaultKeterangan = $detailExisting[$siswa->id]['keterangan'] ?? '';
                                        }
                                    @endphp
                                    <tr x-show="!searchQuery || '{{ strtolower(addslashes($siswa->user->name)) }}'.includes(searchQuery.toLowerCase()) || '{{ $siswa->nis }}'.includes(searchQuery)"
                                        class="text-slate-800 dark:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors {{ $loop->even ? 'bg-slate-50/50 dark:bg-slate-800/40' : 'bg-white dark:bg-slate-900' }}">
                                        
                                        {{-- Nomor Urut (disembunyikan di HP via stack-hidden-mobile) --}}
                                        <td class="px-4 sm:px-6 py-4 font-semibold text-slate-700 dark:text-slate-300 tabular-nums stack-hidden-mobile" data-label="No">
                                            {{ $index + 1 }}
                                        </td>

                                        {{-- Header Kartu Mobile: Nama Siswa & NIS --}}
                                        <td class="px-4 sm:px-6 py-4 stack-title" data-label="Nama Siswa">
                                            <div class="flex items-center justify-between sm:block gap-2 w-full">
                                                <div>
                                                    <div class="font-bold text-slate-900 dark:text-white text-base sm:text-sm">
                                                        {{ $siswa->user->name }}
                                                    </div>
                                                    <div class="text-xs font-semibold text-slate-600 dark:text-slate-400">
                                                        NIS: {{ $siswa->nis }}
                                                    </div>
                                                </div>
                                                <span class="sm:hidden text-xs font-bold px-2 py-1 rounded-md bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-300 dark:border-slate-600">
                                                    #{{ $index + 1 }}
                                                </span>
                                            </div>
                                        </td>

                                        {{-- Status & Keterangan --}}
                                        <td class="px-4 sm:px-6 py-4 stack-full" data-label="Status & Keterangan">
                                            <div class="space-y-2.5">
                                                {{-- Grup Pilihan Status 4 Tombol Besar H/I/S/A --}}
                                                <x-ui.segmented-status :siswa-id="$siswa->id" :siswa-nama="$siswa->user->name" :selected="$defaultStatus" />

                                                {{-- Input Keterangan Jika Bukan Hadir --}}
                                                <div x-show="students[{{ $siswa->id }}]?.status !== 'hadir'" x-cloak class="pt-1">
                                                    <label for="keterangan_{{ $siswa->id }}" class="sr-only">Alasan izin, sakit, atau alpa untuk {{ $siswa->user->name }}</label>
                                                    <input type="text"
                                                           id="keterangan_{{ $siswa->id }}"
                                                           x-model="students[{{ $siswa->id }}].keterangan"
                                                           name="siswa[{{ $siswa->id }}][keterangan]"
                                                           placeholder="Tulis alasan izin / sakit / alpa..."
                                                           class="w-full border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 placeholder:text-slate-500 dark:placeholder:text-slate-400 dark:[color-scheme:dark] rounded-xl text-base sm:text-xs min-h-[44px] py-2 sm:py-1.5 px-3 focus:outline-none focus:ring-4 focus:ring-emerald-500 shadow-xs">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bar Tombol Aksi (Sticky di HP dengan Safe Area) -->
                <div class="sticky bottom-0 left-0 right-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t-2 border-slate-300 dark:border-slate-600 p-3 sm:p-4 shadow-lg pb-[calc(0.75rem+env(safe-area-inset-bottom,0px))]">
                    <div class="max-w-7xl mx-auto flex items-center justify-between sm:justify-end gap-3">
                        <div class="sm:hidden text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <span class="text-emerald-700 dark:text-emerald-400 font-bold" x-text="(summary.hadir || 0) + ' Hadir'"></span> •
                            <span class="text-blue-700 dark:text-blue-400 font-bold" x-text="(summary.izin || 0) + ' Izin'"></span> •
                            <span class="text-amber-700 dark:text-amber-400 font-bold" x-text="(summary.sakit || 0) + ' Sakit'"></span> •
                            <span class="text-red-700 dark:text-red-400 font-bold" x-text="(summary.alpa || 0) + ' Alpa'"></span>
                        </div>
                        <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto justify-end">
                            <a href="{{ route('guru.dashboard') }}"
                               class="inline-flex justify-center items-center px-4 py-2.5 bg-white dark:bg-slate-800 border-2 border-slate-400 dark:border-slate-500 rounded-xl font-bold text-xs text-slate-900 dark:text-slate-100 uppercase tracking-widest shadow-xs hover:bg-slate-100 dark:hover:bg-slate-700 min-h-[44px] transition">
                                Batal
                            </a>
                            <button type="submit"
                                    class="flex-1 sm:flex-initial inline-flex justify-center items-center px-6 py-2.5 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950 rounded-xl font-bold text-xs uppercase tracking-widest shadow-sm min-h-[44px] transition focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500">
                                {{ $sesi ? 'Simpan Koreksi' : 'Simpan Absensi' }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function absensiForm() {
            const initialStudents = {
                @foreach($jadwal->kelas->siswa as $siswa)
                    @php
                        $status = 'hadir';
                        $ket = '';
                        if(old('siswa.'.$siswa->id.'.status')) {
                            $status = old('siswa.'.$siswa->id.'.status');
                            $ket = old('siswa.'.$siswa->id.'.keterangan', '');
                        } elseif(isset($detailExisting[$siswa->id])) {
                            $status = $detailExisting[$siswa->id]['status'];
                            $ket = $detailExisting[$siswa->id]['keterangan'] ?? '';
                        }
                    @endphp
                    "{{ $siswa->id }}": {
                        status: "{{ $status }}",
                        keterangan: "{{ $ket }}"
                    },
                @endforeach
            };

            return {
                students: initialStudents,
                searchQuery: '',
                setSemuaHadir() {
                    for (const id in this.students) {
                        this.students[id].status = 'hadir';
                    }
                },
                get summary() {
                    const counts = { hadir: 0, izin: 0, sakit: 0, alpa: 0, total: 0 };
                    for (const id in this.students) {
                        const st = this.students[id].status;
                        if (counts[st] !== undefined) {
                            counts[st]++;
                        }
                        counts.total++;
                    }
                    return counts;
                }
            }
        }
    </script>
</x-app-layout>
