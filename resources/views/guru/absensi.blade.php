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
                        <h3 class="font-bold text-lg">{{ $jadwal->mapel->nama }}</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            {{ $tanggal->isoFormat('dddd, D MMMM YYYY') }} &bull; {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
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
                        Simpan Absensi
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
