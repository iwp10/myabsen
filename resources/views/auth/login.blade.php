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
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

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
<body class="font-sans antialiased text-gray-900">
    <div class="relative min-h-screen flex items-center justify-center p-4 bg-cover bg-center bg-no-repeat"
         style="background-image: url('{{ asset('images/bg-school.jpg') }}');">
        <!-- Overlay gelap -->
        <div class="absolute inset-0 bg-black/50"></div>

        <!-- Card Form Login -->
        <div class="relative z-10 w-full max-w-md bg-white rounded-lg shadow-xl overflow-hidden">
            <!-- Header Card -->
            <div class="bg-blue-600 px-6 py-6 text-center text-white">
                <h1 class="text-2xl font-bold tracking-tight">Login MyAbsen</h1>
                <p class="text-sm text-blue-100 mt-1">Silakan masukkan Username, NIP, atau NIS</p>
            </div>

            <!-- Body Card -->
            <div class="p-6 sm:p-8">
                <!-- Status Sesi -->
                @if (session('status'))
                    <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-md">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Form Login -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Username / NIP / NIS -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
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
                               class="w-full px-3.5 py-2.5 bg-white border @error('username') border-red-500 @else border-gray-300 @enderror rounded-md text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 text-sm shadow-sm">
                        @error('username')
                            @if (!$errors->has('seconds_left'))
                                <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @endif
                        @enderror

                        <div x-data="{ seconds: {{ (int) ($errors->first('seconds_left') ?? 0) }} }"
                             x-init="if(seconds > 0) setInterval(() => seconds--, 1000)"
                             x-show="seconds > 0"
                             style="{{ $errors->has('seconds_left') ? '' : 'display: none;' }}"
                             class="mt-1.5 text-xs text-red-600 font-medium">
                            Terlalu banyak percobaan. Silakan coba lagi dalam <span x-text='seconds'></span> detik.
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Kata Sandi
                        </label>
                        <input id="password"
                               type="password"
                               name="password"
                               required
                               autocomplete="current-password"
                               placeholder="Masukkan kata sandi"
                               class="w-full px-3.5 py-2.5 bg-white border @error('password') border-red-500 @else border-gray-300 @enderror rounded-md text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 text-sm shadow-sm">
                        @error('password')
                            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                            <input id="remember_me"
                                   type="checkbox"
                                   name="remember"
                                   class="w-4 h-4 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
                        </label>
                    </div>

                    <!-- Tombol Masuk -->
                    <div>
                        <button type="submit"
                                class="w-full flex justify-center items-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out cursor-pointer">
                            Masuk
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center text-xs text-gray-400">
                    &copy; {{ date('Y') }} MyAbsen. Presensi Digital Sekolah.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
