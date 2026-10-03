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
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M18 6l-12 12" />
                    <path d="M6 6l12 12" />
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
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 4h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1" />
                <path d="M5 16h4a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-2a1 1 0 0 1 1 -1" />
                <path d="M15 12h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1" />
                <path d="M15 4h4a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-2a1 1 0 0 1 1 -1" />
            </svg>
            <span>Dashboard</span>
        </x-sidebar-link>

        @if(Auth::user()->role === 'admin')
            <!-- Jurusan -->
            <x-sidebar-link :href="route('admin.jurusan.index')" :active="request()->routeIs('admin.jurusan.*')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M10 3h4v4h-4z" />
                    <path d="M3 17h4v4h-4z" />
                    <path d="M17 17h4v4h-4z" />
                    <path d="M7 17l5 -4l5 4" />
                    <path d="M12 7v6" />
                </svg>
                <span>Jurusan</span>
            </x-sidebar-link>

            <!-- Kelas -->
            <x-sidebar-link :href="route('admin.kelas.index')" :active="request()->routeIs('admin.kelas.*')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M14 12v.01" />
                    <path d="M3 21h18" />
                    <path d="M6 21v-16a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v16" />
                </svg>
                <span>Kelas</span>
            </x-sidebar-link>

            <!-- Mata Pelajaran -->
            <x-sidebar-link :href="route('admin.mapel.index')" :active="request()->routeIs('admin.mapel.*')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M3 19a9 9 0 0 1 9 0a9 9 0 0 1 9 0" />
                    <path d="M3 6a9 9 0 0 1 9 0a9 9 0 0 1 9 0" />
                    <path d="M3 6l0 13" />
                    <path d="M12 6l0 13" />
                    <path d="M21 6l0 13" />
                </svg>
                <span>Mata Pelajaran</span>
            </x-sidebar-link>

            <!-- Guru -->
            <x-sidebar-link :href="route('admin.guru.index')" :active="request()->routeIs('admin.guru.*')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M3 7m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" />
                    <path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" />
                    <path d="M12 12l0 .01" />
                    <path d="M3 13a20 20 0 0 0 18 0" />
                </svg>
                <span>Guru</span>
            </x-sidebar-link>

            <!-- Siswa -->
            <x-sidebar-link :href="route('admin.siswa.index')" :active="request()->routeIs('admin.siswa.*')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                    <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
                </svg>
                <span>Siswa</span>
            </x-sidebar-link>

            <!-- Jadwal -->
            <x-sidebar-link :href="route('admin.jadwal.index')" :active="request()->routeIs('admin.jadwal.*')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4" />
                    <path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                    <path d="M15 3v4" />
                    <path d="M7 3v4" />
                    <path d="M3 11h16" />
                    <path d="M18 16.496v1.504l1 1" />
                </svg>
                <span>Jadwal</span>
            </x-sidebar-link>

            <!-- Koreksi Absensi -->
            <x-sidebar-link :href="route('admin.koreksi-absensi.index')" :active="request()->routeIs('admin.koreksi-absensi.*')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M9 5h10l2 2l-2 2h-10a2 2 0 0 1 -2 -2a2 2 0 0 1 2 -2" />
                    <path d="M13 13h6l2 2l-2 2h-6a2 2 0 0 1 -2 -2a2 2 0 0 1 2 -2" />
                    <path d="M7 21h8l2 2l-2 2h-8a2 2 0 0 1 -2 -2a2 2 0 0 1 2 -2" />
                </svg>
                <span>Koreksi Absensi</span>
            </x-sidebar-link>

            <!-- Laporan -->
            <x-sidebar-link :href="route('admin.laporan.index')" :active="request()->routeIs('admin.laporan.*')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                    <path d="M9 17h6" />
                    <path d="M9 13h6" />
                </svg>
                <span>Laporan</span>
            </x-sidebar-link>
        @endif

        @if(Auth::user()->role === 'guru')
            <!-- Jadwal Mengajar Guru -->
            <x-sidebar-link :href="route('guru.jadwal')" :active="request()->routeIs('guru.jadwal*')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4" />
                    <path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                    <path d="M15 3v4" />
                    <path d="M7 3v4" />
                    <path d="M3 11h16" />
                    <path d="M18 16.496v1.504l1 1" />
                </svg>
                <span>Jadwal Mengajar</span>
            </x-sidebar-link>

            <!-- Riwayat Guru -->
            <x-sidebar-link :href="route('guru.riwayat')" :active="request()->routeIs('guru.riwayat*') || request()->routeIs('guru.laporan.*')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M12 8l0 4l2 2" />
                    <path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5" />
                </svg>
                <span>Riwayat</span>
            </x-sidebar-link>
        @endif

        @if(Auth::user()->role === 'siswa')
            <!-- Riwayat Siswa -->
            <x-sidebar-link :href="route('siswa.riwayat')" :active="request()->routeIs('siswa.riwayat*')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M12 8l0 4l2 2" />
                    <path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5" />
                </svg>
                <span>Riwayat</span>
            </x-sidebar-link>
        @endif
    </div>

    <!-- Bagian Bawah Sidebar (Profil & Keluar) -->
    <div class="p-3 border-t border-gray-200 dark:border-gray-700 space-y-1 flex-shrink-0 bg-gray-50/50 dark:bg-gray-800/50">
        <x-sidebar-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
            </svg>
            <span>Profil</span>
        </x-sidebar-link>

        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit"
                    class="w-full text-left text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 group flex items-center px-3.5 py-2.5 text-sm font-medium rounded-md transition duration-150 ease-in-out">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 dark:text-gray-400 group-hover:text-red-600 dark:group-hover:text-red-400 mr-3 flex-shrink-0 h-5 w-5 transition duration-150 ease-in-out">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" />
                    <path d="M9 12h12l-3 -3" />
                    <path d="M18 15l3 -3" />
                </svg>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>
