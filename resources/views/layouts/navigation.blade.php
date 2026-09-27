<!-- Mobile Backdrop -->
<div x-show="sidebarOpen"
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="sidebarOpen = false"
     class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-xs lg:hidden"
     style="display: none;">
</div>

<!-- Sidebar Component -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       @keydown.escape.window="sidebarOpen = false"
       class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col transition-transform duration-300 ease-in-out lg:static lg:inset-auto lg:z-auto lg:h-screen lg:flex-shrink-0 -translate-x-full lg:translate-x-0">
    
    <!-- Bagian Atas Sidebar -->
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
        <!-- Logo & Nama Sekolah -->
        <div class="flex items-center justify-between">
            <a href="{{ route(Auth::user()->role . '.dashboard') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="Logo SMK Mandiri 02 Balaraja" class="w-12 h-12 object-contain flex-shrink-0">
                <div class="min-w-0">
                    <span class="block text-xl font-bold tracking-tight text-gray-900 dark:text-white leading-tight">MyAbsen</span>
                    <span class="block text-xs font-semibold text-gray-600 dark:text-gray-400 truncate">SMK Mandiri 02 Balaraja</span>
                </div>
            </a>
            <!-- Tombol Tutup Mobile -->
            <button @click="sidebarOpen = false" type="button" class="lg:hidden p-1.5 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none" aria-label="Tutup Menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Identitas User / Role Card -->
        <div class="mt-4 p-3 bg-blue-50 dark:bg-gray-700/60 rounded-lg border border-blue-100 dark:border-gray-600">
            <div class="text-[10px] font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400">
                @if(Auth::user()->role === 'admin')
                    OPERATOR PETUGAS ABSENSI
                @elseif(Auth::user()->role === 'guru')
                    GURU PENGAJAR
                @elseif(Auth::user()->role === 'siswa')
                    SISWA
                @else
                    {{ strtoupper(Auth::user()->role) }}
                @endif
            </div>
            <div class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate mt-0.5" title="{{ Auth::user()->name }}">
                {{ Auth::user()->name }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                {{ Auth::user()->username }}
            </div>
        </div>
    </div>

    <!-- Menu Navigasi Sidebar -->
    <div class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
            Menu Utama
        </div>

        <!-- Dashboard -->
        @php
            $isDashboardActive = request()->routeIs(Auth::user()->role . '.dashboard') || (Auth::user()->role === 'guru' && request()->routeIs('guru.absensi.*'));
        @endphp
        <x-sidebar-link :href="route(Auth::user()->role . '.dashboard')" :active="$isDashboardActive">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Dashboard</span>
        </x-sidebar-link>

        @if(Auth::user()->role === 'admin')
            <!-- Jurusan -->
            <x-sidebar-link :href="route('admin.jurusan.index')" :active="request()->routeIs('admin.jurusan.*')">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span>Jurusan</span>
            </x-sidebar-link>

            <!-- Kelas -->
            <x-sidebar-link :href="route('admin.kelas.index')" :active="request()->routeIs('admin.kelas.*')">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                </svg>
                <span>Kelas</span>
            </x-sidebar-link>

            <!-- Mata Pelajaran -->
            <x-sidebar-link :href="route('admin.mapel.index')" :active="request()->routeIs('admin.mapel.*')">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Mata Pelajaran</span>
            </x-sidebar-link>

            <!-- Guru -->
            <x-sidebar-link :href="route('admin.guru.index')" :active="request()->routeIs('admin.guru.*')">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>Guru</span>
            </x-sidebar-link>

            <!-- Siswa -->
            <x-sidebar-link :href="route('admin.siswa.index')" :active="request()->routeIs('admin.siswa.*')">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>Siswa</span>
            </x-sidebar-link>

            <!-- Jadwal -->
            <x-sidebar-link :href="route('admin.jadwal.index')" :active="request()->routeIs('admin.jadwal.*')">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Jadwal</span>
            </x-sidebar-link>

            <!-- Laporan -->
            <x-sidebar-link :href="route('admin.laporan.index')" :active="request()->routeIs('admin.laporan.*')">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Laporan</span>
            </x-sidebar-link>
        @endif

        @if(Auth::user()->role === 'guru')
            <!-- Jadwal Mengajar Guru -->
            <x-sidebar-link :href="route('guru.jadwal')" :active="request()->routeIs('guru.jadwal*')">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Jadwal Mengajar</span>
            </x-sidebar-link>

            <!-- Riwayat Guru -->
            <x-sidebar-link :href="route('guru.riwayat')" :active="request()->routeIs('guru.riwayat*') || request()->routeIs('guru.laporan.*')">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Riwayat</span>
            </x-sidebar-link>
        @endif

        @if(Auth::user()->role === 'siswa')
            <!-- Riwayat Siswa -->
            <x-sidebar-link :href="route('siswa.riwayat')" :active="request()->routeIs('siswa.riwayat*')">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Riwayat</span>
            </x-sidebar-link>
        @endif
    </div>

    <!-- Bagian Bawah Sidebar (Profil & Keluar) -->
    <div class="p-3 border-t border-gray-200 dark:border-gray-700 space-y-1 flex-shrink-0 bg-gray-50/50 dark:bg-gray-800/50">
        <x-sidebar-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>Profil</span>
        </x-sidebar-link>

        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit"
                    class="w-full text-left text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 group flex items-center px-3.5 py-2.5 text-sm font-medium rounded-md transition duration-150 ease-in-out">
                <svg class="text-gray-400 dark:text-gray-400 group-hover:text-red-600 dark:group-hover:text-red-400 mr-3 flex-shrink-0 h-5 w-5 transition duration-150 ease-in-out" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>
