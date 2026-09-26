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
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                        <h3 class="text-lg font-bold">Daftar Guru</h3>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                            <form action="{{ route('admin.guru.index') }}" method="GET" class="flex gap-2">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIP..." class="w-full sm:w-64 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md text-sm transition">Cari</button>
                                @if(request('search'))
                                    <a href="{{ route('admin.guru.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-3 rounded-md text-sm flex items-center justify-center transition">Reset</a>
                                @endif
                            </form>
                            <a href="{{ route('admin.guru.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm text-center transition">
                                Tambah Guru
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3 whitespace-nowrap">Nama</th>
                                    <th scope="col" class="px-6 py-3 whitespace-nowrap">NIP / Username</th>
                                    <th scope="col" class="px-6 py-3 whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($gurus as $guru)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">{{ $guru->user->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $guru->nip }}</td>
                                        <td class="px-6 py-4 flex items-center space-x-3 whitespace-nowrap">
                                            <a href="{{ route('admin.guru.edit', $guru) }}" class="text-blue-600 dark:text-blue-400 hover:underline">Edit</a>
                                            <form action="{{ route('admin.guru.destroy', $guru) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus guru ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Hapus</button>
                                            </form>
                                            <form action="{{ route('admin.guru.reset-password', $guru) }}" method="POST" onsubmit="return confirm('Reset password guru ini ke \'password\'?');" class="inline">
                                                @csrf
                                                <button type="submit" class="text-yellow-600 dark:text-yellow-400 hover:underline">Reset Password</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400 mb-3">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                </div>
                                                <p class="text-base font-medium text-gray-900 dark:text-gray-100">Belum ada data guru</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Data guru yang ditambahkan akan tampil di sini.</p>
                                            </div>
                                        </td>
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
