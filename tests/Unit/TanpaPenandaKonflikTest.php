<?php

namespace Tests\Unit;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class TanpaPenandaKonflikTest extends TestCase
{
    /**
     * Memeriksa apakah suatu teks mengandung penanda konflik git:
     * - Baris yang diawali '<<<<<<<' diikuti spasi
     * - Baris yang diawali '>>>>>>>' diikuti spasi
     * - Baris '=======' tepat sendirian jika berkas tersebut juga memuat salah satu penanda di atas.
     *
     * @return array<int, array{line: int, type: string, content: string}>
     */
    public static function cariPenandaKonflikDalamKonten(string $content): array
    {
        // Susun prefix secara dinamis agar tidak mencocokkan kode penguji itu sendiri
        $prefixBuka = '<'.'<'.'<'.'<'.'<<< ';
        $prefixTutup = '>'.'>'.'>'.'>'.'/>>> ';
        $pemisahTepat = '='.'='.'='.'='.'===';

        $lines = explode("\n", $content);
        $violations = [];
        $hasBukaAtauTutup = false;
        $barisPemisah = [];

        foreach ($lines as $index => $line) {
            $lineNum = $index + 1;
            $trimmed = rtrim($line, "\r");

            if (str_starts_with($trimmed, $prefixBuka)) {
                $hasBukaAtauTutup = true;
                $violations[] = [
                    'line' => $lineNum,
                    'type' => 'conflict_start',
                    'content' => $trimmed,
                ];
            } elseif (str_starts_with($trimmed, $prefixTutup)) {
                $hasBukaAtauTutup = true;
                $violations[] = [
                    'line' => $lineNum,
                    'type' => 'conflict_end',
                    'content' => $trimmed,
                ];
            } elseif (trim($trimmed) === $pemisahTepat) {
                $barisPemisah[] = [
                    'line' => $lineNum,
                    'type' => 'conflict_separator',
                    'content' => $trimmed,
                ];
            }
        }

        // Garis ======= sendirian hanya dianggap konflik bila berkas juga memuat <<<<<<< atau >>>>>>>
        if ($hasBukaAtauTutup && ! empty($barisPemisah)) {
            $violations = array_merge($violations, $barisPemisah);
            usort($violations, fn ($a, $b) => $a['line'] <=> $b['line']);
        }

        return $violations;
    }

    /**
     * Menghasilkan daftar berkas teks yang wajib dipindai sesuai spesifikasi.
     *
     * @return array<int, string>
     */
    protected function getBerkasYangDipindai(): array
    {
        $baseDir = dirname(__DIR__, 2);

        $rootFiles = [
            'README.md',
            'AGENTS.md',
            'composer.json',
            'package.json',
            '.env.example',
        ];

        $targetDirs = [
            'app',
            'bootstrap',
            'config',
            'database',
            'resources',
            'routes',
            'tests',
            'docs',
        ];

        $ignoredDirNames = [
            'vendor',
            'node_modules',
            'storage',
            'public',
            '.git',
        ];

        $binaryExtensions = [
            'png', 'jpg', 'jpeg', 'gif', 'ico', 'pdf', 'xlsx', 'xls', 'zip',
            'woff', 'woff2', 'ttf', 'eot', 'tar', 'gz', 'mp4', 'webp',
        ];

        $files = [];

        // 1. Berkas akar proyek
        foreach ($rootFiles as $rf) {
            $fullPath = $baseDir.DIRECTORY_SEPARATOR.$rf;
            if (is_file($fullPath)) {
                $files[] = $fullPath;
            }
        }

        // 2. Berkas dalam direktori target
        foreach ($targetDirs as $dirName) {
            $dirPath = $baseDir.DIRECTORY_SEPARATOR.$dirName;
            if (! is_dir($dirPath)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iterator as $item) {
                if (! $item->isFile()) {
                    continue;
                }

                $path = $item->getRealPath();

                // Abaikan jika berada di dalam folder yang dikecualikan
                $skip = false;
                foreach ($ignoredDirNames as $ignored) {
                    if (str_contains($path, DIRECTORY_SEPARATOR.$ignored.DIRECTORY_SEPARATOR)) {
                        $skip = true;
                        break;
                    }
                }
                if ($skip) {
                    continue;
                }

                // Abaikan berkas biner berdasarkan ekstensi
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if (in_array($ext, $binaryExtensions, true)) {
                    continue;
                }

                // Abaikan berkas penguji ini sendiri sebagai perlindungan ekstra
                if ($item->getFilename() === 'TanpaPenandaKonflikTest.php') {
                    continue;
                }

                $files[] = $path;
            }
        }

        return $files;
    }

    /**
     * Unit test untuk pemindai: string bersih tidak menghasilkan temuan.
     */
    public function test_pemindai_mendeteksi_string_bersih_tanpa_temuan(): void
    {
        $cleanContent = <<<'TXT'
        <?php
        // Baris kode normal
        $data = ['status' => 'hadir'];
        // Tanda sama dengan biasa untuk pembatas komentar:
        // ==========================================
        return $data;
        TXT;

        $violations = self::cariPenandaKonflikDalamKonten($cleanContent);
        $this->assertEmpty($violations);
    }

    /**
     * Unit test untuk pemindai: string berpenanda konflik berhasil dideteksi lengkap.
     */
    public function test_pemindai_mendeteksi_string_dengan_penanda_konflik(): void
    {
        $buka = '<'.'<'.'<'.'<'.'<<< HEAD';
        $pemisah = '='.'='.'='.'='.'===';
        $tutup = '>'.'>'.'>'.'>'.'/>>> 1e18dd7 (fix: test commit)';

        $conflictContent = implode("\n", [
            'baris 1',
            $buka,
            'kode kita',
            $pemisah,
            'kode mereka',
            $tutup,
            'baris akhir',
        ]);

        $violations = self::cariPenandaKonflikDalamKonten($conflictContent);

        $this->assertCount(3, $violations);
        $this->assertSame(2, $violations[0]['line']);
        $this->assertSame('conflict_start', $violations[0]['type']);
        $this->assertSame(4, $violations[1]['line']);
        $this->assertSame('conflict_separator', $violations[1]['type']);
        $this->assertSame(6, $violations[2]['line']);
        $this->assertSame('conflict_end', $violations[2]['type']);
    }

    /**
     * Penegak permanen: Memindai seluruh berkas proyek dan gagal bila ada penanda konflik Git.
     */
    public function test_seluruh_berkas_proyek_bebas_dari_penanda_konflik(): void
    {
        $files = $this->getBerkasYangDipindai();
        $this->assertNotEmpty($files, 'Daftar berkas yang dipindai tidak boleh kosong.');

        $baseDir = dirname(__DIR__, 2);
        $allViolations = [];

        foreach ($files as $filePath) {
            $content = file_get_contents($filePath);
            if ($content === false) {
                continue;
            }

            // Abaikan jika berkas mengandung karakter byte null (berkas biner)
            if (str_contains($content, "\0")) {
                continue;
            }

            $violations = self::cariPenandaKonflikDalamKonten($content);
            if (! empty($violations)) {
                $relative = str_replace($baseDir.DIRECTORY_SEPARATOR, '', $filePath);
                foreach ($violations as $v) {
                    $allViolations[] = "{$relative}:{$v['line']} [{$v['type']}] -> {$v['content']}";
                }
            }
        }

        $this->assertEmpty(
            $allViolations,
            "Ditemukan penanda konflik Git yang belum dibersihkan:\n".implode("\n", $allViolations)
        );
    }
}
