<?php

namespace Tests\Unit;

use Tests\TestCase;

class EnvExampleTest extends TestCase
{
    /**
     * Membaca berkas .env.example dan mengembalikan pasangan key => value.
     *
     * @return array<string, string>
     */
    private function parseEnvExample(): array
    {
        $path = base_path('.env.example');
        $this->assertFileExists($path, 'Berkas .env.example harus ada di akar proyek.');

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $data = [];

        foreach ($lines as $line) {
            $line = trim($line);

            // Lewati baris komentar murni
            if (str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Potong komentar inline jika ada (# yang didahului spasi)
                if (str_contains($value, '#')) {
                    $parts = explode('#', $value, 2);
                    $value = trim($parts[0]);
                }

                $data[$key] = $value;
            }
        }

        return $data;
    }

    public function test_env_example_contains_all_required_keys(): void
    {
        $env = $this->parseEnvExample();

        $requiredKeys = [
            'SESSION_SECURE_COOKIE',
            'ABSENSI_PASSWORD_AWAL',
            'ABSENSI_BATAS_KEHADIRAN_RENDAH',
            'ADMIN_NAME',
            'ADMIN_USERNAME',
            'ADMIN_PASSWORD',
            'APP_DEBUG',
        ];

        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey(
                $key,
                $env,
                "Berkas .env.example harus memuat kunci: {$key}"
            );
        }
    }

    public function test_env_example_does_not_contain_real_secrets(): void
    {
        $env = $this->parseEnvExample();

        $this->assertArrayHasKey('APP_KEY', $env, 'Kunci APP_KEY harus didefinisikan.');
        $this->assertSame(
            '',
            $env['APP_KEY'],
            'Nilai APP_KEY di .env.example harus kosong (tanpa rahasia nyata).'
        );
        $this->assertSame(
            '',
            $env['ADMIN_PASSWORD'],
            'Nilai ADMIN_PASSWORD di .env.example harus kosong.'
        );
    }
}
