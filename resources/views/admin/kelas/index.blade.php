<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Kelola Kelas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                        <div class="flex flex-col">
                            <h3 class="text-lg font-bold">Daftar Kelas</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Menampilkan {{ $kelas->total() }} kelas</p>
                            @if($activePeriode['tahun_ajaran'])
                                <span class="text-xs text-green-600 dark:text-green-400 mt-1">Periode Aktif: {{ $activePeriode['tahun_ajaran'] }} - {{ $activePeriode['semester'] }}</span>
                            @endif
                        </div>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                            <form action="{{ route('admin.kelas.index') }}" method="GET" class="flex flex-col sm:flex-row gap-2 w-full">
                                <select name="periode" class="w-full sm:w-56 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                    <option value="">Semua periode</option>
                                    @foreach($daftarPeriode as $item)
                                        <option value="{{ $item['value'] }}" {{ $filterPeriode === $item['value'] ? 'selected' : '' }}>
                                            {{ $item['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <select name="tingkat" class="w-full sm:w-36 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:[color-scheme:dark] focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                    <option value="">Semua tingkat</option>
                                    @foreach($daftarTingkat as $t)
                                        <option value="{{ $t }}" {{ (string)$filterTingkat === (string)$t ? 'selected' : '' }}>
                                            Tingkat {{ $t }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, tingkat, jurusan..." class="w-full sm:w-48 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md text-sm transition">Cari</button>
                                @if(request()->hasAny(['search', 'periode', 'tahun_ajaran', 'semester', 'tingkat']) && (request('search') || request('periode') || request('tahun_ajaran') || request('semester') || request('tingkat')))
                                    <a href="{{ route('admin.kelas.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-3 rounded-md text-sm flex items-center justify-center transition">Reset</a>
                                @endif
                            </form>
                            <a href="{{ route('admin.kelas.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm text-center transition">
                                Tambah Kelas
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

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3 whitespace-nowrap">Nama</th>
                                    <th scope="col" class="px-6 py-3 whitespace-nowrap">Tingkat</th>
                                    <th scope="col" class="px-6 py-3 whitespace-nowrap">Jurusan</th>
                                    <th scope="col" class="px-6 py-3 whitespace-nowrap">Tahun Ajaran</th>
                                    <th scope="col" class="px-6 py-3 whitespace-nowrap">Semester</th>
                                    <th scope="col" class="px-6 py-3 whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($kelas as $k)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">{{ $k->nama }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $k->tingkat }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $k->jurusan->nama }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $k->tahun_ajaran }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $k->semester }}</td>
                                        <td class="px-6 py-4 flex space-x-2 whitespace-nowrap">
                                            <a href="{{ route('admin.kelas.edit', $k) }}" class="text-blue-600 dark:text-blue-400 hover:underline">Edit</a>
                                            <button type="button" 
                                                x-data=""
                                                x-on:click.prevent="$dispatch('open-confirm-modal', {
                                                    title: 'Konfirmasi Hapus',
                                                    message: 'Apakah Anda yakin ingin menghapus kelas ini?\n\nData yang sudah dihapus mungkin tidak dapat dikembalikan.',
                                                    action: '{{ route('admin.kelas.destroy', $k) }}',
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
                                        <td colspan="6" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400 mb-3">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                    </svg>
                                                </div>
                                                <p class="text-base font-medium text-gray-900 dark:text-gray-100">Belum ada data kelas</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Data kelas yang ditambahkan akan tampil di sini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $kelas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
