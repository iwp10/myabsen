<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Kelola Kelas') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <x-alert type="success" :message="session('success')" />
            @endif

            @if (session('error'))
                <x-alert type="error" :message="session('error')" />
            @endif

            <x-ui.page-header title="Kelola Kelas" :description="'Menampilkan ' . $kelas->total() . ' kelas'">
                <x-slot:actions>
                    <a href="{{ route('admin.kelas.create') }}" class="{{ \App\Support\Ui::tombol('primary') }} w-full sm:w-auto">
                        <x-ui.icon name="plus" size="sm" class="mr-1.5" />
                        Tambah Kelas
                    </a>
                </x-slot:actions>
            </x-ui.page-header>

            {{-- Filter Terpadu --}}
            <x-ui.filter-bar :action="route('admin.kelas.index')">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3 w-full">
                    <div class="w-full sm:w-56 min-w-0">
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

                    <div class="w-full sm:w-44 min-w-0">
                        <label for="filter_tingkat" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tingkat</label>
                        <x-ui.select id="filter_tingkat" name="tingkat">
                            <option value="">Semua tingkat</option>
                            @foreach($daftarTingkat as $t)
                                <option value="{{ $t }}" {{ (string)$filterTingkat === (string)$t ? 'selected' : '' }}>
                                    Tingkat {{ $t }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div class="flex-1 min-w-0">
                        <label for="filter_search" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pencarian</label>
                        <x-text-input id="filter_search" type="text" name="search" :value="request('search')" placeholder="Cari nama, jurusan..." class="w-full" />
                    </div>

                    <div class="flex items-center gap-2 pt-1 w-full sm:w-auto">
                        <button type="submit" class="{{ \App\Support\Ui::tombol('primary') }} w-full sm:w-auto">
                            Cari
                        </button>
                        @php
                            $hasActiveFilters = request()->hasAny(['search', 'periode', 'tahun_ajaran', 'semester', 'tingkat']) && (request('search') || request('periode') || request('tahun_ajaran') || request('semester') || request('tingkat'));
                        @endphp
                        @if($hasActiveFilters)
                            <a href="{{ route('admin.kelas.index') }}" class="{{ \App\Support\Ui::tombol('secondary') }} w-full sm:w-auto">
                                Reset
                            </a>
                        @endif
                    </div>
                </div>
            </x-ui.filter-bar>

            {{-- Tabel Daftar Kelas --}}
            <x-ui.table>
                <thead class="{{ \App\Support\Ui::tableHeader() }}">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap">Nama Kelas</th>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap">Tingkat</th>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap">Jurusan</th>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap">Tahun Ajaran</th>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap">Semester</th>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                    @forelse ($kelas as $k)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                            <td class="stack-title px-6 py-4 font-bold text-slate-900 dark:text-white" data-label="Nama Kelas">
                                {{ $k->nama }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-slate-700 dark:text-slate-300" data-label="Tingkat">
                                Tingkat {{ $k->tingkat }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-slate-700 dark:text-slate-300" data-label="Jurusan">
                                {{ $k->jurusan->nama }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-300" data-label="Tahun Ajaran">
                                {{ $k->tahun_ajaran }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Semester">
                                <span class="{{ \App\Support\Ui::badgeSemester($k->semester) }}">
                                    {{ $k->semester }}
                                </span>
                            </td>
                            <td class="stack-actions px-6 py-4 whitespace-nowrap text-right" data-label="Aksi">
                                <a href="{{ route('admin.kelas.edit', $k) }}" class="inline-flex items-center justify-center min-h-[44px] px-3 py-2 text-sm font-semibold text-blue-700 dark:text-blue-400 hover:underline">
                                    Edit
                                </a>
                                <button type="button" 
                                    x-data=""
                                    x-on:click.prevent="$dispatch('open-confirm-modal', {
                                        title: 'Konfirmasi Hapus',
                                        message: 'Apakah Anda yakin ingin menghapus kelas ini?\n\nData yang sudah dihapus mungkin tidak dapat dikembalikan.',
                                        action: '{{ route('admin.kelas.destroy', $k) }}',
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
                            <td colspan="6" class="px-6 py-12 text-center" data-label="Data Kosong">
                                <x-ui.empty-state
                                    title="Belum ada data kelas"
                                    description="Data kelas rombongan belajar yang ditambahkan akan tampil di sini."
                                    icon="building"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-4">
                {{ $kelas->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
