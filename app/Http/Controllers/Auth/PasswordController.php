<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $passwordAwal = (string) config('absensi.password_awal', 'password');

        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                Password::defaults(),
                'min:8',
                'confirmed',
                function (string $attribute, mixed $value, Closure $fail) use ($user, $passwordAwal) {
                    if (Hash::check($value, $user->password)) {
                        $fail('Password baru tidak boleh sama dengan password lama.');

                        return;
                    }

                    if ($value === 'password' || $value === $passwordAwal) {
                        $fail('Password baru tidak boleh sama dengan password awal.');
                    }
                },
            ],
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'current_password.current_password' => 'Password lama salah.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru kurang dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $wasMustChangePassword = (bool) $user->must_change_password;

        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ]);

        if ($wasMustChangePassword) {
            $role = $user->role;
            $dashboardRoute = match ($role) {
                'admin' => 'admin.dashboard',
                'guru' => 'guru.dashboard',
                'siswa' => 'siswa.dashboard',
                default => 'login',
            };

            return redirect()->route($dashboardRoute)->with('status', 'Password berhasil diganti.');
        }

        return redirect()->route('profile.edit')->with('status', 'Password berhasil diganti.');
    }
}
