<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('Absensi Kelas ') . $jadwal->kelas->nama }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8 pb-28 sm:pb-12" x-data="absensiForm()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Banner Sesi & Info -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border-2 border-gray-300 dark:border-gray-600 overflow-hidden">
                <div class="p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap mb-2">
                            <h3 class="font-extrabold text-xl sm:text-2xl text-gray-900 dark:text-gray-100">{{ $jadwal->mapel->nama }}</h3>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200 border border-blue-300 dark:border-blue-700">
                                Kelas {{ $jadwal->kelas->nama }}
                            </span>
                            @if($sesi)
                                <x-ui.status-badge status="sudah_diabsen" label="Mode Koreksi" />
                            @else
                                <x-ui.status-badge status="belum_diabsen" label="Pengisian Susulan / Baru" />
                            @endif
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 text-sm font-medium flex items-center gap-2 flex-wrap">
                            <span>Tanggal Sesi: <strong class="text-gray-900 dark:text-gray-100">{{ $tanggal->isoFormat('dddd, D MMMM YYYY') }}</strong></span>
                            <span>&bull;</span>
                            <span>{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB</span>
                        </p>
                    </div>
                    
                    <!-- Ringkasan reaktif H / I / S / A -->
                    <div class="grid grid-cols-4 gap-2 sm:gap-3 bg-gray-50 dark:bg-gray-750 p-3 sm:p-4 rounded-xl border-2 border-gray-300 dark:border-gray-600">
                        <div class="text-center px-2 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border-2 border-emerald-300 dark:border-emerald-700">
                            <span class="block text-[11px] font-extrabold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider">Hadir</span>
                            <span class="font-extrabold text-xl sm:text-2xl text-emerald-700 dark:text-emerald-400" x-text="summary.hadir"></span>
                        </div>
                        <div class="text-center px-2 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/40 border-2 border-blue-300 dark:border-blue-700">
                            <span class="block text-[11px] font-extrabold text-blue-800 dark:text-blue-300 uppercase tracking-wider">Izin</span>
                            <span class="font-extrabold text-xl sm:text-2xl text-blue-600 dark:text-blue-400" x-text="summary.izin"></span>
                        </div>
                        <div class="text-center px-2 py-1 rounded-lg bg-amber-50 dark:bg-amber-950/40 border-2 border-amber-300 dark:border-amber-700">
                            <span class="block text-[11px] font-extrabold text-amber-800 dark:text-amber-300 uppercase tracking-wider">Sakit</span>
                            <span class="font-extrabold text-xl sm:text-2xl text-amber-600 dark:text-amber-400" x-text="summary.sakit"></span>
                        </div>
                        <div class="text-center px-2 py-1 rounded-lg bg-red-50 dark:bg-red-950/40 border-2 border-red-300 dark:border-red-700">
                            <span class="block text-[11px] font-extrabold text-red-800 dark:text-red-300 uppercase tracking-wider">Alpa</span>
                            <span class="font-extrabold text-xl sm:text-2xl text-red-600 dark:text-red-400" x-text="summary.alpa"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pemilih Tanggal Sesi / Batas Koreksi -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border-2 border-gray-300 dark:border-gray-600 overflow-hidden">
                <div class="p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    @if(Auth::user()->role === 'admin')
                        <div>
                            <x-input-label for="tanggal_admin" :value="__('Tanggal Sesi (Koreksi Admin)')" />
                            <input type="date" id="tanggal_admin" value="{{ $tanggal->toDateString() }}" max="{{ \Carbon\Carbon::now('Asia/Jakarta')->toDateString() }}" onchange="window.location.href='{{ route('guru.absensi.show', $jadwal->id) }}?tanggal=' + this.value" class="mt-1 block border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg shadow-sm text-base sm:text-sm min-h-[44px] sm:min-h-0 py-2 sm:py-1.5 px-3">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Admin bebas memilih tanggal historis (tidak boleh masa depan).</p>
                        </div>
                    @else
                        <div>
                            <x-input-label :value="__('Tanggal Sesi Absensi')" />
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <span class="px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm font-bold rounded-lg border-2 border-gray-300 dark:border-gray-600">
                                    {{ $tanggal->isoFormat('dddd, D MMMM YYYY') }}
                                </span>
                                <a href="{{ route('guru.koreksi-absensi') }}" class="inline-flex items-center text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 gap-1.5 bg-blue-50 dark:bg-blue-950/40 px-3 py-2 rounded-lg border-2 border-blue-200 dark:border-blue-800 min-h-[44px] transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M11.5 21h-5.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v6" />
                                        <path d="M16 3v4" />
                                        <path d="M8 3v4" />
                                        <path d="M4 11h16" />
                                        <path d="M15 19l2 2l4 -4" />
                                    </svg>
                                    <span>Pilih Tanggal Lain di Jadwal & Koreksi Absensi &rarr;</span>
                                </a>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">Sesi absensi guru dibatasi 7 hari terakhir (hari ini s.d. H-6).</p>
                        </div>
                    @endif

                    @if($sesi)
                        <div class="text-sm text-gray-600 dark:text-gray-300">
                            <div class="flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400 font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Sesi sudah pernah disimpan
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                @if($sesi->diubah_oleh)
                                    Terakhir dikoreksi oleh user #{{ $sesi->diubah_oleh }} pada {{ $sesi->updated_at?->format('H:i') }}
                                @else
                                    Diabsen pertama kali oleh user #{{ $sesi->diabsen_oleh }} pada {{ $sesi->created_at?->format('H:i') }}
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-sm text-amber-700 dark:text-amber-400 flex items-center gap-1.5 font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
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

            <form method="POST" action="{{ route('guru.absensi.store', $jadwal->id) }}" id="form-absensi">
                @csrf
                <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">
                
                <!-- Catatan Sesi -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border-2 border-gray-300 dark:border-gray-600 overflow-hidden mb-6">
                    <div class="p-5 sm:p-6">
                        <x-input-label for="catatan" :value="__('Catatan Sesi (Opsional)')" />
                        <textarea id="catatan" name="catatan" rows="2" class="mt-1 block w-full border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 rounded-lg shadow-sm text-base sm:text-sm py-2 px-3" placeholder="Contoh: Materi BAB 3 tuntas, 2 siswa izin dispensasi lomba...">{{ old('catatan', $sesi->catatan ?? '') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('catatan')" />
                    </div>
                </div>

                <!-- Daftar Siswa (Tabel Desktop / Kartu HP) -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border-2 border-gray-300 dark:border-gray-600 overflow-hidden">
                    <div class="p-4 sm:p-5 border-b-2 border-gray-300 dark:border-gray-600 flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-base text-gray-900 dark:text-gray-100">Daftar Siswa Kelas</h4>
                            <p class="text-xs text-gray-600 dark:text-gray-300">Total {{ $jadwal->kelas->siswa->count() }} siswa terdaftar</p>
                        </div>
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 hidden sm:inline">Pilih status untuk tiap siswa</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full responsive-stack-table border-collapse">
                            <thead class="bg-gray-100 dark:bg-gray-800 border-b-2 border-gray-400 dark:border-gray-500 text-gray-800 dark:text-gray-200">
                                <tr>
                                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider w-16">No</th>
                                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider w-64">Nama Siswa</th>
                                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider min-w-[340px]">Status & Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                @foreach($jadwal->kelas->siswa as $index => $siswa)
                                    @php
                                        $defaultStatus = 'hadir';
                                        $defaultKeterangan = '';
                                        if(old('siswa.'.$siswa->id.'.status')) {
                                            $defaultStatus = old('siswa.'.$siswa->id.'.status');
                                        } elseif(isset($detailExisting[$siswa->id])) {
                                            $defaultStatus = $detailExisting[$siswa->id]['status'];
                                            $defaultKeterangan = $detailExisting[$siswa->id]['keterangan'];
                                        }
                                    @endphp
                                    <tr class="text-gray-900 dark:text-gray-100 hover:bg-gray-50/70 dark:hover:bg-gray-750 transition-colors">
                                        <td class="px-4 sm:px-6 py-4 font-semibold text-gray-700 dark:text-gray-300" data-label="No">{{ $index + 1 }}</td>
                                        <td class="px-4 sm:px-6 py-4" data-label="Nama Siswa">
                                            <div class="font-bold text-gray-900 dark:text-gray-100 text-base sm:text-sm">{{ $siswa->user->name }}</div>
                                            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">NIS: {{ $siswa->nis }}</div>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4" data-label="Status & Keterangan">
                                            <div class="space-y-3">
                                                <!-- Pilihan Status Segmen Radio Besar -->
                                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                                    <!-- Hadir -->
                                                    <label class="cursor-pointer flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg border-2 text-xs font-bold transition select-none min-h-[44px]"
                                                           :class="students[{{ $siswa->id }}].status === 'hadir' 
                                                               ? 'bg-emerald-700 text-white border-emerald-700 dark:bg-emerald-500 dark:text-gray-900 dark:border-emerald-500 shadow-xs' 
                                                               : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:border-emerald-500'">
                                                        <input type="radio" x-model="students[{{ $siswa->id }}].status" name="siswa[{{ $siswa->id }}][status]" value="hadir" class="sr-only">
                                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                        <span>Hadir</span>
                                                    </label>

                                                    <!-- Izin -->
                                                    <label class="cursor-pointer flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg border-2 text-xs font-bold transition select-none min-h-[44px]"
                                                           :class="students[{{ $siswa->id }}].status === 'izin' 
                                                               ? 'bg-blue-600 text-white border-blue-600 dark:bg-blue-400 dark:text-gray-900 dark:border-blue-400 shadow-xs' 
                                                               : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:border-blue-500'">
                                                        <input type="radio" x-model="students[{{ $siswa->id }}].status" name="siswa[{{ $siswa->id }}][status]" value="izin" class="sr-only">
                                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        <span>Izin</span>
                                                    </label>

                                                    <!-- Sakit -->
                                                    <label class="cursor-pointer flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg border-2 text-xs font-bold transition select-none min-h-[44px]"
                                                           :class="students[{{ $siswa->id }}].status === 'sakit' 
                                                               ? 'bg-amber-400 text-gray-900 border-amber-400 dark:bg-amber-400 dark:text-gray-900 dark:border-amber-400 shadow-xs' 
                                                               : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:border-amber-500'">
                                                        <input type="radio" x-model="students[{{ $siswa->id }}].status" name="siswa[{{ $siswa->id }}][status]" value="sakit" class="sr-only">
                                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                                        <span>Sakit</span>
                                                    </label>

                                                    <!-- Alpa -->
                                                    <label class="cursor-pointer flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg border-2 text-xs font-bold transition select-none min-h-[44px]"
                                                           :class="students[{{ $siswa->id }}].status === 'alpa' 
                                                               ? 'bg-red-600 text-white border-red-600 dark:bg-red-400 dark:text-gray-900 dark:border-red-400 shadow-xs' 
                                                               : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:border-red-500'">
                                                        <input type="radio" x-model="students[{{ $siswa->id }}].status" name="siswa[{{ $siswa->id }}][status]" value="alpa" class="sr-only">
                                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        <span>Alpa</span>
                                                    </label>
                                                </div>
                                                
                                                <!-- Input Keterangan Jika Bukan Hadir -->
                                                <div x-show="students[{{ $siswa->id }}].status !== 'hadir'" x-cloak class="pt-1">
                                                    <input type="text" x-model="students[{{ $siswa->id }}].keterangan" name="siswa[{{ $siswa->id }}][keterangan]" placeholder="Tulis alasan izin / sakit / alpa..." class="w-full border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg text-base sm:text-xs min-h-[44px] py-2 sm:py-1.5 px-3 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 shadow-xs">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bar Tombol Aksi (Sticky di HP dengan Safe Area, Static di Desktop) -->
                <div class="sticky bottom-0 z-40 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md border-t-2 border-gray-300 dark:border-gray-600 p-4 sm:p-5 shadow-lg sm:shadow-sm rounded-t-xl sm:rounded-xl pb-[calc(1rem+env(safe-area-inset-bottom,0px))] mt-6">
                    <div class="max-w-7xl mx-auto flex items-center justify-between sm:justify-end gap-3">
                        <div class="sm:hidden text-xs font-semibold text-gray-700 dark:text-gray-300">
                            <span class="text-emerald-700 dark:text-emerald-400 font-bold" x-text="summary.hadir + ' H'"></span> &bull;
                            <span class="text-blue-600 dark:text-blue-400 font-bold" x-text="summary.izin + ' I'"></span> &bull;
                            <span class="text-amber-600 dark:text-amber-400 font-bold" x-text="summary.sakit + ' S'"></span> &bull;
                            <span class="text-red-600 dark:text-red-400 font-bold" x-text="summary.alpa + ' A'"></span>
                        </div>
                        <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                            <a href="{{ route('guru.dashboard') }}" class="inline-flex justify-center items-center px-4 py-2.5 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-xl font-bold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-xs hover:bg-gray-100 dark:hover:bg-gray-700 min-h-[44px] transition">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex justify-center items-center px-6 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white border-2 border-blue-700 dark:border-blue-500 rounded-xl font-bold text-xs uppercase tracking-widest shadow-sm min-h-[44px] transition">
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
            // Data inisial dari server
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
                get summary() {
                    const counts = { hadir: 0, izin: 0, sakit: 0, alpa: 0 };
                    for (const id in this.students) {
                        counts[this.students[id].status]++;
                    }
                    return counts;
                }
            }
        }
    </script>
</x-app-layout>
