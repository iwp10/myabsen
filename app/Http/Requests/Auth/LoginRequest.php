<?php

namespace App\Http\Requests\Auth;

use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $input = $this->input('username');
        $password = $this->input('password');
        $remember = $this->boolean('remember');

        // Cek username
        if (Auth::attempt(['username' => $input, 'password' => $password], $remember)) {
            RateLimiter::clear($this->throttleKey());

            return;
        }

        // Cek nip dari tabel guru
        $guru = Guru::where('nip', $input)->first();
        if ($guru && Auth::attempt(['id' => $guru->user_id, 'password' => $password], $remember)) {
            RateLimiter::clear($this->throttleKey());

            return;
        }

        // Cek nis dari tabel siswa
        $siswa = Siswa::where('nis', $input)->first();
        if ($siswa && Auth::attempt(['id' => $siswa->user_id, 'password' => $password], $remember)) {
            RateLimiter::clear($this->throttleKey());

            return;
        }

        RateLimiter::hit($this->throttleKey());

        $retriesLeft = RateLimiter::retriesLeft($this->throttleKey(), 5);

        throw ValidationException::withMessages([
            'username' => "Kredensial tidak valid. Sisa percobaan Anda: {$retriesLeft} kali lagi sebelum dikunci.",
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
            'seconds_left' => $seconds,
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('username')).'|'.$this->ip());
    }
}
