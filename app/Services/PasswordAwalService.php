<?php

namespace App\Services;

use RuntimeException;

class PasswordAwalService
{
    /**
     * Mendapatkan password awal yang terkonfigurasi.
     *
     * Di environment produksi: jika kosong, bernilai 'password', atau kurang dari 8 karakter,
     * akan melempar exception demi keamanan sistem.
     * Di environment non-produksi: fallback ke 'password' tetap diperbolehkan.
     *
     * @throws RuntimeException
     */
    public static function get(): string
    {
        $password = (string) config('absensi.password_awal', '');

        if (app()->isProduction()) {
            if ($password === '' || $password === 'password' || strlen($password) < 8) {
                throw new RuntimeException('Password awal belum diatur dengan aman. Hubungi pengelola sistem.');
            }

            return $password;
        }

        if ($password === '') {
            return 'password';
        }

        return $password;
    }
}
