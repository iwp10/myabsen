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
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold">Daftar Kelas</h3>
                        <a href="{{ route('admin.kelas.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Tambah Kelas
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Nama</th>
                                    <th scope="col" class="px-6 py-3">Tingkat</th>
                                    <th scope="col" class="px-6 py-3">Jurusan</th>
                                    <th scope="col" class="px-6 py-3">Tahun Ajaran</th>
                                    <th scope="col" class="px-6 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($kelas as $k)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-6 py-4">{{ $k->nama }}</td>
                                        <td class="px-6 py-4">{{ $k->tingkat }}</td>
                                        <td class="px-6 py-4">{{ $k->jurusan->nama }}</td>
                                        <td class="px-6 py-4">{{ $k->tahun_ajaran }}</td>
                                        <td class="px-6 py-4 flex space-x-2">
                                            <a href="{{ route('admin.kelas.edit', $k) }}" class="text-blue-600 hover:underline">Edit</a>
                                            <form action="{{ route('admin.kelas.destroy', $k) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kelas ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center">Belum ada data kelas.</td>
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
