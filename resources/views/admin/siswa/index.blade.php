<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Kelola Siswa') }}
        </h2>
    </x-slot>

    <div class="py-12"
         x-data="{
             pageItems: [
                 @foreach($siswas as $siswa)
                     { id: {{ $siswa->id }}, name: @js($siswa->user->name), hasHistory: {{ $siswa->detail_absensi_exists ? 'true' : 'false' }} },
                 @endforeach
             ],
             selected: [],

             get selectedItems() {
                 return this.pageItems.filter(item => this.selected.includes(item.id));
             },
             get isAllSelected() {
                 return this.pageItems.length > 0 && this.selected.length === this.pageItems.length;
             },
             get isIndeterminate() {
                 return this.selected.length > 0 && this.selected.length < this.pageItems.length;
             },
             toggleSelectAll() {
                 if (this.isAllSelected) {
                     this.selected = [];
                 } else {
                     this.selected = this.pageItems.map(item => item.id);
                 }
             },
             clearSelection() {
                 this.selected = [];
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
         }"
         x-effect="if ($refs.selectAllCheckbox) { $refs.selectAllCheckbox.indeterminate = isIndeterminate; }">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4">
                    <x-alert type="success" :message="session('success')" />
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-4">
                    <x-alert type="warning">
                        {!! session('warning') !!}
                    </x-alert>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4">
                    <x-alert type="error">
                        {!! session('error') !!}
                    </x-alert>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                        <div>
                            <h3 class="text-lg font-bold">Daftar Siswa</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Menampilkan {{ $siswas->total() }} siswa</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.siswa.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm text-center transition">
                                Tambah Siswa
                            </a>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 px-4 py-3 rounded text-sm">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-6 p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700/40">
                        <form action="{{ route('admin.siswa.index') }}" method="GET" class="flex flex-col md:flex-row items-stretch md:items-end gap-3">
                            <div class="w-full md:w-64">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Kelas</label>
                                <select name="kelas_id" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
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
                                </select>
                            </div>

                            <div class="w-full md:w-48">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Urutkan</label>
                                <select name="urut" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                    <option value="nama_asc" {{ in_array($urut, ['nama_asc', 'nama-az']) ? 'selected' : '' }}>Nama A-Z (bawaan)</option>
                                    <option value="nama_desc" {{ in_array($urut, ['nama_desc', 'nama-za']) ? 'selected' : '' }}>Nama Z-A</option>
                                    <option value="nis" {{ $urut === 'nis' ? 'selected' : '' }}>NIS</option>
                                </select>
                            </div>

                            <div class="w-full md:flex-1">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Pencarian</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIS..." class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md text-sm transition">
                                    Cari
                                </button>
                                @php
                                    $hasActiveFilters = request()->filled('search') || request()->filled('kelas_id') || (request()->filled('urut') && !in_array(request('urut'), ['nama_asc', 'nama-az']));
                                @endphp
                                @if($hasActiveFilters)
                                    <a href="{{ route('admin.siswa.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-3 rounded-md text-sm flex items-center justify-center transition">
                                        Reset
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                    
                    <div class="mb-6 p-4 sm:p-5 border-2 border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 shadow-xs">
                        <h4 class="font-bold text-base text-slate-900 dark:text-white mb-3">Impor Data Siswa</h4>
                        <form action="{{ route('admin.siswa.import') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-start">
                            @csrf
                            <div class="sm:col-span-4 min-w-0">
                                <label for="import_kelas_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Kelas</label>
                                <select id="import_kelas_id" name="kelas_id" required class="w-full min-w-0 border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 dark:[color-scheme:dark] focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 rounded-lg text-sm min-h-[44px] py-2 pl-3 pr-8 font-medium">
                                    <option value="">Pilih Kelas</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}">{{ $k->nama }}</option>
                                    @endforeach
                                </select>
                                @error('kelas_id') <span class="text-red-700 dark:text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div class="sm:col-span-6 min-w-0">
                                <label for="import_file" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">File Excel/CSV</label>
                                <x-ui.file-input id="import_file" name="file" accept=".xlsx,.xls,.csv" required help="Format .xlsx, .xls, atau .csv. Maksimal sesuai batas server." />
                                @error('file') <span class="text-red-700 dark:text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div class="sm:col-span-2 min-w-0">
                                <span class="hidden sm:block text-xs font-semibold mb-1 invisible" aria-hidden="true">&nbsp;</span>
                                <button type="submit" class="w-full min-h-[44px] inline-flex items-center justify-center bg-emerald-700 hover:bg-emerald-800 text-white dark:bg-emerald-600 dark:hover:bg-emerald-500 font-bold py-2.5 px-4 rounded-lg text-sm transition shadow-xs">
                                    Impor
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-4 py-3 whitespace-nowrap w-10">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox"
                                                x-ref="selectAllCheckbox"
                                                :checked="isAllSelected"
                                                @change="toggleSelectAll"
                                                aria-label="Pilih semua siswa di halaman ini"
                                                class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-blue-600 focus:ring-blue-500 dark:[color-scheme:dark]">
                                            <span class="sr-only">Pilih semua di halaman ini ({{ $siswas->count() }})</span>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5">
                                            <span>No.</span>
                                            <span class="text-[11px] font-normal normal-case text-gray-500 dark:text-gray-400">
                                                (Pilih semua di halaman ini: {{ $siswas->count() }})
                                            </span>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 whitespace-nowrap">Nama</th>
                                    <th scope="col" class="px-6 py-3 whitespace-nowrap">NIS / Username</th>
                                    <th scope="col" class="px-6 py-3 whitespace-nowrap">Kelas</th>
                                    <th scope="col" class="px-6 py-3 whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($siswas as $siswa)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                        :class="{ 'bg-blue-50/60 dark:bg-blue-950/20': selected.includes({{ $siswa->id }}) }">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <input type="checkbox"
                                                :value="{{ $siswa->id }}"
                                                x-model.number="selected"
                                                aria-label="Pilih siswa {{ $siswa->user->name }}"
                                                class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-blue-600 focus:ring-blue-500 dark:[color-scheme:dark]">
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                            {{ $siswas->firstItem() ? $siswas->firstItem() + $loop->index : $loop->iteration }}
                                        </td>
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                            {{ $siswa->user->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $siswa->nis }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $siswa->kelas->tingkat }} {{ $siswa->kelas->nama }} - {{ $siswa->kelas->jurusan->kode }}
                                        </td>
                                        <td class="px-6 py-4 flex items-center space-x-3 whitespace-nowrap">
                                            <a href="{{ route('admin.siswa.edit', $siswa) }}" class="text-blue-600 dark:text-blue-400 hover:underline">Edit</a>
                                            <button type="button" 
                                                x-data=""
                                                x-on:click.prevent="$dispatch('open-confirm-modal', {
                                                    title: 'Konfirmasi Hapus',
                                                    message: 'Apakah Anda yakin ingin menghapus siswa ini?\n\nData yang sudah dihapus mungkin tidak dapat dikembalikan.',
                                                    action: '{{ route('admin.siswa.destroy', $siswa) }}',
                                                    method: 'DELETE',
                                                    confirmText: 'Hapus'
                                                })"
                                                class="text-red-600 dark:text-red-400 hover:underline">
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
                                                class="text-yellow-600 dark:text-yellow-400 hover:underline">
                                                Reset Password
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400 mb-3">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                                    </svg>
                                                </div>
                                                <p class="text-base font-medium text-gray-900 dark:text-gray-100">Belum ada data siswa</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Data siswa yang ditambahkan atau diimpor akan tampil di sini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $siswas->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Bilah Aksi Massal (Floating Bottom Bar) -->
        <div x-show="selected.length > 0"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4"
             style="display: none;"
             class="fixed bottom-4 inset-x-4 sm:inset-x-auto sm:right-8 sm:left-auto sm:max-w-xl z-40 bg-white/95 dark:bg-gray-800/95 backdrop-blur shadow-2xl rounded-xl border border-gray-200 dark:border-gray-700 p-3 sm:p-4 flex flex-col sm:flex-row items-center justify-between gap-3">

            <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 w-full sm:w-auto">
                <span class="inline-flex items-center justify-center size-6 rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 font-bold text-xs" x-text="selected.length"></span>
                <span class="font-medium"><span x-text="selected.length"></span> dipilih</span>
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <button type="button" @click="clearSelection" class="text-xs text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 underline focus:outline-none">
                    Batalkan pilihan
                </button>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <button type="button"
                        @click="openResetModal"
                        :disabled="selected.length === 0"
                        class="px-3 py-2 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white font-medium text-xs rounded-md shadow-sm transition disabled:opacity-50">
                    Reset password yang dipilih
                </button>
                <button type="button"
                        @click="openHapusModal"
                        :disabled="selected.length === 0"
                        class="px-3 py-2 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-medium text-xs rounded-md shadow-sm transition disabled:opacity-50">
                    Hapus yang dipilih
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
