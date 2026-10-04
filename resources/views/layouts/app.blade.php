<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ darkMode: localStorage.getItem('theme') === 'dark', sidebarOpen: false }"
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))"
      :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MyAbsen') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>
    </head>
    <body class="font-sans antialiased text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-900">
        <div class="flex h-screen bg-gray-100 dark:bg-gray-900 overflow-hidden">
            <!-- Sidebar Navigasi -->
            @include('layouts.navigation')

            <!-- Area Konten Utama -->
            <div class="flex-1 flex flex-col min-w-0 h-screen overflow-y-auto">
                <!-- Topbar Header -->
                <header class="sticky top-0 z-30 flex-shrink-0 flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-xs">
                    <div class="flex items-center gap-3 min-w-0">
                        <!-- Tombol Hamburger Mobile -->
                        <button @click="sidebarOpen = true" type="button" class="lg:hidden p-2 -ml-2 rounded-md text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Buka Menu">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M4 6l16 0" />
                                <path d="M4 12l16 0" />
                                <path d="M4 18l16 0" />
                            </svg>
                        </button>

                        <!-- Heading / Title Halaman -->
                        @isset($header)
                            <div class="truncate">
                                {{ $header }}
                            </div>
                        @else
                            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight truncate">
                                MyAbsen
                            </h2>
                        @endisset
                    </div>

                    <!-- Topbar Kanan: Dark Mode & User Dropdown -->
                    <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                        <!-- Dark Mode Toggle -->
                        <button @click="darkMode = !darkMode" type="button" class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none rounded-lg text-sm p-2 transition" title="Ganti Tema">
                            <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" />
                            </svg>
                            <svg x-show="darkMode" style="display: none;" :style="{ display: darkMode ? 'block' : 'none' }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                <path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7" />
                            </svg>
                        </button>

                        <!-- User Profile Dropdown -->
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-700/60 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition">
                                    <span class="max-w-[120px] sm:max-w-none truncate">{{ Auth::user()->name }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ms-1.5 h-4 w-4">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M6 9l6 6l6 -6" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('Profil') }}
                                </x-dropdown-link>

                                <!-- Authentication -->
                                <x-dropdown-link href="#"
                                    x-data=""
                                    x-on:click.prevent="$dispatch('open-confirm-modal', {
                                        title: 'Konfirmasi Logout',
                                        message: 'Apakah Anda yakin ingin keluar dari akun?',
                                        action: '{{ route('logout') }}',
                                        method: 'POST',
                                        confirmText: 'Logout'
                                    })">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 px-4 sm:px-0">
                    {{ $slot }}
                </main>
            </div>
        </div>
        
        <!-- Global Confirm Modal -->
        <x-confirm-modal />
    </body>
</html>
