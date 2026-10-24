<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Kelola Mata Pelajaran') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <x-alert type="success" :message="session('success')" />
            @endif

            <x-ui.page-header title="Kelola Mata Pelajaran" :description="'Menampilkan ' . $mapels->total() . ' mata pelajaran'">
                <x-slot:actions>
                    <a href="{{ route('admin.mapel.create') }}" class="{{ \App\Support\Ui::tombol('primary') }} w-full sm:w-auto">
                        <x-ui.icon name="plus" size="sm" class="mr-1.5" />
                        Tambah Mata Pelajaran
                    </a>
                </x-slot:actions>
            </x-ui.page-header>

            {{-- Form Filter --}}
            <x-ui.filter-bar :action="route('admin.mapel.index')">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3 w-full">
                    <div class="flex-1 min-w-0">
                        <label for="filter_search" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pencarian</label>
                        <x-text-input id="filter_search" type="text" name="search" :value="request('search')" placeholder="Cari nama atau kode mapel..." class="w-full" />
                    </div>
                    <div class="flex items-center gap-2 pt-1 w-full sm:w-auto">
                        <button type="submit" class="{{ \App\Support\Ui::tombol('primary') }} w-full sm:w-auto">
                            Cari
                        </button>
                        @if(request('search'))
                            <a href="{{ route('admin.mapel.index') }}" class="{{ \App\Support\Ui::tombol('secondary') }} w-full sm:w-auto">
                                Reset
                            </a>
                        @endif
                    </div>
                </div>
            </x-ui.filter-bar>

            {{-- Tabel Daftar Mata Pelajaran --}}
            <x-ui.table>
                <thead class="{{ \App\Support\Ui::tableHeader() }}">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap">Nama Mata Pelajaran</th>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap">Kode</th>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                    @forelse ($mapels as $mapel)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                            <td class="stack-title px-6 py-4 font-bold text-slate-900 dark:text-white" data-label="Nama Mata Pelajaran">
                                {{ $mapel->nama }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-slate-700 dark:text-slate-300" data-label="Kode">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-800 border border-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700 font-mono">
                                    {{ $mapel->kode }}
                                </span>
                            </td>
                            <td class="stack-actions px-6 py-4 whitespace-nowrap text-right" data-label="Aksi">
                                <a href="{{ route('admin.mapel.edit', $mapel) }}" class="inline-flex items-center justify-center min-h-[44px] px-3 py-2 text-sm font-semibold text-blue-700 dark:text-blue-400 hover:underline">
                                    Edit
                                </a>
                                <button type="button" 
                                    x-data=""
                                    x-on:click.prevent="$dispatch('open-confirm-modal', {
                                        title: 'Konfirmasi Hapus',
                                        message: 'Apakah Anda yakin ingin menghapus mata pelajaran ini?\n\nData yang sudah dihapus mungkin tidak dapat dikembalikan.',
                                        action: '{{ route('admin.mapel.destroy', $mapel) }}',
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
                            <td colspan="3" class="px-6 py-12 text-center" data-label="Data Kosong">
                                <x-ui.empty-state
                                    title="Belum ada data mata pelajaran"
                                    description="Data mata pelajaran yang ditambahkan akan tampil di sini."
                                    icon="book"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-4">
                {{ $mapels->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
