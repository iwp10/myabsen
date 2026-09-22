<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Kelola Guru') }}
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
                        <h3 class="text-lg font-bold">Daftar Guru</h3>
                        <div class="flex space-x-2">
                            <form action="{{ route('admin.guru.index') }}" method="GET" class="flex">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIP..." class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mr-2">
                                <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Cari</button>
                            </form>
                            <a href="{{ route('admin.guru.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Tambah Guru
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Nama</th>
                                    <th scope="col" class="px-6 py-3">NIP / Username</th>
                                    <th scope="col" class="px-6 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($gurus as $guru)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-6 py-4">{{ $guru->user->name }}</td>
                                        <td class="px-6 py-4">{{ $guru->nip }}</td>
                                        <td class="px-6 py-4 flex items-center space-x-3">
                                            <a href="{{ route('admin.guru.edit', $guru) }}" class="text-blue-600 hover:underline">Edit</a>
                                            <form action="{{ route('admin.guru.destroy', $guru) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus guru ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                            </form>
                                            <form action="{{ route('admin.guru.reset-password', $guru) }}" method="POST" onsubmit="return confirm('Reset password guru ini ke \'password\'?');" class="inline">
                                                @csrf
                                                <button type="submit" class="text-yellow-600 hover:underline">Reset Password</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-center">Belum ada data guru.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $gurus->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
