<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ darkMode: localStorage.getItem('theme') === 'dark' }"
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))"
      :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>403 - Akses Ditolak | {{ config('app.name', 'MyAbsen') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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
            <div class="bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-2xl p-6 sm:p-8 shadow-lg dark:shadow-black/40">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-100 dark:bg-red-500/15 text-red-800 dark:text-red-300 border-2 border-red-400 dark:border-red-500/50 mb-6 shadow-inner">
                    <x-ui.icon size="lg" name="x" class="w-10 h-10" />
                </div>

                <h1 class="text-6xl font-extrabold text-red-700 dark:text-red-400 mb-2 tracking-tight">403</h1>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 mb-3">Akses Ditolak</h2>
                <p class="text-slate-600 dark:text-slate-300 mb-6 text-sm sm:text-base leading-relaxed">
                    Anda tidak memiliki izin untuk membuka halaman ini. Jika Anda merasa ini keliru, hubungi admin sekolah.
                </p>

                @php
                    $rawMessage = trim($exception?->getMessage() ?? '');
                    $isEnglish = (bool) preg_match('/(?:this action is unauthorized|forbidden|unauthorized|not found|server error)/i', $rawMessage);
                    $isSafeIndonesia = !empty($rawMessage) && !$isEnglish && $rawMessage !== 'Akses ditolak.' && $rawMessage !== 'Akses Ditolak.';
                @endphp
                @if ($isSafeIndonesia)
                    <div class="text-xs sm:text-sm font-medium text-amber-900 dark:text-amber-300 bg-amber-100 dark:bg-amber-500/15 border-2 border-amber-400 dark:border-amber-500/50 rounded-xl p-3 mb-6 text-center">
                        {{ $rawMessage }}
                    </div>
                @endif

                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    @auth
                        @php
                            $role = auth()->user()->role ?? null;
                            $dashboardRoute = match ($role) {
                                'admin' => 'admin.dashboard',
                                'guru' => 'guru.dashboard',
                                'siswa' => 'siswa.dashboard',
                                default => null,
                            };
                            $targetUrl = $dashboardRoute ? route($dashboardRoute) : url('/');
                        @endphp
                        <a href="{{ $targetUrl }}" class="w-full sm:w-auto inline-flex items-center justify-center min-h-[44px] min-w-[44px] px-5 py-2.5 text-sm font-semibold rounded-xl text-white bg-emerald-700 hover:bg-emerald-800 dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950 shadow-xs focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500 transition">
                            <x-ui.icon size="sm" name="arrow-right" class="mr-2" />
                            Kembali ke Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center min-h-[44px] min-w-[44px] px-5 py-2.5 text-sm font-semibold rounded-xl text-white bg-emerald-700 hover:bg-emerald-800 dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950 shadow-xs focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500 transition">
                            <x-ui.icon size="sm" name="arrow-right" class="mr-2" />
                            Ke Halaman Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </body>
</html>
