<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Absensi Kelas ') . $jadwal->kelas->nama }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="absensiForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100 flex justify-between items-center flex-wrap gap-4">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h3 class="font-bold text-xl">{{ $jadwal->mapel->nama }}</h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                Kelas {{ $jadwal->kelas->nama }}
                            </span>
                            @if($sesi)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                    Mode Koreksi
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                    Pengisian Susulan / Baru
                                </span>
                            @endif
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 font-medium flex items-center gap-2 flex-wrap">
                            <span>Tanggal Sesi: <strong class="text-gray-900 dark:text-gray-100">{{ $tanggal->isoFormat('dddd, D MMMM YYYY') }}</strong></span>
                            <span>&bull;</span>
                            <span>{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB</span>
                        </p>
                    </div>
                    
                    <!-- Ringkasan reaktif -->
                    <div class="flex space-x-4 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg border dark:border-gray-600">
                        <div class="text-center">
                            <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Hadir</span>
                            <span class="font-bold text-lg text-green-600" x-text="summary.hadir"></span>
                        </div>
                        <div class="text-center">
                            <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Izin</span>
                            <span class="font-bold text-lg text-blue-600" x-text="summary.izin"></span>
                        </div>
                        <div class="text-center">
                            <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Sakit</span>
                            <span class="font-bold text-lg text-yellow-600" x-text="summary.sakit"></span>
                        </div>
                        <div class="text-center">
                            <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Alpa</span>
                            <span class="font-bold text-lg text-red-600" x-text="summary.alpa"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pemilih Tanggal Sesi / Batas Koreksi -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    @if(Auth::user()->role === 'admin')
                        <div>
                            <x-input-label for="tanggal_admin" :value="__('Tanggal Sesi (Koreksi Admin)')" />
                            <input type="date" id="tanggal_admin" value="{{ $tanggal->toDateString() }}" max="{{ \Carbon\Carbon::now('Asia/Jakarta')->toDateString() }}" onchange="window.location.href='{{ route('guru.absensi.show', $jadwal->id) }}?tanggal=' + this.value" class="mt-1 block border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm text-sm">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Admin bebas memilih tanggal historis (tidak boleh masa depan).</p>
                        </div>
                    @else
                        <div>
                            <x-input-label for="tanggal_guru" :value="__('Pilih Tanggal Sesi / Koreksi')" />
                            <select id="tanggal_guru" onchange="window.location.href='{{ route('guru.absensi.show', $jadwal->id) }}?tanggal=' + this.value" class="mt-1 block border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm text-sm">
                                @forelse($tanggalBolehDikoreksi as $tgl)
                                    @php
                                        $isHariIni = $tgl === \Carbon\Carbon::now('Asia/Jakarta')->toDateString();
                                        $label = \Carbon\Carbon::parse($tgl, 'Asia/Jakarta')->isoFormat('dddd, D MMMM YYYY') . ($isHariIni ? ' (Hari Ini)' : ' (Koreksi)');
                                    @endphp
                                    <option value="{{ $tgl }}" {{ $tanggal->toDateString() === $tgl ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @empty
                                    <option value="{{ $tanggal->toDateString() }}">{{ $tanggal->isoFormat('dddd, D MMMM YYYY') }}</option>
                                @endforelse
                            </select>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Dibatasi maksimal 7 hari terakhir yang cocok dengan hari {{ ucfirst($jadwal->hari) }}.</p>
                        </div>
                    @endif

                    @if($sesi)
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <div class="flex items-center gap-1.5 text-green-600 dark:text-green-400 font-semibold">
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
                        <div class="text-sm text-amber-600 dark:text-amber-400 flex items-center gap-1.5 font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Belum ada data absensi untuk tanggal ini
                        </div>
                    @endif
                </div>
            </div>

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('guru.absensi.store', $jadwal->id) }}">
                @csrf
                <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">
                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="mb-4">
                            <x-input-label for="catatan" :value="__('Catatan Sesi (Opsional)')" />
                            <textarea id="catatan" name="catatan" rows="2" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('catatan', $sesi->catatan ?? '') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('catatan')" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-x-auto">
                    <table class="w-full whitespace-no-wrap">
                        <thead>
                            <tr class="text-left font-bold border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                <th class="px-6 py-3">No</th>
                                <th class="px-6 py-3">Nama Siswa</th>
                                <th class="px-6 py-3 min-w-[300px]">Status & Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-gray-700">
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
                                <tr class="text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium">{{ $siswa->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $siswa->nis }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col sm:flex-row gap-2">
                                            <div class="flex space-x-2">
                                                <label class="inline-flex items-center">
                                                    <input type="radio" x-model="students[{{ $siswa->id }}].status" name="siswa[{{ $siswa->id }}][status]" value="hadir" class="text-green-600 focus:ring-green-500">
                                                    <span class="ml-1 mr-3">Hadir</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" x-model="students[{{ $siswa->id }}].status" name="siswa[{{ $siswa->id }}][status]" value="izin" class="text-blue-600 focus:ring-blue-500">
                                                    <span class="ml-1 mr-3">Izin</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" x-model="students[{{ $siswa->id }}].status" name="siswa[{{ $siswa->id }}][status]" value="sakit" class="text-yellow-600 focus:ring-yellow-500">
                                                    <span class="ml-1 mr-3">Sakit</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" x-model="students[{{ $siswa->id }}].status" name="siswa[{{ $siswa->id }}][status]" value="alpa" class="text-red-600 focus:ring-red-500">
                                                    <span class="ml-1">Alpa</span>
                                                </label>
                                            </div>
                                            
                                            <div class="w-full sm:w-auto flex-grow" x-show="students[{{ $siswa->id }}].status !== 'hadir'" x-cloak>
                                                <input type="text" x-model="students[{{ $siswa->id }}].keterangan" name="siswa[{{ $siswa->id }}][keterangan]" placeholder="Keterangan..." class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('guru.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        {{ $sesi ? 'Simpan Koreksi' : 'Simpan Absensi' }}
                    </button>
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
