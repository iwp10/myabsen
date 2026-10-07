<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = config('absensi.admin.name') ?: 'Administrator';
        $username = config('absensi.admin.username') ?: 'admin';
        $password = config('absensi.admin.password');

        $existingAdmin = User::where('username', $username)->first();
        if ($existingAdmin) {
            $this->command?->info("Akun admin dengan username '{$username}' sudah ada. Pembuatan akun dilewati.");

            return;
        }

        $generated = false;
        if (empty($password)) {
            $password = Str::password(16);
            $generated = true;
        }

        if (app()->isProduction()) {
            if (! $generated && (strlen($password) < 12 || $password === 'password')) {
                throw new RuntimeException('Di lingkungan produksi, password admin minimal 12 karakter dan tidak boleh menggunakan "password".');
            }
        }

        User::create([
            'name' => $name,
            'username' => $username,
            'password' => Hash::make($password),
            'role' => 'admin',
            'must_change_password' => true,
        ]);

        if ($generated) {
            $this->command?->warn("Password admin dibuat otomatis: {$password}");
        } else {
            $this->command?->info("Akun admin '{$username}' berhasil dibuat.");
        }
    }
}
