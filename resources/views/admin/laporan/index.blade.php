<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Rekap Laporan Absensi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-6 border-b border-gray-200 dark:border-gray-700 gap-2">
                        <div>
                            <h3 class="text-lg font-bold">Filter & Ekspor Laporan</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Unduh rekapitulasi data absensi siswa dalam format lembar kerja Excel atau dokumen PDF resmi.
                            </p>
                        </div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 self-start sm:self-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                <path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" />
                            </svg>
                            <span>Periode Aktif: {{ $activePeriode['semester'] ?? 'Ganjil' }} {{ $activePeriode['tahun_ajaran'] ?? '' }}</span>
                        </div>
                    </div>
                    
                    <form action="{{ route('admin.laporan.export') }}" method="GET" class="space-y-4 max-w-lg">
                        
                        <div>
                            <x-input-label for="kelas_id" :value="__('Kelas (Opsional)')" />
                            <select id="kelas_id" name="kelas_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">-- Semua Kelas --</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <x-input-label for="mapel_id" :value="__('Mata Pelajaran (Opsional)')" />
                            <select id="mapel_id" name="mapel_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">-- Semua Mata Pelajaran --</option>
                                @foreach($mapel as $m)
                                    <option value="{{ $m->id }}">{{ $m->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="tahun_ajaran" :value="__('Tahun Ajaran')" />
                                <select id="tahun_ajaran" name="tahun_ajaran" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    @foreach($daftarTahunAjaran as $ta)
                                        <option value="{{ $ta }}" {{ ($activePeriode['tahun_ajaran'] ?? '') === $ta ? 'selected' : '' }}>
                                            {{ $ta }} {{ ($activePeriode['tahun_ajaran'] ?? '') === $ta ? '(Aktif)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="semester" :value="__('Semester')" />
                                <select id="semester" name="semester" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="Ganjil" {{ ($activePeriode['semester'] ?? '') === 'Ganjil' ? 'selected' : '' }}>
                                        Ganjil {{ ($activePeriode['semester'] ?? '') === 'Ganjil' ? '(Aktif)' : '' }}
                                    </option>
                                    <option value="Genap" {{ ($activePeriode['semester'] ?? '') === 'Genap' ? 'selected' : '' }}>
                                        Genap {{ ($activePeriode['semester'] ?? '') === 'Genap' ? '(Aktif)' : '' }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 pt-4">
                            <button type="submit" formaction="{{ route('admin.laporan.export') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-4 rounded flex items-center justify-center transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Ekspor Excel
                            </button>
                            <button type="submit" formaction="{{ route('admin.laporan.exportPdf') }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-4 rounded flex items-center justify-center transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Ekspor PDF
                            </button>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
