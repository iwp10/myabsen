<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ darkMode: localStorage.getItem('theme') === 'dark' }"
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MyAbsen') }} - Login</title>

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
<body class="font-sans antialiased text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950">
    <div class="relative min-h-screen flex items-center justify-center p-4 bg-cover bg-center bg-no-repeat"
         style="background-image: url('{{ asset('images/bg-school.jpg') }}');">
        <!-- Overlay gelap -->
        <div class="absolute inset-0 bg-slate-950/70 dark:bg-slate-950/80"></div>

        <!-- Card Form Login -->
        <div class="relative z-10 w-full max-w-md bg-white dark:bg-slate-900 border-2 border-slate-300 dark:border-slate-600 rounded-2xl shadow-xl dark:shadow-black/60 dark:ring-1 dark:ring-white/5 overflow-hidden">
            <!-- Header Card -->
            <div class="bg-emerald-700 dark:bg-emerald-800 px-6 py-6 text-center text-white border-b-2 border-emerald-800 dark:border-emerald-700">
                <h1 class="text-2xl font-bold tracking-tight text-white">Login MyAbsen</h1>
                <p class="text-sm text-emerald-100 dark:text-emerald-200 mt-1">Silakan masukkan Username, NIP, atau NIS</p>
            </div>

            <!-- Body Card -->
            <div class="p-6 sm:p-8">
                <!-- Status Sesi -->
                @if (session('status'))
                    <x-alert type="success" class="mb-4">
                        {{ session('status') }}
                    </x-alert>
                @endif

                @if (session('error'))
                    <x-alert type="error" class="mb-4">
                        {{ session('error') }}
                    </x-alert>
                @endif

                <!-- Form Login -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Username / NIP / NIS -->
                    <div>
                        <label for="username" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">
                            Username / NIP / NIS
                        </label>
                        <input id="username"
                               type="text"
                               name="username"
                               value="{{ old('username') }}"
                               required
                               autofocus
                               autocomplete="username"
                               placeholder="Masukkan Username, NIP, atau NIS"
                               @class([
                                   'w-full min-h-[44px] px-3.5 py-2.5 bg-white dark:bg-slate-950 border-2 rounded-xl text-slate-900 dark:text-slate-50 placeholder:text-slate-500 dark:placeholder:text-slate-400 dark:[color-scheme:dark] focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500 transition duration-150 text-base sm:text-sm shadow-xs',
                                   'border-red-500 dark:border-red-400' => $errors->has('username'),
                                   'border-slate-400 dark:border-slate-500' => ! $errors->has('username'),
                               ])>
                        @error('username')
                            @if (!$errors->has('seconds_left'))
                                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
                            @endif
                        @enderror

                        <div x-data="{ seconds: {{ (int) ($errors->first('seconds_left') ?? 0) }} }"
                             x-init="if(seconds > 0) setInterval(() => seconds--, 1000)"
                             x-show="seconds > 0"
                             style="{{ $errors->has('seconds_left') ? '' : 'display: none;' }}"
                             class="mt-1.5 text-xs text-red-600 dark:text-red-400 font-medium">
                            Terlalu banyak percobaan. Silakan coba lagi dalam <span x-text='seconds'></span> detik.
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">
                            Kata Sandi
                        </label>
                        <input id="password"
                               type="password"
                               name="password"
                               required
                               autocomplete="current-password"
                               placeholder="Masukkan kata sandi"
                               @class([
                                   'w-full min-h-[44px] px-3.5 py-2.5 bg-white dark:bg-slate-950 border-2 rounded-xl text-slate-900 dark:text-slate-50 placeholder:text-slate-500 dark:placeholder:text-slate-400 dark:[color-scheme:dark] focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500 transition duration-150 text-base sm:text-sm shadow-xs',
                                   'border-red-500 dark:border-red-400' => $errors->has('password'),
                                   'border-slate-400 dark:border-slate-500' => ! $errors->has('password'),
                               ])>
                        @error('password')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer select-none min-h-[44px]">
                            <input id="remember_me"
                                   type="checkbox"
                                   name="remember"
                                   class="w-5 h-5 rounded border-2 border-slate-400 dark:border-slate-500 text-emerald-600 dark:text-emerald-400 bg-white dark:bg-slate-950 shadow-xs focus:ring-emerald-500">
                            <span class="ms-2 text-sm text-slate-700 dark:text-slate-200">Ingat saya</span>
                        </label>
                    </div>

                    <!-- Tombol Masuk -->
                    <div>
                        <button type="submit"
                                class="w-full min-h-[44px] flex justify-center items-center py-2.5 px-4 rounded-xl shadow-xs text-base sm:text-sm font-bold text-white dark:text-slate-950 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 dark:bg-emerald-400 dark:hover:bg-emerald-300 focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500 transition duration-150 ease-in-out cursor-pointer">
                            Masuk
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center text-xs text-slate-600 dark:text-slate-400">
                    &copy; {{ date('Y') }} MyAbsen. Presensi Digital Sekolah.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
