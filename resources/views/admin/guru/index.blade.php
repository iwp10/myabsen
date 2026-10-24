<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Kelola Guru') }}
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

            <x-ui.page-header title="Kelola Guru" :description="'Menampilkan ' . $gurus->total() . ' guru'">
                <x-slot:actions>
                    <a href="{{ route('admin.guru.create') }}" class="{{ \App\Support\Ui::tombol('primary') }} w-full sm:w-auto">
                        <x-ui.icon name="plus" size="sm" class="mr-1.5" />
                        Tambah Guru
                    </a>
                </x-slot:actions>
            </x-ui.page-header>

            {{-- Form Filter --}}
            <x-ui.filter-bar :action="route('admin.guru.index')">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3 w-full">
                    <div class="flex-1 min-w-0">
                        <label for="filter_search" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pencarian</label>
                        <x-text-input id="filter_search" type="text" name="search" :value="request('search')" placeholder="Cari nama atau NIP..." class="w-full" />
                    </div>
                    <div class="flex items-center gap-2 pt-1 w-full sm:w-auto">
                        <button type="submit" class="{{ \App\Support\Ui::tombol('primary') }} w-full sm:w-auto">
                            Cari
                        </button>
                        @if(request('search'))
                            <a href="{{ route('admin.guru.index') }}" class="{{ \App\Support\Ui::tombol('secondary') }} w-full sm:w-auto">
                                Reset
                            </a>
                        @endif
                    </div>
                </div>
            </x-ui.filter-bar>

            {{-- Tabel Daftar Guru --}}
            <x-ui.table>
                <thead class="{{ \App\Support\Ui::tableHeader() }}">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap">Nama Guru</th>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap">NIP / Username</th>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                    @forelse ($gurus as $guru)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                            <td class="stack-title px-6 py-4 font-bold text-slate-900 dark:text-white" data-label="Nama Guru">
                                {{ $guru->user->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-slate-700 dark:text-slate-300" data-label="NIP / Username">
                                {{ $guru->nip }}
                            </td>
                            <td class="stack-actions px-6 py-4 whitespace-nowrap text-right" data-label="Aksi">
                                <a href="{{ route('admin.guru.edit', $guru) }}" class="inline-flex items-center justify-center min-h-[44px] px-3 py-2 text-sm font-semibold text-blue-700 dark:text-blue-400 hover:underline">
                                    Edit
                                </a>
                                <button type="button" 
                                    x-data=""
                                    x-on:click.prevent="$dispatch('open-confirm-modal', {
                                        title: 'Konfirmasi Hapus',
                                        message: 'Apakah Anda yakin ingin menghapus guru ini?\n\nData yang sudah dihapus mungkin tidak dapat dikembalikan.',
                                        action: '{{ route('admin.guru.destroy', $guru) }}',
                                        method: 'DELETE',
                                        confirmText: 'Hapus'
                                    })"
                                    class="inline-flex items-center justify-center min-h-[44px] px-3 py-2 text-sm font-semibold text-red-700 dark:text-red-400 hover:underline">
                                    Hapus
                                </button>
                                <button type="button" 
                                    x-data=""
                                    x-on:click.prevent="$dispatch('open-confirm-modal', {
                                        title: 'Konfirmasi Reset Password',
                                        message: 'Apakah Anda yakin ingin mereset password guru ini ke password awal?',
                                        action: '{{ route('admin.guru.reset-password', $guru) }}',
                                        method: 'POST',
                                        confirmText: 'Reset Password'
                                    })"
                                    class="inline-flex items-center justify-center min-h-[44px] px-3 py-2 text-sm font-semibold text-amber-700 dark:text-amber-400 hover:underline">
                                    Reset Password
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center" data-label="Data Kosong">
                                <x-ui.empty-state
                                    title="Belum ada data guru"
                                    description="Data guru yang ditambahkan akan tampil di sini."
                                    icon="users"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-4">
                {{ $gurus->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
