<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Data Terhapus') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 dark:bg-green-900/30 dark:border-green-600 dark:text-green-300 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 dark:bg-red-900/30 dark:border-red-600 dark:text-red-300 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{!! session('error') !!}</span>
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
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
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
                                        <td colspan="{{ $jenis === 'siswa' || $jenis === 'kelas' ? 5 : 4 }}" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
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
    </div>
</x-app-layout>
