<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Kelola Jadwal') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <p class="font-semibold text-sm">Terjadi kesalahan pada filter jadwal:</p>
                    <ul class="mt-1 list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 space-y-5">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div class="flex flex-col">
                            <h3 class="text-lg font-bold">Daftar Jadwal</h3>
                            @if($activePeriode['tahun_ajaran'])
                                <span class="text-sm text-green-600 dark:text-green-400">Periode Aktif: {{ $activePeriode['tahun_ajaran'] }} - {{ $activePeriode['semester'] }}</span>
                            @endif
                        </div>
                        <a href="{{ route('admin.jadwal.create') }}" class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md text-sm transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M12 5v14M5 12h14"/></svg>
                            Tambah Jadwal
                        </a>
                    </div>

                    {{-- Form Filter Terpadu --}}
                    @php
                        $hasActiveFilters = request()->hasAny(['search', 'periode', 'tahun_ajaran', 'semester', 'hari', 'kelas_id', 'guru_id', 'mapel_id', 'jam_mulai_dari', 'jam_mulai_sampai', 'jam_dari', 'jam_sampai'])
                            && (request('search') || request('periode') || request('tahun_ajaran') || request('semester') || request('hari') || request('kelas_id') || request('guru_id') || request('mapel_id') || request('jam_mulai_dari') || request('jam_mulai_sampai') || request('jam_dari') || request('jam_sampai'));
                    @endphp
                    <div class="bg-gray-50 dark:bg-gray-700/40 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                        <form method="GET" action="{{ route('admin.jadwal.index') }}" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                {{-- Filter Periode --}}
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Periode</label>
                                    <select name="periode" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                        <option value="">Semua periode</option>
                                        @foreach($daftarPeriode as $item)
                                            <option value="{{ $item['value'] }}" {{ $filterPeriode === $item['value'] ? 'selected' : '' }}>
                                                {{ $item['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filter Hari --}}
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Hari</label>
                                    <select name="hari" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm capitalize">
                                        <option value="">Semua Hari</option>
                                        @foreach($daftarHariOptions as $val => $lbl)
                                            <option value="{{ $val }}" {{ strtolower((string)$filterHari) === $val ? 'selected' : '' }}>
                                                {{ $lbl }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filter Kelas --}}
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Kelas</label>
                                    <select name="kelas_id" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                        <option value="">Semua Kelas</option>
                                        @foreach($daftarKelas as $k)
                                            <option value="{{ $k->id }}" {{ (string)$filterKelasId === (string)$k->id ? 'selected' : '' }}>
                                                {{ $k->nama }} ({{ $k->tahun_ajaran }} - {{ $k->semester }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filter Guru --}}
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Guru</label>
                                    <select name="guru_id" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                        <option value="">Semua Guru</option>
                                        @foreach($daftarGuru as $g)
                                            <option value="{{ $g->id }}" {{ (string)$filterGuruId === (string)$g->id ? 'selected' : '' }}>
                                                {{ $g->user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filter Mapel --}}
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Mata Pelajaran</label>
                                    <select name="mapel_id" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                        <option value="">Semua Mapel</option>
                                        @foreach($daftarMapel as $m)
                                            <option value="{{ $m->id }}" {{ (string)$filterMapelId === (string)$m->id ? 'selected' : '' }}>
                                                {{ $m->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filter Rentang Jam (Jam Mulai Dari) --}}
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Jam mulai dari</label>
                                    <input type="time" name="jam_mulai_dari" value="{{ $filterJamMulaiDari }}" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                </div>

                                {{-- Filter Rentang Jam (Sampai) --}}
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">sampai</label>
                                    <input type="time" name="jam_mulai_sampai" value="{{ $filterJamMulaiSampai }}" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                </div>

                                {{-- Kata Kunci Pencarian --}}
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Pencarian</label>
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari jadwal..." class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-1">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-5 rounded-md text-sm transition">
                                    Terapkan Filter
                                </button>
                                @if($hasActiveFilters)
                                    <a href="{{ route('admin.jadwal.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-md text-sm transition">
                                        Reset
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 dark:text-gray-200">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">T.A</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Semester</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Hari</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Jam</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Kelas</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Mata Pelajaran</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Guru</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($jadwals as $jadwal)
                                    @php
                                        $badgeHariMap = [
                                            'senin' => 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300',
                                            'selasa' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
                                            'rabu' => 'bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-300',
                                            'kamis' => 'bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-300',
                                            'jumat' => 'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300',
                                            'sabtu' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
                                        ];
                                        $hariKey = strtolower($jadwal->hari);
                                        $hariClass = $badgeHariMap[$hariKey] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                                    @endphp
                                    <tr>
                                        {{-- Periode: teks abu-abu netral --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400 font-medium">{{ $jadwal->tahun_ajaran }}</td>

                                        {{-- Semester: badge, Ganjil biru dan Genap ungu --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if(strtolower($jadwal->semester) === 'genap')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">
                                                    {{ $jadwal->semester }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                                    {{ $jadwal->semester }}
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Hari: badge warna berbeda tiap hari --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize {{ $hariClass }}">
                                                {{ $jadwal->hari }}
                                            </span>
                                        </td>

                                        {{-- Jam: teks teal dengan angka monospasi/tabular --}}
                                        <td class="px-6 py-4 whitespace-nowrap font-mono tabular-nums text-sm font-medium text-teal-700 dark:text-teal-400">
                                            {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                                        </td>

                                        {{-- Kelas: badge indigo --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300">
                                                {{ $jadwal->kelas->nama }}
                                            </span>
                                        </td>

                                        {{-- Mata Pelajaran: teks hijau (emerald) semi-tebal --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-emerald-700 dark:text-emerald-400">
                                            {{ $jadwal->mapel->nama }}
                                        </td>

                                        {{-- Guru: teks oranye/amber --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-amber-800 dark:text-amber-300">
                                            {{ $jadwal->guru->user->name }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('admin.jadwal.edit', $jadwal) }}" class="text-blue-600 dark:text-blue-400 hover:underline mr-3">Edit</a>
                                            <button type="button" 
                                                x-data=""
                                                x-on:click.prevent="$dispatch('open-confirm-modal', {
                                                    title: 'Konfirmasi Hapus',
                                                    message: 'Apakah Anda yakin ingin menghapus jadwal ini?\n\nData yang sudah dihapus mungkin tidak dapat dikembalikan.',
                                                    action: '{{ route('admin.jadwal.destroy', $jadwal) }}',
                                                    method: 'DELETE',
                                                    confirmText: 'Hapus'
                                                })"
                                                class="text-red-600 dark:text-red-400 hover:underline">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400 mb-3">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                                @if($hasActiveFilters)
                                                    <p class="text-base font-medium text-gray-900 dark:text-gray-100">Tidak ada jadwal yang cocok</p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Coba sesuaikan atau reset filter pencarian Anda untuk melihat data jadwal.</p>
                                                    <div class="mt-4">
                                                        <a href="{{ route('admin.jadwal.index') }}" class="inline-flex items-center px-3.5 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-xs font-semibold rounded-md transition">
                                                            Reset Filter
                                                        </a>
                                                    </div>
                                                @else
                                                    <p class="text-base font-medium text-gray-900 dark:text-gray-100">Belum ada data jadwal</p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Data jadwal pelajaran yang ditambahkan akan tampil di sini.</p>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $jadwals->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
