<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Kelola Siswa') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12 pb-32 sm:pb-24"
         x-data="pilihanMassal(
             {{ json_encode($siswas->pluck('id')->map(fn($id) => (string)$id)->values()) }},
             [],
             {
                 pageItems: [
                     @foreach($siswas as $siswa)
                         { id: {{ $siswa->id }}, name: @js($siswa->user->name), hasHistory: {{ $siswa->detail_absensi_exists ? 'true' : 'false' }} },
                     @endforeach
                 ],
                 get selectedItems() {
                     return this.pageItems.filter(item => this.terpilih.includes(String(item.id)));
                 },
                 openResetModal() {
                     $dispatch('open-confirm-massal-modal', {
                         title: 'Konfirmasi Reset Password Siswa',
                         type: 'siswa-reset',
                         action: '{{ route('admin.siswa.massal.reset-password') }}' + window.location.search,
                         method: 'POST',
                         confirmText: 'Reset Password',
                         confirmColor: 'warning',
                         items: this.selectedItems
                     });
                 },
                 openHapusModal() {
                     $dispatch('open-confirm-massal-modal', {
                         title: 'Konfirmasi Hapus Siswa',
                         type: 'siswa-hapus',
                         action: '{{ route('admin.siswa.massal.hapus') }}' + window.location.search,
                         method: 'POST',
                         confirmText: 'Hapus Siswa',
                         confirmColor: 'danger',
                         items: this.selectedItems
                     });
                 }
             }
         )">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <x-alert type="success" :message="session('success')" />
            @endif

            @if (session('warning'))
                <x-alert type="warning">
                    {!! session('warning') !!}
                </x-alert>
            @endif

            @if (session('error'))
                <x-alert type="error">
                    {!! session('error') !!}
                </x-alert>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 dark:bg-red-900/30 border-2 border-red-400 dark:border-red-600 text-red-900 dark:text-red-200 px-4 py-3 rounded-xl text-sm" role="alert">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-ui.page-header title="Kelola Siswa" :description="'Menampilkan ' . $siswas->total() . ' siswa'">
                <x-slot:actions>
                    <a href="{{ route('admin.siswa.create') }}" class="{{ \App\Support\Ui::tombol('primary') }} w-full sm:w-auto">
                        <x-ui.icon name="plus" size="sm" class="mr-1.5" />
                        Tambah Siswa
                    </a>
                </x-slot:actions>
            </x-ui.page-header>

            {{-- Form Filter Terpadu --}}
            <x-ui.filter-bar :action="route('admin.siswa.index')">
                <div class="flex flex-col md:flex-row items-stretch md:items-end gap-3 w-full">
                    <div class="w-full md:w-64 min-w-0">
                        <label for="filter_kelas_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kelas</label>
                        <x-ui.select id="filter_kelas_id" name="kelas_id">
                            <option value="">Semua kelas</option>
                            @foreach($kelasListGrouped as $group)
                                <optgroup label="{{ $group['label'] }}">
                                    @foreach($group['items'] as $k)
                                        <option value="{{ $k->id }}" {{ (string)$filterKelasId === (string)$k->id ? 'selected' : '' }}>
                                            {{ $k->tingkat }} {{ $k->nama }} - {{ $k->jurusan->kode }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div class="w-full md:w-52 min-w-0">
                        <label for="filter_urut" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Urutan</label>
                        <x-ui.select id="filter_urut" name="urut">
                            <option value="nama_asc" {{ in_array($urut, ['nama_asc', 'nama-az']) ? 'selected' : '' }}>Nama A-Z (bawaan)</option>
                            <option value="nama_desc" {{ in_array($urut, ['nama_desc', 'nama-za']) ? 'selected' : '' }}>Nama Z-A</option>
                            <option value="nis" {{ $urut === 'nis' ? 'selected' : '' }}>NIS</option>
                        </x-ui.select>
                    </div>

                    <div class="w-full md:flex-1 min-w-0">
                        <label for="filter_search" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pencarian</label>
                        <x-text-input id="filter_search" type="text" name="search" :value="request('search')" placeholder="Cari nama atau NIS..." class="w-full" />
                    </div>

                    <div class="flex items-center gap-2 pt-1 w-full md:w-auto">
                        <button type="submit" class="{{ \App\Support\Ui::tombol('primary') }} w-full md:w-auto">
                            Cari
                        </button>
                        @php
                            $hasActiveFilters = request()->filled('search') || request()->filled('kelas_id') || (request()->filled('urut') && !in_array(request('urut'), ['nama_asc', 'nama-az']));
                        @endphp
                        @if($hasActiveFilters)
                            <a href="{{ route('admin.siswa.index') }}" class="{{ \App\Support\Ui::tombol('secondary') }} w-full md:w-auto">
                                Reset
                            </a>
                        @endif
                    </div>
                </div>
            </x-ui.filter-bar>

            {{-- Kartu Impor Siswa --}}
            <x-ui.card>
                <h4 class="font-bold text-base text-slate-900 dark:text-white mb-3">Impor Data Siswa</h4>
                <form action="{{ route('admin.siswa.import') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-start">
                    @csrf
                    <div class="sm:col-span-4 min-w-0">
                        <label for="import_kelas_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Kelas</label>
                        <x-ui.select id="import_kelas_id" name="kelas_id" required>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @endforeach
                        </x-ui.select>
                        @error('kelas_id') <span class="text-red-700 dark:text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-6 min-w-0">
                        <label for="import_file" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">File Excel/CSV</label>
                        <x-ui.file-input id="import_file" name="file" accept=".xlsx,.xls,.csv" required help="Format .xlsx, .xls, atau .csv. Maksimal sesuai batas server." />
                        @error('file') <span class="text-red-700 dark:text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-2 min-w-0">
                        <span class="hidden sm:block text-xs font-semibold mb-1 invisible" aria-hidden="true">&nbsp;</span>
                        <button type="submit" class="w-full min-h-[44px] inline-flex items-center justify-center bg-emerald-700 hover:bg-emerald-800 text-white dark:bg-emerald-500 dark:hover:bg-emerald-400 dark:text-slate-950 font-bold py-2.5 px-4 rounded-xl text-sm transition shadow-xs">
                            Impor
                        </button>
                    </div>
                </form>
            </x-ui.card>

            {{-- Bilah Pilih Semua di Layar HP --}}
            <div class="block md:hidden bg-white dark:bg-slate-900 border-2 border-slate-300 dark:border-slate-600 rounded-xl p-3 shadow-xs">
                <x-ui.pilih-semua aria-label="Pilih semua siswa di halaman ini" label="Pilih semua di halaman ini" :count="$siswas->count()" />
            </div>

            {{-- Tabel Daftar Siswa --}}
            <x-ui.table>
                <thead class="{{ \App\Support\Ui::tableHeader() }}">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 whitespace-nowrap w-12">
                            <x-ui.pilih-semua aria-label="Pilih semua siswa di halaman ini" :count="$siswas->count()" />
                        </th>
                        <th scope="col" class="px-4 py-3.5 whitespace-nowrap w-16">
                            No.
                        </th>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap">Nama Siswa</th>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap">NIS / Username</th>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap">Kelas</th>
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                    @forelse ($siswas as $siswa)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors"
                            :class="{ 'ring-2 ring-blue-500 dark:ring-blue-400 bg-blue-50/40 dark:bg-blue-950/20': terpilih.includes(String('{{ $siswa->id }}')) }">
                            <td class="stack-checkbox px-4 py-4 whitespace-nowrap" data-label="Pilih">
                                <x-ui.pilih-baris :id="$siswa->id" name="ids[]" :aria-label="'Pilih siswa ' . $siswa->user->name" />
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-slate-700 dark:text-slate-300 tabular-nums font-semibold" data-label="No.">
                                {{ $siswas->firstItem() ? $siswas->firstItem() + $loop->index : $loop->iteration }}
                            </td>
                            <td class="stack-title px-6 py-4 font-bold text-slate-900 dark:text-white" data-label="Nama Siswa">
                                {{ $siswa->user->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-slate-700 dark:text-slate-300" data-label="NIS / Username">
                                {{ $siswa->nis }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Kelas">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700">
                                    {{ $siswa->kelas->tingkat }} {{ $siswa->kelas->nama }} - {{ $siswa->kelas->jurusan->kode }}
                                </span>
                            </td>
                            <td class="stack-actions px-6 py-4 whitespace-nowrap text-right" data-label="Aksi">
                                <a href="{{ route('admin.siswa.edit', $siswa) }}" class="inline-flex items-center justify-center min-h-[44px] px-3 py-2 text-sm font-semibold text-blue-700 dark:text-blue-400 hover:underline">
                                    Edit
                                </a>
                                <button type="button" 
                                    x-data=""
                                    x-on:click.prevent="$dispatch('open-confirm-modal', {
                                        title: 'Konfirmasi Hapus',
                                        message: 'Apakah Anda yakin ingin menghapus siswa ini?\n\nData yang sudah dihapus mungkin tidak dapat dikembalikan.',
                                        action: '{{ route('admin.siswa.destroy', $siswa) }}',
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
                                        message: 'Apakah Anda yakin ingin mereset password siswa ini ke password awal?',
                                        action: '{{ route('admin.siswa.reset-password', $siswa) }}',
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
                            <td colspan="6" class="px-6 py-12 text-center" data-label="Data Kosong">
                                <x-ui.empty-state
                                    title="Belum ada data siswa"
                                    description="Data siswa yang ditambahkan atau diimpor akan tampil di sini."
                                    icon="users"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-4">
                {{ $siswas->links() }}
            </div>
        </div>

        {{-- Bilah Aksi Massal (Floating Bottom Bar) Melekat di Bawah Layar --}}
        <div x-show="terpilih.length > 0"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4"
             style="display: none;"
             class="fixed bottom-0 inset-x-0 sm:bottom-4 sm:inset-x-auto sm:right-8 sm:left-auto sm:max-w-xl z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-2xl rounded-t-2xl sm:rounded-xl border-t-2 sm:border-2 border-slate-300 dark:border-slate-600 p-4 pb-[calc(1rem+env(safe-area-inset-bottom,0px))] flex flex-col sm:flex-row items-center justify-between gap-3">

            <div class="flex items-center gap-2 text-sm text-slate-800 dark:text-slate-200 w-full sm:w-auto">
                <span class="inline-flex items-center justify-center size-6 rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300 font-bold text-xs" x-text="terpilih.length"></span>
                <span class="font-semibold"><span x-text="terpilih.length"></span> dipilih</span>
                <span class="text-slate-600 dark:text-slate-400">|</span>
                <button type="button" @click="reset()" class="text-xs font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white underline focus:outline-none">
                    Batalkan pilihan
                </button>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <button type="button"
                        @click="openResetModal"
                        :disabled="terpilih.length === 0"
                        class="min-h-[44px] px-3.5 py-2 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white dark:bg-amber-500 dark:hover:bg-amber-400 dark:text-slate-950 font-semibold text-xs rounded-xl shadow-xs transition disabled:opacity-50">
                    Reset password yang dipilih
                </button>
                <button type="button"
                        @click="openHapusModal"
                        :disabled="terpilih.length === 0"
                        class="min-h-[44px] px-3.5 py-2 bg-red-700 hover:bg-red-800 active:bg-red-900 text-white dark:bg-red-600 dark:hover:bg-red-500 font-semibold text-xs rounded-xl shadow-xs transition disabled:opacity-50">
                    Hapus yang dipilih
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
