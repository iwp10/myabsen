<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Kelola Jadwal') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <x-alert type="success" :message="session('success')" />
            @endif

            @if(session('error'))
                <x-alert type="error" :message="session('error')" />
            @endif

            @if($errors->any())
                <div class="bg-red-100 dark:bg-red-900/30 border-2 border-red-400 dark:border-red-600 text-red-900 dark:text-red-200 px-4 py-3 rounded-xl text-sm" role="alert">
                    <p class="font-semibold text-sm">Terjadi kesalahan pada filter jadwal:</p>
                    <ul class="mt-1 list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-ui.page-header title="Kelola Jadwal" :description="'Menampilkan ' . $jadwals->total() . ' jadwal' . ($activePeriode['tahun_ajaran'] ? ' (Periode Aktif: ' . $activePeriode['tahun_ajaran'] . ' - ' . $activePeriode['semester'] . ')' : '')">
                <x-slot:actions>
                    <a href="{{ route('admin.jadwal.create') }}" class="{{ \App\Support\Ui::tombol('primary') }} w-full sm:w-auto">
                        <x-ui.icon name="plus" size="sm" class="mr-1.5" />
                        Tambah Jadwal
                    </a>
                </x-slot:actions>
            </x-ui.page-header>

            {{-- Form Filter Terpadu --}}
            @php
                $hasActiveFilters = request()->hasAny(['search', 'periode', 'tahun_ajaran', 'semester', 'hari', 'kelas_id', 'guru_id', 'mapel_id', 'jam_mulai_dari', 'jam_mulai_sampai', 'jam_dari', 'jam_sampai'])
                    && (request('search') || request('periode') || request('tahun_ajaran') || request('semester') || request('hari') || request('kelas_id') || request('guru_id') || request('mapel_id') || request('jam_mulai_dari') || request('jam_mulai_sampai') || request('jam_dari') || request('jam_sampai'));
            @endphp
            <x-ui.filter-bar :action="route('admin.jadwal.index')" formClass="space-y-4 w-full">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    {{-- Filter Periode --}}
                    <div>
                        <label for="filter_periode" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Periode</label>
                        <x-ui.select id="filter_periode" name="periode">
                            <option value="">Semua periode</option>
                            @foreach($daftarPeriode as $item)
                                <option value="{{ $item['value'] }}" {{ $filterPeriode === $item['value'] ? 'selected' : '' }}>
                                    {{ $item['label'] }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    {{-- Filter Hari --}}
                    <div>
                        <label for="filter_hari" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Hari</label>
                        <x-ui.select id="filter_hari" name="hari">
                            <option value="">Semua Hari</option>
                            @foreach($daftarHariOptions as $val => $lbl)
                                <option value="{{ $val }}" {{ strtolower((string)$filterHari) === $val ? 'selected' : '' }}>
                                    {{ $lbl }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    {{-- Filter Kelas --}}
                    <div>
                        <label for="filter_kelas_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kelas</label>
                        <x-ui.select id="filter_kelas_id" name="kelas_id">
                            <option value="">Semua Kelas</option>
                            @foreach($daftarKelas as $k)
                                <option value="{{ $k->id }}" {{ (string)$filterKelasId === (string)$k->id ? 'selected' : '' }}>
                                    {{ $k->nama }} ({{ $k->tahun_ajaran }} - {{ $k->semester }})
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    {{-- Filter Guru --}}
                    <div>
                        <label for="filter_guru_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Guru</label>
                        <x-ui.select id="filter_guru_id" name="guru_id">
                            <option value="">Semua Guru</option>
                            @foreach($daftarGuru as $g)
                                <option value="{{ $g->id }}" {{ (string)$filterGuruId === (string)$g->id ? 'selected' : '' }}>
                                    {{ $g->user->name }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    {{-- Filter Mapel --}}
                    <div>
                        <label for="filter_mapel_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mata Pelajaran</label>
                        <x-ui.select id="filter_mapel_id" name="mapel_id">
                            <option value="">Semua Mapel</option>
                            @foreach($daftarMapel as $m)
                                <option value="{{ $m->id }}" {{ (string)$filterMapelId === (string)$m->id ? 'selected' : '' }}>
                                    {{ $m->nama }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    {{-- Filter Jam Mulai Dari --}}
                    <div>
                        <label for="filter_jam_mulai_dari" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jam mulai dari</label>
                        <x-text-input id="filter_jam_mulai_dari" type="time" name="jam_mulai_dari" :value="$filterJamMulaiDari" class="w-full" />
                    </div>

                    {{-- Filter Jam Mulai Sampai --}}
                    <div>
                        <label for="filter_jam_mulai_sampai" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Sampai jam</label>
                        <x-text-input id="filter_jam_mulai_sampai" type="time" name="jam_mulai_sampai" :value="$filterJamMulaiSampai" class="w-full" />
                    </div>

                    {{-- Filter Pencarian Kata Kunci --}}
                    <div>
                        <label for="filter_search" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pencarian</label>
                        <x-text-input id="filter_search" type="text" name="search" :value="request('search')" placeholder="Cari jadwal..." class="w-full" />
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <button type="submit" class="{{ \App\Support\Ui::tombol('primary') }} w-full sm:w-auto">
                        Terapkan Filter
                    </button>
                    @if($hasActiveFilters)
                        <a href="{{ route('admin.jadwal.index') }}" class="{{ \App\Support\Ui::tombol('secondary') }} w-full sm:w-auto">
                            Reset
                        </a>
                    @endif
                </div>
            </x-ui.filter-bar>

            {{-- Tabel Daftar Jadwal --}}
            <x-ui.table>
                <thead class="{{ \App\Support\Ui::tableHeader() }}">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap">T.A</th>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap">Semester</th>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap">Hari</th>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap">Jam</th>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap">Kelas</th>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap">Mata Pelajaran</th>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap">Guru</th>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                    @forelse ($jadwals as $jadwal)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                            {{-- Periode: T.A --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400 font-medium order-6 md:order-none" data-label="Tahun Ajaran">
                                {{ $jadwal->tahun_ajaran }}
                            </td>

                            {{-- Semester --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm order-7 md:order-none" data-label="Semester">
                                <span class="{{ \App\Support\Ui::badgeSemester($jadwal->semester) }}">
                                    {{ $jadwal->semester }}
                                </span>
                            </td>

                            {{-- Hari: Di HP tampil sebagai bagian judul kartu --}}
                            <td class="stack-title px-6 py-4 whitespace-nowrap order-1 md:order-none" data-label="Hari">
                                <div class="flex items-center justify-between w-full">
                                    <span class="{{ \App\Support\Ui::badgeHari($jadwal->hari) }}">
                                        {{ ucfirst($jadwal->hari) }}
                                    </span>
                                    <span class="font-mono tabular-nums text-sm font-bold text-teal-700 dark:text-teal-400 md:hidden">
                                        {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                                    </span>
                                </div>
                            </td>

                            {{-- Jam --}}
                            <td class="px-6 py-4 whitespace-nowrap font-mono tabular-nums text-sm font-bold text-teal-700 dark:text-teal-400 order-2 md:order-none hidden md:table-cell" data-label="Jam Pelajaran">
                                {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                            </td>

                            {{-- Kelas --}}
                            <td class="px-6 py-4 whitespace-nowrap order-3 md:order-none" data-label="Kelas">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300">
                                    {{ $jadwal->kelas->nama }}
                                </span>
                            </td>

                            {{-- Mata Pelajaran --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-emerald-700 dark:text-emerald-400 order-4 md:order-none" data-label="Mata Pelajaran">
                                {{ $jadwal->mapel->nama }}
                            </td>

                            {{-- Guru --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-amber-800 dark:text-amber-300 order-5 md:order-none" data-label="Guru">
                                {{ $jadwal->guru->user->name }}
                            </td>

                            {{-- Aksi --}}
                            <td class="stack-actions px-6 py-4 whitespace-nowrap text-right order-last" data-label="Aksi">
                                <a href="{{ route('admin.jadwal.edit', $jadwal) }}" class="inline-flex items-center justify-center min-h-[44px] px-3 py-2 text-sm font-semibold text-blue-700 dark:text-blue-400 hover:underline">
                                    Edit
                                </a>
                                <button type="button" 
                                    x-data=""
                                    x-on:click.prevent="$dispatch('open-confirm-modal', {
                                        title: 'Konfirmasi Hapus',
                                        message: 'Apakah Anda yakin ingin menghapus jadwal ini?\n\nData yang sudah dihapus mungkin tidak dapat dikembalikan.',
                                        action: '{{ route('admin.jadwal.destroy', $jadwal) }}',
                                        method: 'DELETE',
                                        confirmText: 'Hapus'
                                    })"
                                    class="inline-flex items-center justify-center min-h-[44px] px-3 py-2 text-sm font-semibold text-red-700 dark:text-red-400 hover:underline">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center" data-label="Data Kosong">
                                <x-ui.empty-state
                                    title="Belum ada data jadwal"
                                    description="Data jadwal pelajaran yang ditambahkan akan tampil di sini."
                                    icon="calendar"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-4">
                {{ $jadwals->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
