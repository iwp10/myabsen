<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ darkMode: localStorage.getItem('theme') === 'dark' }"
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))"
      :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>419 - Sesi Kedaluwarsa | {{ config('app.name', 'MyAbsen') }}</title>

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
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full text-center">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 mb-6 shadow-inner">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <h1 class="text-6xl font-extrabold text-amber-600 dark:text-amber-400 mb-2">419</h1>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Sesi Telah Kedaluwarsa</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-8 text-sm sm:text-base leading-relaxed">
                Halaman atau sesi Anda telah kedaluwarsa karena tidak ada aktivitas dalam waktu lama. Silakan masuk kembali untuk melanjutkan.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-900 shadow-sm transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                    Kembali ke Halaman Login
                </a>
            </div>
        </div>
    </body>
</html>
