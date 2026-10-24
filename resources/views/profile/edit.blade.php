<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 dark:text-slate-100 leading-tight">
            {{ __('Profil') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if ($user->must_change_password || session('warning'))
                <x-alert type="warning">
                    {{ session('warning') ?: 'Anda harus mengganti password awal sebelum melanjutkan.' }}
                </x-alert>
            @endif

            @if (session('status') || session('success'))
                <x-alert type="success" :autoDismiss="true">
                    {{ session('status') === 'password-updated' ? 'Password berhasil diganti.' : (session('status') ?: session('success')) }}
                </x-alert>
            @endif

            @if ($errors->updatePassword->any() || session('error'))
                <x-alert type="error">
                    {{ session('error') ?: 'Password gagal diganti. Periksa isian di bawah.' }}
                </x-alert>
            @endif

            <div class="p-4 sm:p-6 lg:p-8 bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-2xl shadow-sm">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
