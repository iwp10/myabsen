<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Data Terhapus') }}
        </h2>
    </x-slot>

    <div class="py-12"
         x-data="{
             pageItems: [
                 @foreach($items as $item)
                     @php
                         $itemName = match ($jenis) {
                             'guru' => $item->user?->name ?? $item->nip ?? '-',
                             'siswa' => $item->user?->name ?? $item->nis ?? '-',
                             'kelas' => $item->nama,
                             'mapel' => $item->nama,
                         };
                     @endphp
                     { id: {{ $item->id }}, name: @js($itemName) },
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
             openPulihkanModal() {
                 $dispatch('open-confirm-massal-modal', {
                     title: 'Konfirmasi Pemulihan Data',
                     type: 'arsip-pulihkan',
                     action: '{{ route('admin.arsip.massal.pulihkan', ['jenis' => $jenis]) }}' + window.location.search,
                     method: 'POST',
                     confirmText: 'Pulihkan',
                     confirmColor: 'primary',
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
                    <!-- Navigasi Tab -->
                    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                        <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
                            @php
                                $tabs = [
                                    'guru' => 'Guru',
                                    'siswa' => 'Siswa',
                                    'kelas' => 'Kelas',
                                    'mapel' => 'Mata Pelajaran',
                                ];
                            @endphp
                            @foreach ($tabs as $key => $title)
                                @php
                                    $isActive = $jenis === $key;
                                    $count = $counts[$key] ?? 0;
                                @endphp
                                <a href="{{ route('admin.arsip.index', ['jenis' => $key]) }}"
                                   class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 {{ $isActive ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                    <span>{{ $title }}</span>
                                    <span class="px-2 py-0.5 text-xs rounded-full {{ $isActive ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                        {{ $count }}
                                    </span>
                                </a>
                            @endforeach
                        </nav>
                    </div>

                    <!-- Pencarian -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                        <h3 class="text-lg font-bold">
                            Data Terhapus: {{ $tabs[$jenis] ?? ucfirst($jenis) }}
                        </h3>
                        <form action="{{ route('admin.arsip.index') }}" method="GET" class="flex gap-2 w-full sm:w-auto">
                            <input type="hidden" name="jenis" value="{{ $jenis }}">
                            @php
                                $placeholder = match ($jenis) {
                                    'guru' => 'Cari nama atau NIP...',
                                    'siswa' => 'Cari nama atau NIS...',
                                    'kelas' => 'Cari nama kelas...',
                                    'mapel' => 'Cari nama atau kode mapel...',
                                    default => 'Cari...',
                                };
                            @endphp
                            <input type="text"
                                   name="search"
                                   value="{{ $search }}"
                                   placeholder="{{ $placeholder }}"
                                   class="w-full sm:w-64 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm dark:[color-scheme:dark]">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md text-sm transition">
                                Cari
                            </button>
                            @if ($search)
                                <a href="{{ route('admin.arsip.index', ['jenis' => $jenis]) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-3 rounded-md text-sm flex items-center justify-center transition">
                                    Reset
                                </a>
                            @endif
                        </form>
                    </div>

                    <!-- Tabel Data Terhapus -->
                    <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-4 py-3 whitespace-nowrap w-10">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox"
                                                x-ref="selectAllCheckbox"
                                                :checked="isAllSelected"
                                                @change="toggleSelectAll"
                                                aria-label="Pilih semua data di halaman ini"
                                                class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-blue-600 focus:ring-blue-500 dark:[color-scheme:dark]">
                                            <span class="sr-only">Pilih semua di halaman ini ({{ $items->count() }})</span>
                                        </div>
                                    </th>
                                    @if ($jenis === 'guru')
                                        <th scope="col" class="px-6 py-3">Nama Guru</th>
                                        <th scope="col" class="px-6 py-3">NIP</th>
                                        <th scope="col" class="px-6 py-3">Dihapus Pada</th>
                                        <th scope="col" class="px-6 py-3">Aksi</th>
                                    @elseif ($jenis === 'siswa')
                                        <th scope="col" class="px-6 py-3">Nama Siswa</th>
                                        <th scope="col" class="px-6 py-3">NIS</th>
                                        <th scope="col" class="px-6 py-3">Kelas</th>
                                        <th scope="col" class="px-6 py-3">Dihapus Pada</th>
                                        <th scope="col" class="px-6 py-3">Aksi</th>
                                    @elseif ($jenis === 'kelas')
                                        <th scope="col" class="px-6 py-3">Nama Kelas</th>
                                        <th scope="col" class="px-6 py-3">Jurusan</th>
                                        <th scope="col" class="px-6 py-3">Periode</th>
                                        <th scope="col" class="px-6 py-3">Dihapus Pada</th>
                                        <th scope="col" class="px-6 py-3">Aksi</th>
                                    @elseif ($jenis === 'mapel')
                                        <th scope="col" class="px-6 py-3">Nama Mata Pelajaran</th>
                                        <th scope="col" class="px-6 py-3">Kode</th>
                                        <th scope="col" class="px-6 py-3">Dihapus Pada</th>
                                        <th scope="col" class="px-6 py-3">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $item)
                                    @php
                                        $itemName = match ($jenis) {
                                            'guru' => $item->user?->name ?? $item->nip ?? '-',
                                            'siswa' => $item->user?->name ?? $item->nis ?? '-',
                                            'kelas' => $item->nama,
                                            'mapel' => $item->nama,
                                        };
                                        $label = ucfirst($jenis);
                                    @endphp
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                        :class="{ 'bg-blue-50/60 dark:bg-blue-950/20': selected.includes({{ $item->id }}) }">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <input type="checkbox"
                                                :value="{{ $item->id }}"
                                                x-model.number="selected"
                                                aria-label="Pilih {{ $label }} {{ $itemName }}"
                                                class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-blue-600 focus:ring-blue-500 dark:[color-scheme:dark]">
                                        </td>
                                        @if ($jenis === 'guru')
                                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                                {{ $item->user?->name ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->nip ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $item->deleted_at ? $item->deleted_at->format('d/m/Y H:i') : '-' }}
                                            </td>
                                        @elseif ($jenis === 'siswa')
                                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                                {{ $item->user?->name ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->nis }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($item->kelas)
                                                    {{ $item->kelas->nama }}
                                                    @if ($item->kelas->trashed())
                                                        <span class="text-xs text-red-500 font-semibold">(terhapus)</span>
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $item->deleted_at ? $item->deleted_at->format('d/m/Y H:i') : '-' }}
                                            </td>
                                        @elseif ($jenis === 'kelas')
                                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                                {{ $item->nama }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->jurusan?->nama ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->tahun_ajaran }} ({{ $item->semester }})</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $item->deleted_at ? $item->deleted_at->format('d/m/Y H:i') : '-' }}
                                            </td>
                                        @elseif ($jenis === 'mapel')
                                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                                {{ $item->nama }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->kode }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $item->deleted_at ? $item->deleted_at->format('d/m/Y H:i') : '-' }}
                                            </td>
                                        @endif

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button type="button"
                                                x-data=""
                                                x-on:click.prevent="$dispatch('open-confirm-modal', {
                                                    title: 'Konfirmasi Pemulihan',
                                                    message: 'Pulihkan {{ $label }} {{ addslashes($itemName) }}?',
                                                    action: '{{ route('admin.arsip.pulihkan', ['jenis' => $jenis, 'id' => $item->id]) }}',
                                                    method: 'POST',
                                                    confirmText: 'Pulihkan'
                                                })"
                                                class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                                Pulihkan
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $jenis === 'siswa' || $jenis === 'kelas' ? 6 : 5 }}" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            Tidak ada data terhapus.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginasi -->
                    <div class="mt-4">
                        {{ $items->links() }}
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
                        @click="openPulihkanModal"
                        :disabled="selected.length === 0"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium text-xs rounded-md shadow-sm transition disabled:opacity-50">
                    Pulihkan yang dipilih
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
