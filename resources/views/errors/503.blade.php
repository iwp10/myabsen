<!DOCTYPE html>
<html lang="id"
      x-data="{ darkMode: localStorage.getItem('theme') === 'dark' }"
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))"
      :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>503 - Sistem Sedang Dalam Pemeliharaan | {{ config('app.name', 'MyAbsen') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Inline Fallback Styles -->
        <style>
            body {
                margin: 0;
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                background-color: #f8fafc;
                color: #1e293b;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                padding: 16px;
                box-sizing: border-box;
            }
            .dark body {
                background-color: #0f172a;
                color: #e2e8f0;
            }
            .fallback-card {
                max-width: 28rem;
                width: 100%;
                background: #ffffff;
                border: 2px solid #cbd5e1;
                border-radius: 1rem;
                padding: 2rem 1.5rem;
                text-align: center;
                box-sizing: border-box;
            }
            .dark .fallback-card {
                background: #1e293b;
                border-color: #475569;
            }
        </style>

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
    <body class="font-sans antialiased bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full text-center">
            <div class="fallback-card bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-2xl p-6 sm:p-8 shadow-lg dark:shadow-black/40">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-amber-100 dark:bg-amber-500/15 text-amber-800 dark:text-amber-300 border-2 border-amber-400 dark:border-amber-500/50 mb-6 shadow-inner">
                    <x-ui.icon size="lg" class="w-10 h-10">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M7 10h3v-3l-3.5 -3.5a6 6 0 0 1 8 8l6 6a2 2 0 0 1 -3 3l-6 -6a6 6 0 0 1 -8 -8l3.5 3.5" />
                    </x-ui.icon>
                </div>

                <h1 class="text-6xl font-extrabold text-amber-700 dark:text-amber-400 mb-2 tracking-tight">503</h1>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 mb-3">Sistem Sedang Dalam Pemeliharaan</h2>
                <p class="text-slate-600 dark:text-slate-300 mb-8 text-sm sm:text-base leading-relaxed">
                    MyAbsen sedang diperbarui. Silakan coba lagi beberapa saat lagi.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <button type="button" onclick="window.location.reload()" class="w-full sm:w-auto inline-flex items-center justify-center min-h-[44px] min-w-[44px] px-5 py-2.5 text-sm font-semibold rounded-xl text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-800 border-2 border-slate-400 dark:border-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 shadow-xs focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500 transition">
                        <x-ui.icon size="sm" class="mr-2 w-4 h-4 shrink-0">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" />
                            <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" />
                        </x-ui.icon>
                        Muat Ulang
                    </button>
                    <a href="{{ url('/') }}" class="w-full sm:w-auto inline-flex items-center justify-center min-h-[44px] min-w-[44px] px-5 py-2.5 text-sm font-semibold rounded-xl text-white bg-emerald-700 hover:bg-emerald-800 dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950 shadow-xs focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500 transition">
                        <x-ui.icon size="sm" name="arrow-right" class="mr-2" />
                        Ke Halaman Beranda
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>
