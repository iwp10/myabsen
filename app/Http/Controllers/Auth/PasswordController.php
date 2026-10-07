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
        $passwordAwal = (string) config('absensi.password_awal', 'password');

        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                Password::defaults(),
                'min:8',
                'confirmed',
                function (string $attribute, mixed $value, Closure $fail) use ($passwordAwal) {
                    if ($value === 'password' || $value === $passwordAwal) {
                        $fail('Password baru tidak boleh sama dengan password awal.');
                    }
                },
            ],
        ], [
            'password.min' => 'Password baru minimal harus 8 karakter.',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ]);

        return back()->with('status', 'password-updated');
    }
}
