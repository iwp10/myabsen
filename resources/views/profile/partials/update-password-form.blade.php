<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Perbarui Password
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Pastikan akun Anda menggunakan password yang aman dengan minimal 8 karakter.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Password Saat Ini" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full {{ $errors->updatePassword->has('current_password') ? '!border-red-500 focus:!border-red-500 focus:!ring-red-500' : '' }}" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Password Baru" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full {{ $errors->updatePassword->has('password') ? '!border-red-500 focus:!border-red-500 focus:!ring-red-500' : '' }}" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Konfirmasi Password Baru" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full {{ $errors->updatePassword->has('password_confirmation') ? '!border-red-500 focus:!border-red-500 focus:!ring-red-500' : '' }}" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Simpan Password</x-primary-button>
        </div>
    </form>
</section>
